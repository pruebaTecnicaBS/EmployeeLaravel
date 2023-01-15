<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('activo')->default(1);
            $table->string('name_e', 255)->default('');
            $table->string('last_name', 255)->default('');
            $table->date('birthdate');
            $table->unsignedInteger('gender_id');
            $table->foreign('gender_id')
                ->references('id')
                ->on('genders');
            $table->unsignedInteger('job_id');
            $table->foreign('job_id')
                ->references('id')
                ->on('jobs');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employees');
    }
}
