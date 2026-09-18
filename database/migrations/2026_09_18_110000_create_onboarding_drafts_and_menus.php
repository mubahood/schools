<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 1. onboarding_drafts — a signup lives here until the owner proves the phone
 *    or email is theirs. Nothing touches admin_users/enterprises before that,
 *    so an abandoned signup is one row to sweep, never an orphan school.
 * 2. Menu entries so schools can find Billing and Newline can find the console.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('onboarding_drafts', function (Blueprint $table) {
            $table->id();
            $table->string('token', 80)->unique();          // resume link + session key
            $table->string('email', 190)->index();
            $table->string('phone', 30)->index();            // normalised +256...
            $table->text('user_data');                       // json, password stored as hash
            $table->text('enterprise_data')->nullable();     // json, filled at step 3
            $table->string('otp_hash', 100)->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->unsignedTinyInteger('otp_attempts')->default(0);
            $table->unsignedSmallInteger('otp_sends')->default(0);
            $table->timestamp('otp_sent_at')->nullable();
            $table->string('verified_via', 10)->nullable();  // sms | email
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('consumed_at')->nullable();    // school created
            $table->unsignedBigInteger('enterprise_id')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();
        });

        $now = now();
        if (!DB::table('admin_menu')->where('uri', 'billing')->exists()) {
            DB::table('admin_menu')->insert([
                'parent_id' => 0, 'order' => 5, 'title' => 'Subscription & Billing',
                'icon' => 'fa-credit-card', 'uri' => 'billing', 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
        if (!DB::table('admin_menu')->where('uri', 'subscriptions-admin')->exists()) {
            $id = DB::table('admin_menu')->insertGetId([
                'parent_id' => 0, 'order' => 4, 'title' => 'Subscriptions (Newline)',
                'icon' => 'fa-money', 'uri' => 'subscriptions-admin', 'created_at' => $now, 'updated_at' => $now,
            ]);
            DB::table('admin_role_menu')->insert(['role_id' => 1, 'menu_id' => $id, 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('onboarding_drafts');
        foreach (['billing', 'subscriptions-admin'] as $uri) {
            $id = DB::table('admin_menu')->where('uri', $uri)->value('id');
            if ($id) {
                DB::table('admin_role_menu')->where('menu_id', $id)->delete();
                DB::table('admin_menu')->where('id', $id)->delete();
            }
        }
    }
};
