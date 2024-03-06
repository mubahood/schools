<?php

use App\Models\Enterprise;
use App\Models\Term;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFixedAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class);
            $table->foreignIdFor(User::class, 'assigned_to_id')->nullable();
            $table->foreignIdFor(Term::class, 'due_term_id');
            $table->text('category')->nullable();
            $table->text('name');
            $table->text('description')->nullable();
            $table->text('photo')->nullable();
            $table->string('status');
            $table->date('purchase_date');
            $table->date('warranty_expiry_date');
            $table->date('maintenance_due_date');
            $table->decimal('purchase_price', 10, 2);
            $table->decimal('current_value', 10, 2);
            $table->text('remarks')->nullable();
            $table->string('serial_number');
            $table->string('code');
            $table->text('qr_code')->nullable();
            $table->text('barcode')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fixed_assets');
    }
}
