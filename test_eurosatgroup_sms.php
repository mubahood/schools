<?php

/**
 * Test script for EUROSATGROUP SMS API
 * This script tests the send_message_1 function
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\DirectMessage;
use App\Models\Enterprise;

echo "=================================\n";
echo "EUROSATGROUP SMS API TEST\n";
echo "=================================\n\n";

// Get the first active enterprise
$enterprise = Enterprise::where('can_send_messages', 'Yes')->first();

if (!$enterprise) {
    echo "ERROR: No active enterprise found with messaging enabled.\n";
    exit(1);
}

echo "Using Enterprise: {$enterprise->name} (ID: {$enterprise->id})\n";
echo "Current Wallet Balance: {$enterprise->wallet_balance}\n\n";

// Create a test message
$message = new DirectMessage();
$message->enterprise_id = $enterprise->id;
$message->receiver_number = '+256783204665'; // Test number provided
$message->message_body = 'Hello! This is a test message from EUROSATGROUP SMS API. Testing functionality.';
$message->status = 'Pending';
$message->administrator_id = 1; // Adjust if needed

echo "Creating test message...\n";
echo "Receiver: {$message->receiver_number}\n";
echo "Message: {$message->message_body}\n";
echo "Message Length: " . strlen($message->message_body) . " characters\n\n";

// Save the message first
try {
    $message->save();
    echo "Message saved with ID: {$message->id}\n\n";
} catch (\Exception $e) {
    echo "ERROR saving message: {$e->getMessage()}\n";
    exit(1);
}

// Send the message using the new send_message_1 function
echo "Sending message via EUROSATGROUP API...\n";
echo "-----------------------------------\n";

try {
    $result = DirectMessage::send_message_1($message);
    
    echo "\nResult: $result\n\n";
    
    // Reload the message to see updated status
    $message->refresh();
    
    echo "Final Status: {$message->status}\n";
    
    if ($message->status == 'Sent') {
        echo "✓ SUCCESS! Message sent successfully!\n";
        echo "Response: {$message->response}\n";
    } else {
        echo "✗ FAILED!\n";
        echo "Error: {$message->error_message_message}\n";
    }
    
    // Check wallet balance after sending
    $enterprise->refresh();
    echo "\nWallet Balance After: {$enterprise->wallet_balance}\n";
    
} catch (\Exception $e) {
    echo "EXCEPTION: {$e->getMessage()}\n";
    echo "Stack Trace:\n{$e->getTraceAsString()}\n";
}

echo "\n=================================\n";
echo "TEST COMPLETED\n";
echo "=================================\n";
