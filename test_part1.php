<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\DirectMessage;

echo "\n=== Testing Part 1 Message ===\n\n";

// Create test message with Part 1's exact content
$message = new DirectMessage();
$message->enterprise_id = 9;
$message->administrator_id = 1;
$message->receiver_number = '+256783204665'; // Use test number
$message->message_body = "1/2: As Bright Future SS, we've partnered with Furaha to offer school fees financing. with schoolpay, MTN & Airtel, enjoy instant affordable payments direct to";
$message->status = 'Pending';
$message->save();

echo "Test Message ID: #{$message->id}\n";
echo "Message Length: " . strlen($message->message_body) . " characters\n";
echo "Message: {$message->message_body}\n\n";

echo "Sending...\n\n";

$result = DirectMessage::send_message_1($message);
$message->refresh();

echo "Result: {$result}\n";
echo "Status: {$message->status}\n";
if ($message->error_message_message) {
    echo "Error: {$message->error_message_message}\n";
}
if ($message->response) {
    echo "Response: {$message->response}\n";
}

echo "\n";
