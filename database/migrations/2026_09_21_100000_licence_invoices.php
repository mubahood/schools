<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Licence invoices: itemised, term-linked, drafted by Newline and payable by
 * the school. Adds the columns an invoice needs to stand on its own as a
 * document (title, notes, line items, a shareable token) and the link to the
 * term that payment activates.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id')->index();
            $table->string('label', 190);
            $table->text('description')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->string('unit', 30)->nullable();          // "students", "term", "licence"
            $table->unsignedBigInteger('unit_amount')->default(0);
            $table->unsignedBigInteger('amount')->default(0);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->string('title', 190)->nullable()->after('kind');
            $table->text('notes')->nullable()->after('description');
            $table->text('inclusions')->nullable()->after('notes');   // JSON list of what the package covers
            $table->unsignedBigInteger('term_id')->nullable()->index()->after('subscription_id');
            $table->unsignedBigInteger('academic_year_id')->nullable()->after('term_id');
            $table->string('public_token', 64)->nullable()->unique()->after('number');
            $table->unsignedBigInteger('created_by')->nullable()->after('paid_at');
            $table->timestamp('sent_at')->nullable()->after('created_by');
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoice_items');
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['title', 'notes', 'inclusions', 'term_id', 'academic_year_id', 'public_token', 'created_by', 'sent_at']);
        });
    }
};
