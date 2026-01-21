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
            // Only fetch from user if receiver_number is not set or too short
            if (empty($m->receiver_number) || strlen(trim($m->receiver_number)) < 7) {
                if (!empty($m->administrator_id)) {
                    $u = Administrator::find($m->administrator_id);
                    if ($u) {
                        // Try phone_number_1 first
                        if (!empty($u->phone_number_1) && strlen(trim($u->phone_number_1)) >= 7) {
                            $m->receiver_number = $u->phone_number_1;
                        } 
                        // If phone_number_1 is not valid, try phone_number_2
                        elseif (!empty($u->phone_number_2) && strlen(trim($u->phone_number_2)) >= 7) {
                            $m->receiver_number = $u->phone_number_2;
                        }
                    }
                }
            }
            
            // Standardize phone number format (handles 07..., 256..., +256...)
            if (!empty($m->receiver_number)) {
                $m->receiver_number = Utils::prepareUgandanPhoneNumber($m->receiver_number);
            }
            
            return $m;
        });
    }

    public static function send_message($m)
    {
        //use send_message_1
        return self::send_message_1($m);
        if ($m->status !== 'Pending') {
            return "Message status is not 'Pending'. Current status: {$m->status}";
        }

        // Validate phone number
        // if (!Utils::validateUgandanPhoneNumber($m->receiver_number)) {
        //     $m->status = 'Failed';
        //     $m->error_message_message = 'Invalid phone number - ' . $m->receiver_number;
        //     $m->save();
        //     return $m->error_message_message;
        // }
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

    /**
     * Send SMS via EUROSATGROUP InstantSMS API with automatic message splitting
     * @param DirectMessage $m The message model instance
     * @return string Status message ('success' or error description)
     */
    public static function send_message_1($m)
    {
        // Clear error message at the start
        $m->error_message_message = null;
        
        if ($m->status !== 'Pending') {
            return "Message status is not 'Pending'. Current status: {$m->status}";
        }

        // Prepare and validate phone number
        // If receiver_number is set and valid, use it; otherwise get from user
        if (empty($m->receiver_number) || strlen(trim($m->receiver_number)) < 7) {
            // Get phone from administrator
            if (!empty($m->administrator_id)) {
                $user = Administrator::find($m->administrator_id);
                if ($user) {
                    // Try phone_number_1 first
                    if (!empty($user->phone_number_1) && strlen(trim($user->phone_number_1)) >= 7) {
                        $m->receiver_number = $user->phone_number_1;
                    } 
                    // If phone_number_1 is invalid, try phone_number_2
                    elseif (!empty($user->phone_number_2) && strlen(trim($user->phone_number_2)) >= 7) {
                        $m->receiver_number = $user->phone_number_2;
                    }
                }
            }
        }

        // Standardize phone number format (handles 07..., 256..., +256...)
        $m->receiver_number = Utils::prepareUgandanPhoneNumber($m->receiver_number);

        // Validate phone number after preparation
        if (!Utils::validateUgandanPhoneNumber($m->receiver_number)) {
            $m->status = 'Failed';
            $m->error_message_message = 'Invalid phone number: ' . $m->receiver_number;
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

        // Calculate message parts and check wallet balance
        $originalMessage = trim($m->message_body);
        $messageParts = self::splitMessage($originalMessage, 160);
        $totalParts = count($messageParts);
        $totalCost = $totalParts * 50;

        // Check wallet balance for all parts
        if ($ent->wallet_balance < $totalCost) {
            $m->status = 'Failed';
            $m->error_message_message = "Insufficient funds. Message requires {$totalParts} parts (UGX {$totalCost}). Current balance: UGX {$ent->wallet_balance}";
            $m->save();
            return $m->error_message_message;
        }

        // If message needs splitting (more than 160 chars), handle multi-part sending
        if ($totalParts > 1) {
            return self::sendSplitMessage($m, $messageParts, $ent);
        }

        // Single message - send directly
        return self::sendSingleMessage($m, $originalMessage, $ent);
    }

    /**
     * Split message into parts based on character limit
     * Handles special characters and ensures smart splitting
     * 
     * @param string $message The message to split
     * @param int $maxLength Maximum length per part (default 160)
     * @return array Array of message parts
     */
    private static function splitMessage($message, $maxLength = 160)
    {
        $message = trim($message);
        $length = mb_strlen($message, 'UTF-8');
        
        // If message fits in one part, return as is
        if ($length <= $maxLength) {
            return [$message];
        }

        $parts = [];
        $remainingMessage = $message;
        $partNumber = 1;
        
        // Calculate total parts needed (for header like "1/3:")
        $estimatedParts = ceil($length / $maxLength);
        $headerLength = strlen("$estimatedParts/$estimatedParts: ");
        $usableLength = $maxLength - $headerLength;

        while (mb_strlen($remainingMessage, 'UTF-8') > 0) {
            if (mb_strlen($remainingMessage, 'UTF-8') <= $usableLength) {
                // Last part
                $parts[] = $remainingMessage;
                break;
            }

            // Try to split at word boundary
            $splitPos = $usableLength;
            $chunk = mb_substr($remainingMessage, 0, $usableLength, 'UTF-8');
            
            // Look for last space or punctuation before limit
            $lastSpace = max(
                mb_strrpos($chunk, ' ', 0, 'UTF-8'),
                mb_strrpos($chunk, '.', 0, 'UTF-8'),
                mb_strrpos($chunk, ',', 0, 'UTF-8'),
                mb_strrpos($chunk, '!', 0, 'UTF-8'),
                mb_strrpos($chunk, '?', 0, 'UTF-8')
            );

            // If we found a good break point and it's not too close to the beginning
            if ($lastSpace !== false && $lastSpace > $usableLength * 0.6) {
                $splitPos = $lastSpace + 1;
            }

            $parts[] = trim(mb_substr($remainingMessage, 0, $splitPos, 'UTF-8'));
            $remainingMessage = trim(mb_substr($remainingMessage, $splitPos, null, 'UTF-8'));
            $partNumber++;
        }

        return $parts;
    }

    /**
     * Send a split message (multiple parts)
     * Creates child messages and sends each part
     * 
     * @param DirectMessage $parentMessage The parent message
     * @param array $messageParts Array of message parts
     * @param Enterprise $ent The enterprise
     * @return string Status message
     */
    private static function sendSplitMessage($parentMessage, $messageParts, $ent)
    {
        $totalParts = count($messageParts);
        
        // Store original message in parent
        $parentMessage->original_message = $parentMessage->message_body;
        $parentMessage->original_message_length = mb_strlen($parentMessage->message_body, 'UTF-8');
        $parentMessage->total_parts = $totalParts;
        // Don't set part_number for parent - leave it null
        $parentMessage->message_body = "[Parent message split into {$totalParts} parts]";
        $parentMessage->status = 'Sent'; // Mark parent as sent
        $parentMessage->save();

        $successCount = 0;
        $failedCount = 0;
        $allMessageIds = [];
        $errors = [];

        // Send each part
        foreach ($messageParts as $index => $partContent) {
            $partNumber = $index + 1;
            $partMessage = "{$partNumber}/{$totalParts}: {$partContent}";

            // Create child message for this part
            $childMessage = new DirectMessage();
            $childMessage->enterprise_id = $parentMessage->enterprise_id;
            $childMessage->bulk_message_id = $parentMessage->bulk_message_id;
            $childMessage->administrator_id = $parentMessage->administrator_id;
            $childMessage->receiver_number = $parentMessage->receiver_number;
            $childMessage->message_body = $partMessage;
            $childMessage->status = 'Pending';
            $childMessage->delivery_time = $parentMessage->delivery_time;
            $childMessage->is_scheduled = $parentMessage->is_scheduled;
            $childMessage->parent_message_id = $parentMessage->id;
            $childMessage->part_number = $partNumber;
            $childMessage->total_parts = $totalParts;
            $childMessage->save();

            // Send this part
            $result = self::sendSingleMessage($childMessage, $partMessage, $ent);
            
            if ($result === 'success') {
                $successCount++;
                $allMessageIds[] = $childMessage->id;
            } else {
                $failedCount++;
                $errors[] = "Part {$partNumber}: " . $result;
            }
            
            // Add small delay between API calls to avoid rate limiting
            if ($index < count($messageParts) - 1) {
                sleep(1); // 1 second delay between messages
            }
        }

        // Update parent with summary
        if ($successCount === $totalParts) {
            $parentMessage->response = "All {$totalParts} parts sent successfully. Child message IDs: " . implode(', ', $allMessageIds);
            $parentMessage->save();
            return 'success';
        } elseif ($successCount > 0) {
            $parentMessage->status = 'Partial';
            $parentMessage->response = "Sent {$successCount}/{$totalParts} parts. Child IDs: " . implode(', ', $allMessageIds);
            $parentMessage->error_message_message = "Failed parts: " . implode('; ', $errors);
            $parentMessage->save();
            return "Partial success: {$successCount}/{$totalParts} parts sent";
        } else {
            $parentMessage->status = 'Failed';
            $parentMessage->error_message_message = "All parts failed: " . implode('; ', $errors);
            $parentMessage->save();
            return "All parts failed: " . implode('; ', $errors);
        }
    }

    /**
     * Send a single SMS message (non-split)
     * 
     * @param DirectMessage $m The message model instance
     * @param string $messageText The message text to send
     * @param Enterprise $ent The enterprise
     * @return string Status message
     */
    private static function sendSingleMessage($m, $messageText, $ent)
    {
        // Get credentials from environment
        $username = env('EUROSATGROUP_USERNAME');
        $password = env('EUROSATGROUP_PASSWORD');

        if (empty($username) || empty($password)) {
            $m->status = 'Failed';
            $m->error_message_message = 'EUROSATGROUP credentials not configured.';
            $m->save();
            return $m->error_message_message;
        }

        // Prepare message and phone number for API
        $msg = trim($messageText);
        $msg = urlencode($msg);
        
        // Remove + prefix for API (phone is already standardized to +256...)
        $receiver_number = str_replace('+', '', trim($m->receiver_number));

        // Construct API URL
        $url = "https://instantsms.eurosatgroup.com/api/smsjsonapi.aspx?unm=" . urlencode($username)
            . "&ps=" . urlencode($password)
            . "&message=" . $msg
            . "&receipients=" . $receiver_number;

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
                $m->error_message_message = "Empty response body from EUROSATGROUP API.";
                $m->save();
                return $m->error_message_message;
            }

            // Get response body as string
            try {
                $response = $body->getContents();
            } catch (\Throwable $th) {
                $m->status = 'Failed';
                $m->error_message_message = "Error reading response body: {$th->getMessage()}";
                $m->save();
                return $m->error_message_message;
            }

            // Parse JSON response
            $json = null;
            try {
                $json = json_decode($response, true);
                
                // If json_decode returns null, it means the response is not valid JSON
                if ($json === null && $response !== 'null') {
                    $m->status = 'Failed';
                    $responsePreview = substr($response, 0, 300);
                    $m->error_message_message = "JSON decode failed. Response is not valid JSON: " . $responsePreview;
                    $m->save();
                    return $m->error_message_message;
                }
            } catch (\Throwable $th) {
                $m->status = 'Failed';
                $m->error_message_message = "Error decoding JSON response: {$th->getMessage()} Response: $response";
                $m->save();
                return $m->error_message_message;
            }

            if (!is_array($json)) {
                $m->status = 'Failed';
                $responsePreview = substr($response, 0, 200); // Show first 200 chars
                $m->error_message_message = "Unexpected API response format. Response: " . $responsePreview;
                $m->save();
                return $m->error_message_message;
            }

            // Check response code
            $code = isset($json['code']) ? $json['code'] : null;
            $status = isset($json['status']) ? $json['status'] : null;
            $messageID = isset($json['messageID']) ? $json['messageID'] : null;
            $contacts = isset($json['contacts']) ? $json['contacts'] : null;

            // Successful response: code 200 and status Delivered
            if ($code == '200' && $status == 'Delivered') {
                $m->status = 'Sent';
                $m->error_message_message = null; // Clear any previous error

                // Store enhanced response with parsed information
                $responseInfo = "Response: $response";
                if (!empty($messageID)) {
                    $responseInfo .= " | API Message ID: " . $messageID;
                }
                if (!empty($contacts)) {
                    $responseInfo .= " | Contacts: " . $contacts;
                }
                $m->response = $responseInfo;

                // Deduct wallet balance - SINGLE SMS PART = 50 UGX
                $wallet_rec = new WalletRecord();
                $wallet_rec->enterprise_id = $m->enterprise_id;
                $wallet_rec->amount = -50; // Each part costs 50 UGX
                
                // Create detailed wallet description
                if ($m->parent_message_id) {
                    $wallet_rec->details = "SMS Part {$m->part_number}/{$m->total_parts} to {$m->receiver_number} via EUROSATGROUP. Parent: #{$m->parent_message_id}, Child: #{$m->id}, API ID: " . ($messageID ?? 'N/A');
                } else {
                    $wallet_rec->details = "SMS to {$m->receiver_number} via EUROSATGROUP. Msg ID: #{$m->id}, API ID: " . ($messageID ?? 'N/A');
                }
                $wallet_rec->save();

                $m->save();
                return 'success';
            } else {
                // Handle error responses
                $m->status = 'Failed';

                // Extract error message
                $errorMessage = '';
                if (isset($json['message'])) {
                    $errorMessage = $json['message'];
                } elseif (isset($json['Message'])) {
                    $errorMessage = $json['Message'];
                } else {
                    $errorMessage = $status ?? 'Unknown error';
                }

                // Add code information
                if (!empty($code)) {
                    $errorMessage = "Code $code: " . $errorMessage;
                }

                $m->error_message_message = "EUROSATGROUP API Error - " . $errorMessage . " Response: $response";
                $m->save();
                return $m->error_message_message;
            }
        } catch (GuzzleException $e) {
            $m->status = 'Failed';
            $m->error_message_message = 'HTTP Error (EUROSATGROUP): ' . $e->getMessage();
            $m->save();
            return $m->error_message_message;
        } catch (\Throwable $th) {
            $m->status = 'Failed';
            $m->error_message_message = "Error (EUROSATGROUP): {$th->getMessage()}";
            $m->save();
            return $m->error_message_message;
        }

        return $m->status;
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
