<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHotfieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hotfields', function (Blueprint $table) {
            $table->id();
            $table->string("table_name", 50)->comment("a database table");
            $table->string("field_name", 50)->comment("a table field name");
            $table->string("field_title", 100)->comment("وصف الحقل الذي يظهر للمستخدم");
            $table->string("field_title_en", 100)->comment("وصف الحقل الذي يظهر للمستخدم");
            $table->boolean("visibility")->default(0)->comment(" 1 = Visible encrypted, 0 = not visible");
            $table->softDeletes();
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
        Schema::dropIfExists('hot_fields');
    }
}
