<?php

/*
 * ADD ATTENDANCE DASHBOARD TO ADMIN MENU
 * 
 * Run this script to add the attendance dashboard menu item to your Laravel Admin.
 * This should be run ONCE after installing the attendance dashboard.
 * 
 * Instructions:
 * 1. Run: php add_attendance_menu.php
 * 2. The attendance dashboard will appear in the admin menu
 * 3. Access it at: /attendance-dashboard
 */

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Load Laravel application
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    // Check if menu item already exists
    $existingMenu = DB::table('admin_menu')
        ->where('uri', 'attendance-dashboard')
        ->first();

    if ($existingMenu) {
        echo "✅ Attendance Dashboard menu item already exists!\n";
        echo "🔗 Access it at: /admin/attendance-dashboard\n";
        exit(0);
    }

    // Find a good parent menu ID (usually main dashboard or reports section)
    $parentMenu = DB::table('admin_menu')
        ->where('title', 'LIKE', '%Dashboard%')
        ->orWhere('title', 'LIKE', '%Report%')
        ->first();

    $parentId = $parentMenu ? $parentMenu->id : 0;

    // Get the next order number
    $maxOrder = DB::table('admin_menu')->max('order') ?? 0;

    // Insert the attendance dashboard menu item
    $menuId = DB::table('admin_menu')->insertGetId([
        'parent_id' => $parentId,
        'order' => $maxOrder + 1,
        'title' => 'Attendance Analytics',
        'icon' => 'fa-chart-bar',
        'uri' => 'attendance-dashboard',
        'permission' => null,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
    ]);

    echo "✅ Successfully added Attendance Dashboard to admin menu!\n";
    echo "📊 Menu ID: {$menuId}\n";
    echo "🔗 Access it at: /admin/attendance-dashboard\n";
    echo "📋 Features included:\n";
    echo "   - Gender-based attendance statistics\n";
    echo "   - Class and stream analytics\n";
    echo "   - Time-based attendance trends\n";
    echo "   - Student attendance rankings\n";
    echo "   - Perfect attendance tracking\n";
    echo "   - High absence alerts\n";
    echo "   - Interactive charts and graphs\n";
    echo "   - Print-friendly reports\n";
    echo "\n🎉 Attendance Dashboard is ready to use!\n";

} catch (Exception $e) {
    echo "❌ Error adding attendance dashboard menu: " . $e->getMessage() . "\n";
    echo "\n💡 Manual Setup Instructions:\n";
    echo "1. Login to Laravel Admin\n";
    echo "2. Go to Admin > Menu\n";
    echo "3. Add new menu item:\n";
    echo "   - Title: Attendance Analytics\n";
    echo "   - Icon: fa-chart-bar\n";
    echo "   - URI: attendance-dashboard\n";
    echo "4. Save and access at /admin/attendance-dashboard\n";
}
?>