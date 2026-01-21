<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\DirectMessage;
use App\Models\WalletRecord;

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "DATABASE VERIFICATION - SMS SPLITTING\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Get the most recent parent message
$parentMsg = DirectMessage::whereNotNull('total_parts')
    ->whereNull('parent_message_id')
    ->orderBy('id', 'desc')
    ->first();

if ($parentMsg) {
    echo "📧 Parent Message #{$parentMsg->id}:\n";
    echo "  Original Message: " . substr($parentMsg->original_message, 0, 100) . "...\n";
    echo "  Total Parts: {$parentMsg->total_parts}\n";
    echo "  Status: {$parentMsg->status}\n";
    echo "  Response: {$parentMsg->response}\n\n";
    
    // Get child messages
    $children = DirectMessage::where('parent_message_id', $parentMsg->id)->get();
    echo "📨 Child Messages ({$children->count()}):\n";
    foreach ($children as $child) {
        echo "  Part {$child->part_number}/{$child->total_parts}:\n";
        echo "    ID: {$child->id}\n";
        echo "    Status: {$child->status}\n";
        echo "    Length: " . strlen($child->message_body) . " chars\n";
        echo "    Message: " . substr($child->message_body, 0, 60) . "...\n\n";
    }
    
    // Get wallet records
    echo "💰 Wallet Records:\n";
    $walletRecords = WalletRecord::where('enterprise_id', $parentMsg->enterprise_id)
        ->where('details', 'LIKE', "%Parent: #{$parentMsg->id}%")
        ->get();
    
    $totalDeducted = 0;
    foreach ($walletRecords as $wr) {
        echo "  Record #{$wr->id}:\n";
        echo "    Amount: UGX {$wr->amount}\n";
        echo "    Details: " . substr($wr->details, 0, 80) . "...\n\n";
        $totalDeducted += $wr->amount;
    }
    
    echo "📊 Summary:\n";
    echo "  Parts Sent: {$children->count()}\n";
    echo "  Total Deducted: UGX " . abs($totalDeducted) . "\n";
    echo "  Cost per Part: UGX 50\n";
    echo "  Expected Total: UGX " . ($children->count() * 50) . "\n";
    
    if (abs($totalDeducted) == ($children->count() * 50)) {
        echo "  ✅ Billing is correct!\n";
    } else {
        echo "  ❌ Billing mismatch!\n";
    }
}

echo "\n═══════════════════════════════════════════════════════════════\n";
