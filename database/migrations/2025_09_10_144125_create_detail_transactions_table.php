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
        Schema::create('detail_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('doc_id');
            $table->foreign('doc_id')->references('doc_id')->on('transactions')->onDelete('cascade');
            $table->integer('item_index');
            $table->string('payment_type'); // tunai, transfer, potongan CN, cek/giro/sup
            $table->decimal('amount', 15, 2);
            $table->string('bank')->nullable();
            $table->string('bank_doc')->nullable();
            $table->dateTime('bank_due')->nullable();
            $table->text('location')->nullable();
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
        Schema::dropIfExists('detail_transactions');
    }
};
