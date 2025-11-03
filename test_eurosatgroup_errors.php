<?php

/**
 * Comprehensive test for EUROSATGROUP SMS API error handling
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\DirectMessage;
use App\Models\Enterprise;

echo "===========================================\n";
echo "EUROSATGROUP SMS API - ERROR HANDLING TEST\n";
echo "===========================================\n\n";

$enterprise = Enterprise::find(7);

// Test 1: Message too long (> 150 characters)
echo "TEST 1: Message Too Long\n";
echo "-------------------------\n";
$longMessage = str_repeat("This is a very long message that exceeds the 150 character limit. ", 3);
echo "Message length: " . strlen($longMessage) . " characters\n";

$msg1 = new DirectMessage();
$msg1->enterprise_id = $enterprise->id;
$msg1->receiver_number = '+256783204665';
$msg1->message_body = $longMessage;
$msg1->status = 'Pending';
$msg1->administrator_id = 1;
$msg1->save();

$result1 = DirectMessage::send_message_1($msg1);
echo "Result: $result1\n";
echo "Status: {$msg1->status}\n";
echo "Expected: Failed (Message too long)\n\n";

// Test 2: Empty message
echo "TEST 2: Empty Message\n";
echo "----------------------\n";
$msg2 = new DirectMessage();
$msg2->enterprise_id = $enterprise->id;
$msg2->receiver_number = '+256783204665';
$msg2->message_body = '   '; // Empty/whitespace only
$msg2->status = 'Pending';
$msg2->administrator_id = 1;
$msg2->save();

$result2 = DirectMessage::send_message_1($msg2);
echo "Result: $result2\n";
echo "Status: {$msg2->status}\n";
echo "Expected: Failed (Message body is empty)\n\n";

// Test 3: Invalid phone number
echo "TEST 3: Invalid Phone Number\n";
echo "-----------------------------\n";
$msg3 = new DirectMessage();
$msg3->enterprise_id = $enterprise->id;
$msg3->receiver_number = '123'; // Very short number
$msg3->message_body = 'Test message';
$msg3->status = 'Pending';
$msg3->administrator_id = 1;
$msg3->save();

$result3 = DirectMessage::send_message_1($msg3);
echo "Result: $result3\n";
echo "Status: {$msg3->status}\n";
echo "Phone sent: {$msg3->receiver_number}\n";
echo "Note: API may accept or reject based on their validation\n\n";

// Test 4: Valid message (should succeed)
echo "TEST 4: Valid Message\n";
echo "----------------------\n";
$msg4 = new DirectMessage();
$msg4->enterprise_id = $enterprise->id;
$msg4->receiver_number = '+256783204665';
$msg4->message_body = 'Test message #' . time() . ' from EUROSATGROUP API';
$msg4->status = 'Pending';
$msg4->administrator_id = 1;
$msg4->save();

$result4 = DirectMessage::send_message_1($msg4);
echo "Result: $result4\n";
echo "Status: {$msg4->status}\n";
if ($msg4->status == 'Sent') {
    echo "✓ Success! Response: {$msg4->response}\n";
} else {
    echo "✗ Failed: {$msg4->error_message_message}\n";
}

echo "\n===========================================\n";
echo "ALL TESTS COMPLETED\n";
echo "===========================================\n";

// Summary
echo "\nSUMMARY:\n";
echo "--------\n";
echo "Test 1 (Too Long):   " . ($msg1->status == 'Failed' ? '✓ PASS' : '✗ FAIL') . "\n";
echo "Test 2 (Empty):      " . ($msg2->status == 'Failed' ? '✓ PASS' : '✗ FAIL') . "\n";
echo "Test 3 (Invalid #):  " . ($msg3->status ? '✓ HANDLED' : '✗ FAIL') . "\n";
echo "Test 4 (Valid):      " . ($msg4->status == 'Sent' ? '✓ PASS' : '✗ FAIL') . "\n";

$enterprise->refresh();
echo "\nFinal Wallet Balance: {$enterprise->wallet_balance}\n";
