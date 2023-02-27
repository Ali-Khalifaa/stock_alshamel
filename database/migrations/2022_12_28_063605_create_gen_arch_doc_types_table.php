<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGenArchDocTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gen_arch_doc_types', function (Blueprint $table) {
            $table->id();
            $table->string('name',100);
            $table->string('name_e',100);
            $table->bigInteger('parent_id');
            $table->tinyInteger('is_valid')->default(0);
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
        Schema::dropIfExists('gen_arch_doc_types');
    }
}
