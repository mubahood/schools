<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

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

            // Initialize Guzzle HTTP client
            $client = new Client([
                'verify' => false, // Equivalent to CURLOPT_SSL_VERIFYPEER => false
                'timeout' => 30,   // Equivalent to CURLOPT_TIMEOUT => 30
                'allow_redirects' => true, // Equivalent to CURLOPT_FOLLOWLOCATION => true
            ]);

            // Make the GET request
            $guzzleResponse = $client->get($url);

            $response = null;
            $body = null;
            if ($guzzleResponse) {
                $body = $guzzleResponse->getBody();
            }

            if ($body == null) {
                $m->status = 'Failed';
                $m->error_message_message = "Empty response body from API. URL: $url";
                $m->save();
                return $m->error_message_message;
            }
            // Get response body as string
            try {
                $response = $body->getContents();
            } catch (\Throwable $th) {
                $m->status = 'Failed';
                $m->error_message_message = "Error reading response body: {$th->getMessage()} URL: $url";
                $m->save();
                return $m->error_message_message;
            }

            $json = null;
            try {
                $json = json_decode($response, true);
            } catch (\Throwable $th) {
                $m->status = 'Failed';
                $m->error_message_message = "Error decoding JSON response: {$th->getMessage()} URL: $url Response: $response";
                $m->save();
                return $m->error_message_message;
            }


            if (!isset($json['socnetblast']) || !is_array($json['socnetblast'])) {
                $m->status = 'Failed';
                $m->error_message_message = "Unexpected API response format. URL: $url Response: $response";
                $m->save();
                return $m->error_message_message;
            }
            $parsedResponse = $json['socnetblast'];

            if (!isset($parsedResponse['status']) || !is_string($parsedResponse['status'])) {
                $m->status = 'Failed';
                $m->error_message_message = "Missing or invalid status in API response. URL: $url Response: $response";
                $m->save();
                return $m->error_message_message;
            }

            //Login ok
            $statusSlipcs = explode(" ", $parsedResponse['status']);
            //if $statusSlipcs does not contain ok
            if (in_array('ok', $statusSlipcs) === false) {
                $m->status = 'Failed';
                $m->error_message_message = "Login failed. Status: " . $parsedResponse['status'] . " URL: $url Response: $response";
                $m->save();
                return $m->error_message_message;
            }

            $info = isset($parsedResponse['info']) && is_string($parsedResponse['info']) ? $parsedResponse['info'] : '';
            $infoSlipcs = explode(" ", $info);
            //if $infoSlipcs does not contain ok
            if (in_array('ok:', $infoSlipcs) === false) {
                $m->status = 'Failed';
                $m->error_message_message = "Message sending failed. Info: " . $info . " URL: $url Response: $response";
                $m->save();
                return $m->error_message_message;
            }
            $m->status = 'Sent';

            // Store enhanced response with parsed information
            $responseInfo = "Response: $response";
            if (isset($parsedResponse['messageId']) && is_scalar($parsedResponse['messageId']) && !empty($parsedResponse['messageId'])) {
                $responseInfo .= " | API Message ID: " . $parsedResponse['messageId'];
            }
            if (isset($parsedResponse['credit']) && is_scalar($parsedResponse['credit']) && !empty($parsedResponse['credit'])) {
                $responseInfo .= " | Credit Remaining: " . $parsedResponse['credit'];
            }
            $m->response = $responseInfo;

            // Deduct wallet balance based on message length
            $no_of_messages = max(1, ceil(strlen($m->message_body) / 160));
            $wallet_rec = new WalletRecord();
            $wallet_rec->enterprise_id = $m->enterprise_id;
            $wallet_rec->amount = $no_of_messages * -50;
            $messageId = isset($parsedResponse['messageId']) && is_scalar($parsedResponse['messageId']) ? $parsedResponse['messageId'] : 'N/A';
            $wallet_rec->details = "Sent $no_of_messages messages to $m->receiver_number. ref: $m->id, API Message ID: " . $messageId;
            $wallet_rec->save();
            return 'success';
        } catch (GuzzleException $e) {
            $m->status = 'Failed';
            $m->error_message_message = 'HTTP Error: ' . $e->getMessage() . " URL: $url";
            $m->save();
            return $m->error_message_message;
        } catch (\Throwable $th) {
            $m->status = 'Failed';
            $m->error_message_message = "Error: {$th->getMessage()}, URL: $url";
            $m->save();
            return $m->error_message_message;
        }

        return $m->status;
    }

    /**
     * Parses the API response to extract status, messageId, info, and credit.
     * 
     * @param string $response The raw API response
     * @return array Parsed response with keys: success, status, messageId, info, credit, error
     */
    private static function parseApiResponse($response)
    {
        $result = [
            'success' => false,
            'status' => null,
            'messageId' => null,
            'info' => null,
            'credit' => null,
            'error' => null
        ];

        try {
            // Decode JSON response
            $decoded = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                // If JSON parsing fails, try legacy string matching
                if (preg_match('/Send ok:/', $response)) {
                    $result['success'] = true;
                    $result['info'] = 'Send ok (legacy format)';
                    return $result;
                }

                $result['error'] = 'Invalid JSON response: ' . json_last_error_msg();
                return $result;
            }

            // Check if response has the expected structure
            if (isset($decoded['socnetblast'])) {
                $data = $decoded['socnetblast'];

                // Extract status
                $result['status'] = $data['status'] ?? null;

                // Extract messageId
                $result['messageId'] = $data['messageId'] ?? null;

                // Extract info
                $result['info'] = $data['info'] ?? null;

                // Extract credit
                $result['credit'] = $data['credit'] ?? null;

                // Determine success based on status and info
                if (!empty($result['status']) && stripos($result['status'], 'Login ok') !== false) {
                    if (!empty($result['info']) && stripos($result['info'], 'Send ok') !== false) {
                        $result['success'] = true;
                    } else {
                        $result['error'] = $result['info'] ?? 'Message sending failed';
                    }
                } else {
                    $result['error'] = $result['status'] ?? 'Login failed';
                }
            } else {
                $result['error'] = 'Unexpected response format';
            }
        } catch (\Throwable $e) {
            $result['error'] = 'Error parsing response: ' . $e->getMessage();
        }

        return $result;
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
