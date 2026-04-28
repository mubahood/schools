<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParentCommitmentRecordsTable extends Migration
{
    public function up()
    {
        Schema::create('parent_commitment_records', function (Blueprint $table) {
            $table->id();

            // Multi-tenancy
            $table->unsignedBigInteger('enterprise_id')->nullable()->index();

            // Student link
            $table->unsignedBigInteger('student_id')->nullable()->index();

            // Parent link (auto-resolved from student, stored for history)
            $table->unsignedBigInteger('parent_id')->nullable()->index();

            // Parent info snapshot (auto-filled but editable for corrections)
            $table->string('parent_name')->nullable();
            $table->string('parent_contact')->nullable();

            // Financial reference (snapshot from ledger, editable for adjustments)
            $table->decimal('outstanding_balance', 14, 2)->default(0)->comment('Outstanding balance snapshot from student ledger at time of commitment');

            // Commitment details
            $table->date('commitment_date')->nullable()->index()->comment('Date by which parent commits to clear the outstanding balance');
            $table->string('promise_status', 20)->default('Pending')->index()->comment('Pending | Fulfilled | Overdue');

            // Audit
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('fulfilled_at')->nullable()->comment('Timestamp when bursar marks record as Fulfilled');

            // Notes
            $table->text('comments')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('parent_commitment_records');
    }
}
