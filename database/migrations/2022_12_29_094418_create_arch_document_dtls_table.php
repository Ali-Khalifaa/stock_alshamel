<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArchDocumentDtlsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('arch_document_dtls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gen_arch_doc_type_id');
            $table->unsignedBigInteger('arch_doc_field_id');
            $table->string('field_value',1000);
            $table->timestamps();

            $table->foreign('gen_arch_doc_type_id')->references('id')->on('gen_arch_doc_types');
            $table->foreign('arch_doc_field_id')->references('id')->on('document_fields');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('arch_document_dtls');
    }
}
