<?php

use App\Models\AcademicYear;
use App\Models\Enterprise;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGenerateTheologyClassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('generate_theology_classes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class);
            $table->foreignIdFor(AcademicYear::class);
            $table->string('BC')->nullable();
            $table->string('MC')->nullable();
            $table->string('TC')->nullable();
            $table->string('P1')->nullable();
            $table->string('P2')->nullable();
            $table->string('P3')->nullable();
            $table->string('P4')->nullable();
            $table->string('P5')->nullable();
            $table->string('P6')->nullable();
            $table->string('P7')->nullable();
        });
    }
    /* 
1	Shubah Upper - 2022	KASIM KAUKAB SSEKANDI	4	51	
2	Primary Six - 2022	ABDUL GHANIYU LUKWAGO	4	0	
3	Primary Five - 2022	FAROOK LUTAAYA	4	14	
4	Primary Four - 2022	FAUZI ABDALLAH SEBISUBI	4	55	
5	Primary Three - 2022	KASULE MAKKIYU	4	83	
6	Primary Two - 2022	IDDI SULAIMAN	4	31	
7	Primary One - 2022	NALUKENGE HIDAAYA	4	43	
8	Top Class - 2022	LATIIFA NAMAGANDA	4	67	

9	Middle Class - 2022	NASSUR MANSUR	4	81	
10	Baby Class - 2022	SWALEH ABUBAKAR MATOVU	4	81	
11	Primary One (Shubah) - 2022	NUHA NAGAWA	4	49	
12	Primary Two (Shubah) - 2022	ISIKO EDRIS SULAIMAN
*/
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('generate_theology_classes');
    }
}
