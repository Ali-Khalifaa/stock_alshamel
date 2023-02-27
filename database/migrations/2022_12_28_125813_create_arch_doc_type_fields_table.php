<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArchDocTypeFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('arch_doc_type_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gen_arch_doc_type_id');
            $table->unsignedBigInteger('arch_doc_field_id');
            $table->unsignedBigInteger('field_order');
            $table->tinyInteger('is_required')->default(0);
            $table->unsignedBigInteger('field_characters');
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
        Schema::dropIfExists('arch_doc_type_fields');
    }
}
