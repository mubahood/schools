<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DirectMessage extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();

        self::deleting(function ($m) {
            if ($m->status == 'Sent') {
                throw new \Exception("Cannot delete a sent message.");
            }
        });
        self::creating(function ($m) {
            if (strlen($m->receiver_number) < 7) {
                $u = Administrator::find($m->administrator_id);
                $m->receiver_number = $u->phone_number_1;
                if (strlen($m->receiver_number) < 7) {
                    $m->receiver_number = $u->phone_number_2;
                }
            }
            $m->receiver_number = Utils::prepareUgandanPhoneNumber($m->receiver_number);
            return $m;
        });
    }

    public static function send_message($m)
    {
        if ($m->status !== 'Pending') {
            return "Message status is not 'Pending'. Current status: {$m->status}";
        }

        // Validate phone number
        if (!Utils::validateUgandanPhoneNumber($m->receiver_number)) {
            $m->status = 'Failed';
            $m->error_message_message = 'Invalid phone number - ' . $m->receiver_number;
            $m->save();
            return $m->error_message_message;
        }
        // Validate enterprise
        $ent = Enterprise::find($m->enterprise_id);
        if ($ent === null) {
            $m->status = 'Failed';
            $m->error_message_message = 'Enterprise is not active.';
            $m->save();
            return $m->error_message_message;
        }

        // Check wallet balance
        if ($ent->wallet_balance < 50) {
            $m->status = 'Failed';
            $m->error_message_message = 'Insufficient funds.';
            $m->save();
            return $m->error_message_message;
        }

        // Validate message body
        if (empty(trim($m->message_body))) {
            $m->status = 'Failed';
            $m->error_message_message = 'Message body is empty.';
            $m->save();
            return $m->error_message_message;
        }

        // Check if messaging is enabled
        if ($ent->can_send_messages !== 'Yes') {
            $m->status = 'Failed';
            $m->error_message_message = 'Messages are not enabled.';
            $m->save();
            return $m->error_message_message;
        }

        $username = 'mubaraka';
        // Construct API URL
        $url = "https://www.socnetsolutions.com/projects/bulk/amfphp/services/blast.php";

        $m->message_body = "this is a simple messge";
        $msg = htmlspecialchars(trim($m->message_body));
        $msg = urlencode($msg);
        $receiver_number = str_replace('+', '', trim($m->receiver_number));
        $url = "https://www.socnetsolutions.com/projects/bulk/amfphp/services/blast.php?spname=$username&sppass=Mub4r4k4@2025&type=json&numbers=$receiver_number&msg=$msg";



        try {
            $ch = curl_init();
            // $url;

            curl_setopt_array($ch, [
                CURLOPT_URL            => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_TIMEOUT        => 30,
            ]);

            $response = curl_exec($ch);
            try {
                curl_errno($ch);
            } catch (\Throwable $th) {
                $m->status = 'Failed';
                $m->error_message_message = 'cURL Error: ' . curl_error($ch)." URL: $url";
                $m->save();
                throw new \Exception('cURL Error: ' . curl_error($ch)." URL: $url");
            }

            curl_close($ch);

            $m->response = $response;

            // Extract useful information from response
            if (self::isMessageSent($response)) {
                $m->status = 'Sent';

                // Deduct wallet balance based on message length
                $no_of_messages = max(1, ceil(strlen($m->message_body) / 160));
                $wallet_rec = new WalletRecord();
                $wallet_rec->enterprise_id = $m->enterprise_id;
                $wallet_rec->amount = $no_of_messages * -50;
                $wallet_rec->details = "Sent $no_of_messages messages to $m->receiver_number. ref: $m->id";
                $wallet_rec->save();
            } else {
                $m->status = 'Failed';
                $m->error_message_message = "Failed to send message. Response: $response. URL: $url";
                $m->save();
                return $m->error_message_message;
            }
        } catch (\Throwable $th) {
            $m->status = 'Failed';
            $m->error_message_message = "Error: {$th->getMessage()}, URL: $url";
            $m->save();
            return $m->error_message_message;
        }

        return $m->status;
    }

    /**
     * Parses the response to determine if the message was successfully sent.
     */
    private static function isMessageSent($response)
    {
        // Check if response contains "Send ok"
        return preg_match('/Send ok:/', $response);
    }



    public function bulk_message()
    {
        return $this->belongsTo(BulkMessage::class);
    }
    public function administrator()
    {
        return $this->belongsTo(Administrator::class);
    }
}
