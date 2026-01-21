<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\DirectMessage;
use App\Models\Enterprise;

echo "\n=== Sending Furaha Message ===\n\n";

// Get Bright Future Secondary School (ID: 9)
$enterprise = Enterprise::find(9);
if (!$enterprise) {
    die("❌ Enterprise not found!\n");
}

echo "Enterprise: {$enterprise->name} (ID: {$enterprise->id})\n";
echo "Current Balance: " . number_format($enterprise->wallet_balance ?? 0, 0) . " UGX\n";

// Ensure sufficient funds
if (($enterprise->wallet_balance ?? 0) < 500) {
    echo "⚠️  Adding 500 UGX to wallet for testing...\n";
    $wallet = new \App\Models\WalletRecord();
    $wallet->enterprise_id = 9;
    $wallet->amount = 500;
    $wallet->details = "Test funding for Furaha SMS";
    $wallet->save();
    $enterprise->updateWalletBalance(); // Refresh wallet balance
    $enterprise->refresh();
    echo "New Balance: " . number_format($enterprise->wallet_balance ?? 0, 0) . " UGX\n";
}
echo "\n";

// Create the message
$messageText = "As Bright Future SS, we've partnered with Furaha to offer school fees financing. with schoolpay, MTN & Airtel, enjoy instant affordable payments direct to school. Download Furuha app on Google play/App store for up to 2M or dial *165*80‡ option 5. call 0326220200/0781917795/0774469353";

$message = new DirectMessage();
$message->enterprise_id = 9;
$message->administrator_id = 1; // Set admin ID
$message->receiver_number = '+256781917795';
$message->message_body = $messageText;
$message->status = 'Pending';
$message->save();

echo "Message Length: " . strlen($messageText) . " characters\n";
echo "Message ID: #" . $message->id . "\n";
echo "Receiver: {$message->receiver_number}\n\n";

// Calculate expected cost
$parts = ceil(strlen($messageText) / 160);
$cost = $parts * 50;
echo "Expected Parts: {$parts}\n";
echo "Expected Cost: {$cost} UGX\n\n";

echo "Sending message...\n\n";

// Send the message
try {
    $result = DirectMessage::send_message_1($message);
    
    // Refresh to get updated data
    $message->refresh();
    
    echo "✅ Result: {$result}\n";
    echo "Status: {$message->status}\n";
    
    if ($message->total_parts && $message->total_parts > 1) {
        echo "Total Parts: {$message->total_parts}\n";
        echo "Parent Message ID: #{$message->id}\n";
        
        // Get child messages
        $children = DirectMessage::where('parent_message_id', $message->id)
            ->orderBy('part_number')
            ->get();
        
        echo "\nChild Messages:\n";
        foreach ($children as $child) {
            echo "  Part {$child->part_number}/{$child->total_parts}: ID #{$child->id}, Status: {$child->status}\n";
        }
    }
    
    // Check wallet deduction
    $enterprise->refresh();
    echo "\nFinal Balance: " . number_format($enterprise->wallet_balance ?? 0, 0) . " UGX\n";
    
    echo "\n✅ Message sent successfully!\n\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
