<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarshmallowNovaImportJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marshmallow_nova_import_jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('file');
            $table->json('sample');
            $table->json('resources');
            $table->json('fields');
            $table->integer('total_rows');
            $table->json('headings');
            $table->integer('progress')->default(0);
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
        Schema::dropIfExists('marshmallow_nova_import_jobs');
    }
}
