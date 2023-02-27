<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArchiveClosedReferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archive_closed_references', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('arch_docfields_id');
            $table->string('field_value', 1000);
            $table->timestamps();
            $table->foreign('arch_docfields_id')->references('id')->on('document_fields');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('archive_closed_references');
    }
}
