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
        if ($m->status != 'Pending') {
            return;
        }

        if (!Utils::validateUgandanPhoneNumber($m->receiver_number)) {
            $m->status = 'Failed';
            $m->error_message_message = 'Invalid phone number' . " - $m->receiver_number.";
            $m->save();
            return;
        }

        $ent = Enterprise::find($m->enterprise_id);
        if ($ent == null) {
            $m->status = 'Failed';
            $m->error_message_message = 'Enterprise is not active.';
            $m->save();
            return;
        }

        if ($ent->wallet_balance < 50) {
            $m->status = 'Failed';
            $m->error_message_message = 'Insufficient funds.';
            $m->save();
            return;
        }

        if ($m->message_body == null || strlen($m->message_body) < 1) {
            $m->status = 'Failed';
            $m->error_message_message = 'Message body is empty.';
            $m->save();
            return;
        }
        if ($ent->can_send_messages != 'Yes') {
            $m->status = 'Failed';
            $m->error_message_message = 'Messages are not enabled.';
            $m->save();
            return;
        }
        // $m->status = 'Sent';
        // $m->save();
        // return;
        $url = "https://www.socnetsolutions.com/projects/bulk/amfphp/services/blast.php?username=mubaraka&passwd=muh1nd0@2023";
        //$m->receiver_number = '+256706638494';
        $url .= "&msg=" . trim($m->message_body);
        $url .= "&numbers=" . $m->receiver_number;

        try {
            $result = file_get_contents($url, false, stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => 'Content-Type: application/json',
                    /* 'content' => json_encode($m), */
                ],
            ]));
            $m->response = $result;
            if (str_contains($result, 'Send ok')) {
                $m->status = 'Sent';
                $no_of_messages  = 1;
                if (strlen($m->message_body) > 160) {
                    $no_of_messages = ceil(strlen($m->message_body) / 160);
                }
                $wallet_rec = new WalletRecord();
                $wallet_rec->enterprise_id = $m->enterprise_id;
                $wallet_rec->amount = $no_of_messages * -50;
                $wallet_rec->details = "Sent $no_of_messages messages to $m->receiver_number. ref: $m->id";
                $wallet_rec->save();
            } else {
                $m->status = 'Failed';
            }
            $m->save();
        } catch (\Throwable $th) {
            $m->status = 'Failed';
            $error_message = $th->getMessage();
            $m->error_message_message = "error: $error_message, url: $url";
            $m->save();
        }
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
