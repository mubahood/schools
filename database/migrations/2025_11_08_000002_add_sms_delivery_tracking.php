<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('direct_messages', function (Blueprint $table) {
            $table->string('delivery_status')->nullable()->after('status');
            $table->timestamp('delivered_at')->nullable()->after('delivery_status');
            $table->timestamp('sent_at')->nullable()->after('delivered_at');
            $table->integer('retry_count')->default(0)->after('sent_at');
            $table->text('delivery_report')->nullable()->after('response');
        });

        // Add index for delivery tracking
        Schema::table('direct_messages', function (Blueprint $table) {
            $table->index('delivery_status', 'idx_delivery_status');
            $table->index(['status', 'delivery_status'], 'idx_status_delivery');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('direct_messages', function (Blueprint $table) {
            $table->dropIndex('idx_delivery_status');
            $table->dropIndex('idx_status_delivery');
            
            $table->dropColumn([
                'delivery_status',
                'delivered_at',
                'sent_at',
                'retry_count',
                'delivery_report'
            ]);
        });
    }
};
