<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            $table->string("name", 100)->comment("Name Arabic");
            $table->string("name_e", 100)->comment("Name English");
            $table->string("url", 200)->comment("مسار نظام الشركة");
            $table->string("address", 200);
            $table->string("phone", 20);

            $table->string("cr")->comment("سجل تجاري");
            $table->string("tax_id")->comment("رقم ضريبي");
            $table->string("vat_no")->comment("رقم تسجيل القيمة المضافة");
            $table->string("email");
            $table->string("website");
            $table->string('is_active')->default('active');
            $table->unsignedInteger('partner_id');
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
        Schema::dropIfExists('companies');
    }
}
