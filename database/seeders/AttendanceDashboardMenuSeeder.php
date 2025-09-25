<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceDashboardMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Check if the attendance dashboard menu item already exists
        $existingMenu = DB::table('admin_menu')
            ->where('uri', 'attendance-dashboard')
            ->first();

        if (!$existingMenu) {
            // Get the highest order number to place the new menu item at the end
            $maxOrder = DB::table('admin_menu')->max('order') ?? 0;

            // Insert the attendance dashboard menu item
            DB::table('admin_menu')->insert([
                'parent_id' => 0, // Top-level menu item
                'order' => $maxOrder + 1,
                'title' => 'Attendance Dashboard',
                'icon' => 'fa-calendar-check-o', // Font Awesome icon for attendance
                'uri' => 'attendance-dashboard',
                'permission' => null, // No specific permission required
                'access_by' => json_encode(['admin', 'dos', 'teacher', 'hm']), // Roles that can access
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->command->info('Attendance Dashboard menu item created successfully!');
        } else {
            $this->command->info('Attendance Dashboard menu item already exists.');
        }
    }
}