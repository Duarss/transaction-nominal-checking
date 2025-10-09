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
        Schema::create('main_payment_details', function (Blueprint $table) {
            $table->id();
            $table->string('doc_id');
            $table->foreign('doc_id')->references('doc_id')->on('main_payments')->onDelete('cascade');
            $table->string('item_index');
            $table->string('payment_type');
            $table->decimal('amount', 15, 2);
            $table->string('bank')->nullable();
            $table->string('bank_doc')->nullable();
            $table->datetime('bank_due')->nullable();
            $table->string('location')->nullable();
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
        Schema::dropIfExists('main_payment_details');
    }
};
