<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('main_payments', function (Blueprint $table) {
            $table->id();
            $table->string('doc_id')->unique();
            $table->datetime('tanggal');
            $table->string('doc_call_id');
            $table->string('branch_id');
            $table->foreign('branch_id')->references('code')->on('branches')->onDelete('cascade');
            $table->string('sales_id');
            $table->string('customer_id');
            $table->foreign('customer_id')->references('code')->on('stores');
            $table->decimal('total', 15, 2);
            $table->dateTime('created_on')->nullable();
            $table->datetime('last_updated')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
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
        Schema::dropIfExists('main_payments');
    }
};
