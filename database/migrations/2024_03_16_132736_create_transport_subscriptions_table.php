<?php

use App\Models\Enterprise;
use App\Models\Term;
use App\Models\TransportRoute;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransportSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transport_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class);
            $table->foreignIdFor(User::class);
            $table->foreignIdFor(TransportRoute::class);
            $table->foreignIdFor(Term::class);
            $table->string('status')->nullable()->default('active');
            $table->string('trip_type')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->text('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transport_subscriptions');
    }
}
