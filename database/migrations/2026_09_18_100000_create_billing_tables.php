<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Platform billing: plans -> subscriptions -> invoices -> payments, plus the
 * access lifecycle on enterprises. See SELF_SERVICE_ONBOARDING_AND_SUBSCRIPTION_PLAN.md.
 *
 * The SMS wallet (wallet_records) is untouched; a wallet top-up is simply an
 * invoice of kind sms_credit whose settlement writes the wallet record.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 80);
            $table->unsignedInteger('min_students')->default(0);
            $table->unsignedInteger('max_students')->nullable(); // null = unlimited
            $table->unsignedBigInteger('price_6m');
            $table->unsignedBigInteger('price_12m');
            $table->text('features')->nullable();               // json list
            $table->boolean('is_public')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('enterprise_id')->index();
            $table->unsignedBigInteger('plan_id');
            $table->string('period', 5);                        // 6m | 12m
            $table->unsignedTinyInteger('instalments')->default(1); // 1 | 3
            $table->unsignedBigInteger('total_amount');
            $table->string('status', 20)->default('pending');   // pending|active|completed|cancelled
            $table->timestamp('starts_at')->nullable();         // set on first payment
            $table->timestamp('ends_at')->nullable();           // starts_at + period
            $table->timestamp('cancelled_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable(); // null = self-service
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('number', 30)->unique();
            $table->unsignedBigInteger('enterprise_id')->index();
            $table->unsignedBigInteger('subscription_id')->nullable()->index();
            $table->string('kind', 20);                          // subscription | sms_credit
            $table->unsignedTinyInteger('instalment_no')->default(1);
            $table->unsignedTinyInteger('instalment_of')->default(1);
            $table->unsignedBigInteger('amount');
            $table->string('currency', 3)->default('UGX');
            $table->string('status', 15)->default('issued');     // issued | paid | void
            $table->string('description', 255)->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id')->index();
            $table->unsignedBigInteger('enterprise_id')->index();
            $table->string('gateway', 20);                       // pesapal | bank | cash | manual
            $table->string('gateway_ref', 120)->nullable();      // pesapal order_tracking_id
            $table->string('merchant_ref', 60)->nullable();      // what we sent the gateway
            $table->unsignedBigInteger('amount');
            $table->string('status', 15)->default('initiated');  // initiated|pending|succeeded|failed|reversed
            $table->string('method', 60)->nullable();            // MTN, Airtel, Visa, bank slip...
            $table->string('confirmation_code', 80)->nullable();
            $table->text('raw_payload')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable(); // null = gateway
            $table->timestamps();
            // A gateway notification replayed twice must map to the same row.
            $table->unique('gateway_ref');
        });

        Schema::table('enterprises', function (Blueprint $table) {
            $table->string('access_status', 25)->default('active')->index();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('access_ends_at')->nullable();
            $table->unsignedSmallInteger('grace_days')->default(7);
            $table->boolean('billing_exempt')->default(false);
            $table->string('subdomain_slug', 40)->nullable()->unique(); // enforced-unique copy of subdomain
        });

        // ---- Backfill: dark launch. Nobody loses access today. ----
        // Established schools (pre-wizard ids, or anyone with students) are
        // grandfathered as active + billing_exempt until you switch them on.
        DB::statement("
            UPDATE enterprises e
            SET access_status = 'active',
                billing_exempt = 1
            WHERE e.id <= 28
               OR EXISTS (SELECT 1 FROM admin_users s WHERE s.enterprise_id = e.id AND s.user_type = 'student')
        ");
        // Schools already switched off by hand stay off.
        DB::statement("UPDATE enterprises SET access_status = 'suspended', billing_exempt = 0 WHERE has_valid_lisence <> 'Yes'");
        // Empty self-registered schools get a 30-day trial from today.
        DB::statement("
            UPDATE enterprises SET access_status = 'trialing',
                trial_ends_at = DATE_ADD(NOW(), INTERVAL 30 DAY),
                access_ends_at = DATE_ADD(NOW(), INTERVAL 30 DAY)
            WHERE billing_exempt = 0 AND access_status = 'active'
        ");
        // Copy subdomains where unique; collisions are left null to be fixed in the console.
        DB::statement("
            UPDATE enterprises e
            JOIN (SELECT LOWER(TRIM(subdomain)) s FROM enterprises WHERE COALESCE(subdomain,'')<>'' GROUP BY s HAVING COUNT(*)=1) u
              ON u.s = LOWER(TRIM(e.subdomain))
            SET e.subdomain_slug = u.s
        ");
    }

    public function down()
    {
        Schema::table('enterprises', function (Blueprint $table) {
            $table->dropColumn(['access_status', 'trial_ends_at', 'access_ends_at', 'grace_days', 'billing_exempt', 'subdomain_slug']);
        });
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('plans');
    }
};
