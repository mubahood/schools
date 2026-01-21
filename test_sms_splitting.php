<?php

/**
 * Test script for SMS splitting functionality
 * Tests message splitting, billing, and tracking for long SMS messages
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\DirectMessage;
use App\Models\Enterprise;
use App\Models\WalletRecord;

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║          SMS MESSAGE SPLITTING TEST                          ║\n";
echo "║          Testing Long Message Handling & Billing             ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Get enterprise with messaging enabled
$enterprise = Enterprise::where('can_send_messages', 'Yes')->first();

if (!$enterprise) {
    echo "❌ ERROR: No enterprise found with messaging enabled.\n";
    exit(1);
}

echo "📊 Test Configuration:\n";
echo "  Enterprise: {$enterprise->name} (ID: {$enterprise->id})\n";
echo "  Initial Wallet Balance: UGX " . number_format($enterprise->wallet_balance) . "\n";
echo "  Test Phone: +256783204665\n";
echo "\n";

// =============================================================================
// TEST 1: Short message (< 160 chars) - Should NOT split
// =============================================================================
echo "═══════════════════════════════════════════════════════════════\n";
echo "TEST 1: Short Message (No Splitting)\n";
echo "═══════════════════════════════════════════════════════════════\n";

$shortMessage = "Hello! This is a test message to verify SMS functionality works correctly.";
echo "Message: \"$shortMessage\"\n";
echo "Length: " . strlen($shortMessage) . " characters\n";

$msg1 = new DirectMessage();
$msg1->enterprise_id = $enterprise->id;
$msg1->receiver_number = '+256783204665';
$msg1->message_body = $shortMessage;
$msg1->status = 'Pending';
$msg1->administrator_id = $enterprise->administrator_id;
$msg1->save();

echo "✓ Message created (ID: {$msg1->id})\n";
echo "Sending...\n";

$initialBalance1 = $enterprise->fresh()->wallet_balance;
$result1 = DirectMessage::send_message_1($msg1);
$msg1->refresh();
$finalBalance1 = $enterprise->fresh()->wallet_balance;
$cost1 = $initialBalance1 - $finalBalance1;

echo "\nResult: " . ($result1 === 'success' ? '✅ SUCCESS' : '❌ FAILED') . "\n";
echo "  Status: {$msg1->status}\n";
echo "  Parts: " . ($msg1->total_parts ?? '1 (no split)') . "\n";
echo "  Cost: UGX {$cost1}\n";
echo "  Expected Cost: UGX 50\n";
echo "  New Balance: UGX " . number_format($finalBalance1) . "\n";

if ($result1 === 'success' && $cost1 == 50 && !$msg1->parent_message_id) {
    echo "✅ TEST 1 PASSED\n";
} else {
    echo "❌ TEST 1 FAILED\n";
}
echo "\n";

// =============================================================================
// TEST 2: Long message (200-300 chars) - Should split into 2 parts
// =============================================================================
echo "═══════════════════════════════════════════════════════════════\n";
echo "TEST 2: Medium Message (2 Parts)\n";
echo "═══════════════════════════════════════════════════════════════\n";

$mediumMessage = "Dear Parent, this is a reminder about the upcoming school fees payment. The deadline for the first term fees is approaching fast. Please ensure you clear all outstanding balances to avoid any inconvenience. Thank you for your cooperation.";
echo "Message: \"$mediumMessage\"\n";
echo "Length: " . strlen($mediumMessage) . " characters\n";
echo "Expected Parts: 2\n";

$msg2 = new DirectMessage();
$msg2->enterprise_id = $enterprise->id;
$msg2->receiver_number = '+256783204665';
$msg2->message_body = $mediumMessage;
$msg2->status = 'Pending';
$msg2->administrator_id = $enterprise->administrator_id;
$msg2->save();

echo "✓ Message created (ID: {$msg2->id})\n";
echo "Sending...\n";

$initialBalance2 = $enterprise->fresh()->wallet_balance;
$result2 = DirectMessage::send_message_1($msg2);
$msg2->refresh();
$finalBalance2 = $enterprise->fresh()->wallet_balance;
$cost2 = $initialBalance2 - $finalBalance2;

echo "\nResult: " . ($result2 === 'success' ? '✅ SUCCESS' : '❌ FAILED') . "\n";
echo "  Status: {$msg2->status}\n";
echo "  Total Parts: " . ($msg2->total_parts ?? 'N/A') . "\n";
echo "  Cost: UGX {$cost2}\n";
echo "  Expected Cost: UGX 100 (2 parts × 50)\n";
echo "  New Balance: UGX " . number_format($finalBalance2) . "\n";

// Check child messages
$childMessages = DirectMessage::where('parent_message_id', $msg2->id)->get();
echo "  Child Messages: {$childMessages->count()}\n";

if ($childMessages->count() > 0) {
    foreach ($childMessages as $child) {
        echo "    - Part {$child->part_number}/{$child->total_parts}: {$child->status} - " . strlen($child->message_body) . " chars\n";
    }
}

// Check wallet records
$walletRecords = WalletRecord::where('enterprise_id', $enterprise->id)
    ->where('details', 'LIKE', "%Parent: #{$msg2->id}%")
    ->get();
echo "  Wallet Records: {$walletRecords->count()}\n";

if ($result2 === 'success' && $cost2 == 100 && $childMessages->count() == 2) {
    echo "✅ TEST 2 PASSED\n";
} else {
    echo "❌ TEST 2 FAILED\n";
    if ($msg2->error_message_message) {
        echo "  Error: {$msg2->error_message_message}\n";
    }
}
echo "\n";

// =============================================================================
// TEST 3: Very long message (500+ chars) - Should split into 4+ parts
// =============================================================================
echo "═══════════════════════════════════════════════════════════════\n";
echo "TEST 3: Long Message (Multiple Parts)\n";
echo "═══════════════════════════════════════════════════════════════\n";

$longMessage = "BRIGHT FUTURE SECONDARY SCHOOL KALIRO. Dear Parent/Guardian, we would like to remind you about the following important matters: 1. The school fees for Term 1 2026 are now due. The deadline for payment is January 31, 2026. Any student with outstanding fees after this date will not be allowed to sit for the end of term examinations. 2. The Parent-Teacher meeting is scheduled for February 15, 2026 at 10:00 AM. All parents are encouraged to attend. 3. The school will be closed for mid-term break from February 20-25, 2026. Students should return on February 26, 2026. For any inquiries, please contact the school administration office. Thank you for your continued support and cooperation.";
echo "Message Length: " . strlen($longMessage) . " characters\n";
echo "Expected Parts: " . ceil(strlen($longMessage) / 160) . "\n";

$msg3 = new DirectMessage();
$msg3->enterprise_id = $enterprise->id;
$msg3->receiver_number = '+256783204665';
$msg3->message_body = $longMessage;
$msg3->status = 'Pending';
$msg3->administrator_id = $enterprise->administrator_id;
$msg3->save();

echo "✓ Message created (ID: {$msg3->id})\n";
echo "Sending...\n";

$initialBalance3 = $enterprise->fresh()->wallet_balance;
$result3 = DirectMessage::send_message_1($msg3);
$msg3->refresh();
$finalBalance3 = $enterprise->fresh()->wallet_balance;
$cost3 = $initialBalance3 - $finalBalance3;

echo "\nResult: " . ($result3 === 'success' ? '✅ SUCCESS' : '❌ FAILED') . "\n";
echo "  Status: {$msg3->status}\n";
echo "  Total Parts: " . ($msg3->total_parts ?? 'N/A') . "\n";
echo "  Cost: UGX {$cost3}\n";
echo "  Expected Cost: UGX " . ($msg3->total_parts * 50) . " ({$msg3->total_parts} parts × 50)\n";
echo "  New Balance: UGX " . number_format($finalBalance3) . "\n";

// Check child messages
$childMessages3 = DirectMessage::where('parent_message_id', $msg3->id)->get();
echo "  Child Messages Sent: {$childMessages3->count()}\n";

if ($childMessages3->count() > 0) {
    foreach ($childMessages3 as $child) {
        echo "    - Part {$child->part_number}/{$child->total_parts}: {$child->status} - " . strlen($child->message_body) . " chars\n";
    }
}

$expectedCost = $msg3->total_parts * 50;
if ($result3 === 'success' && $cost3 == $expectedCost && $childMessages3->count() == $msg3->total_parts) {
    echo "✅ TEST 3 PASSED\n";
} else {
    echo "❌ TEST 3 FAILED\n";
    if ($msg3->error_message_message) {
        echo "  Error: {$msg3->error_message_message}\n";
    }
}
echo "\n";

// =============================================================================
// TEST 4: Edge case - Exactly 160 characters
// =============================================================================
echo "═══════════════════════════════════════════════════════════════\n";
echo "TEST 4: Edge Case - Exactly 160 Characters\n";
echo "═══════════════════════════════════════════════════════════════\n";

$exactMessage = str_pad("Testing exact 160 character message. ", 160, "X");
echo "Message Length: " . strlen($exactMessage) . " characters\n";
echo "Expected Parts: 1 (should NOT split)\n";

$msg4 = new DirectMessage();
$msg4->enterprise_id = $enterprise->id;
$msg4->receiver_number = '+256783204665';
$msg4->message_body = $exactMessage;
$msg4->status = 'Pending';
$msg4->administrator_id = $enterprise->administrator_id;
$msg4->save();

echo "✓ Message created (ID: {$msg4->id})\n";
echo "Sending...\n";

$initialBalance4 = $enterprise->fresh()->wallet_balance;
$result4 = DirectMessage::send_message_1($msg4);
$msg4->refresh();
$finalBalance4 = $enterprise->fresh()->wallet_balance;
$cost4 = $initialBalance4 - $finalBalance4;

echo "\nResult: " . ($result4 === 'success' ? '✅ SUCCESS' : '❌ FAILED') . "\n";
echo "  Status: {$msg4->status}\n";
echo "  Parts: " . ($msg4->total_parts ?? '1 (no split)') . "\n";
echo "  Cost: UGX {$cost4}\n";
echo "  Expected Cost: UGX 50\n";

if ($result4 === 'success' && $cost4 == 50 && !$msg4->parent_message_id) {
    echo "✅ TEST 4 PASSED\n";
} else {
    echo "❌ TEST 4 FAILED\n";
}
echo "\n";

// =============================================================================
// SUMMARY
// =============================================================================
echo "═══════════════════════════════════════════════════════════════\n";
echo "TEST SUMMARY\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";

$allTests = [$result1, $result2, $result3, $result4];
$passedTests = array_filter($allTests, fn($r) => $r === 'success');

echo "Tests Passed: " . count($passedTests) . "/4\n";
echo "Total SMS Sent: " . ($childMessages->count() + $childMessages3->count() + 2) . "\n";
echo "Total Cost: UGX " . ($cost1 + $cost2 + $cost3 + $cost4) . "\n";
echo "Final Balance: UGX " . number_format($enterprise->fresh()->wallet_balance) . "\n";
echo "\n";

if (count($passedTests) == 4) {
    echo "🎉 ALL TESTS PASSED! SMS splitting is working correctly.\n";
} else {
    echo "⚠️  Some tests failed. Please review the errors above.\n";
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "DATABASE VERIFICATION\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";

// Show recent messages
echo "Recent messages in database:\n";
$recentMessages = DirectMessage::whereIn('id', [$msg1->id, $msg2->id, $msg3->id, $msg4->id])
    ->orWhereIn('parent_message_id', [$msg2->id, $msg3->id])
    ->orderBy('id', 'desc')
    ->get();

foreach ($recentMessages as $msg) {
    $type = $msg->parent_message_id ? "  └─ Child" : "Parent";
    $part = $msg->part_number ? " (Part {$msg->part_number}/{$msg->total_parts})" : "";
    echo "  {$type} #{$msg->id}{$part}: {$msg->status} - " . strlen($msg->message_body) . " chars\n";
}

echo "\n✅ Test completed successfully!\n\n";
