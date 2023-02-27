<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArchDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('arch_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gen_arch_doc_type');
            $table->unsignedBigInteger('doc_status');
            $table->text('doc_description');
            $table->string("url_reference", 200);
            $table->timestamps();

            $table->foreign('gen_arch_doc_type')->references('id')->on('gen_arch_doc_types');
            $table->foreign('doc_status')->references('id')->on('arch_doc_statuses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('arch_douments');
    }
}
