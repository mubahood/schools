<?php

namespace App\Models;

use App\Services\SmsService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * A signup in progress. Owns the OTP lifecycle so the controller stays thin.
 */
class OnboardingDraft extends Model
{
    protected $fillable = ['token', 'email', 'phone', 'user_data', 'enterprise_data', 'otp_hash', 'otp_expires_at',
        'otp_attempts', 'otp_sends', 'otp_sent_at', 'verified_via', 'verified_at', 'consumed_at', 'enterprise_id', 'ip'];

    protected $casts = ['user_data' => 'array', 'enterprise_data' => 'array', 'otp_expires_at' => 'datetime',
        'otp_sent_at' => 'datetime', 'verified_at' => 'datetime', 'consumed_at' => 'datetime'];

    public const OTP_TTL_MIN = 10;
    public const OTP_MAX_ATTEMPTS = 5;
    public const OTP_MAX_SENDS_PER_HOUR = 5;
    public const RESEND_COOLDOWN_SEC = 60;

    public static function start(array $user, ?string $ip): self
    {
        $phone = Utils::prepare_phone_number($user['phone_number']);
        // one live draft per phone/email: reuse it so resends don't multiply rows
        $d = self::whereNull('consumed_at')->where(function ($q) use ($user, $phone) {
            $q->where('email', $user['email'])->orWhere('phone', $phone);
        })->orderByDesc('id')->first() ?: new self(['token' => Str::random(64)]);

        $d->email = $user['email'];
        $d->phone = $phone;
        $d->ip = $ip;
        $d->user_data = [
            'first_name' => $user['first_name'], 'last_name' => $user['last_name'],
            'email' => $user['email'], 'phone_number' => $phone,
            'password_hash' => Hash::make($user['password']),
        ];
        if (!$d->exists) {
            $d->save();
        } else {
            $d->save();
        }

        return $d;
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    public function canResend(): array
    {
        if ($this->otp_sent_at && $this->otp_sent_at->diffInSeconds(now()) < self::RESEND_COOLDOWN_SEC) {
            return [false, 'Please wait a minute before requesting another code.'];
        }
        $recent = self::where('phone', $this->phone)->where('otp_sent_at', '>=', now()->subHour())->sum('otp_sends');
        if ($recent >= self::OTP_MAX_SENDS_PER_HOUR) {
            return [false, 'Too many codes requested. Please try again in an hour.'];
        }

        return [true, null];
    }

    /** Generate a fresh code and push it by SMS and email. Returns channels that accepted it. */
    public function sendOtp(): array
    {
        $code = (string) random_int(100000, 999999);
        $this->otp_hash = Hash::make($code);
        $this->otp_expires_at = now()->addMinutes(self::OTP_TTL_MIN);
        $this->otp_attempts = 0;
        $this->otp_sends = (int) $this->otp_sends + 1;
        $this->otp_sent_at = now();
        $this->save();

        $app = Utils::app_name();
        $sent = [];
        if (SmsService::send($this->phone, "$app: your verification code is $code. It expires in " . self::OTP_TTL_MIN . " minutes.")) {
            $sent[] = 'sms';
        }
        try {
            Utils::mail_sender([
                'email' => $this->email,
                'name' => trim(($this->user_data['first_name'] ?? '') . ' ' . ($this->user_data['last_name'] ?? '')),
                'subject' => "$app verification code: $code",
                'body' => "<p>Your verification code is <b style=\"font-size:22px\">$code</b>.</p><p>It expires in " . self::OTP_TTL_MIN . " minutes. If you did not start a registration, ignore this email.</p>",
                'data' => "Your verification code is $code",
            ]);
            $sent[] = 'email';
        } catch (\Throwable $e) {
            Log::error('OTP email failed', ['draft' => $this->id, 'error' => $e->getMessage()]);
        }
        Log::info('Signup OTP sent', ['draft' => $this->id, 'channels' => $sent]);

        return $sent;
    }

    /** Null on success, else the message to show. */
    public function verify(string $code, string $via = 'sms'): ?string
    {
        if ($this->isVerified()) {
            return null;
        }
        if (!$this->otp_hash || !$this->otp_expires_at) {
            return 'No code has been sent yet. Please request one.';
        }
        if ($this->otp_attempts >= self::OTP_MAX_ATTEMPTS) {
            return 'Too many wrong attempts. Please request a new code.';
        }
        if ($this->otp_expires_at->isPast()) {
            return 'That code has expired. Please request a new one.';
        }
        $this->otp_attempts++;
        if (!Hash::check(trim($code), $this->otp_hash)) {
            $this->save();
            $left = self::OTP_MAX_ATTEMPTS - $this->otp_attempts;
            return 'Incorrect code. ' . ($left > 0 ? "$left attempt(s) left." : 'Please request a new code.');
        }
        $this->verified_at = now();
        $this->verified_via = $via;
        $this->otp_hash = null;
        $this->save();

        return null;
    }
}
