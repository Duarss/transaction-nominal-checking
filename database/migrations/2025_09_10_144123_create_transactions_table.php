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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('doc_id')->unique();
            $table->dateTime('date');
            $table->string('doc_call_id');
            $table->string('branch_code');
            $table->foreign('branch_code')->references('code')->on('branches')->onDelete('cascade');
            $table->string('sales_code');
            $table->string('customer_code');
            $table->foreign('customer_code')->references('code')->on('stores');
            $table->decimal('total', 15, 2);
            $table->decimal('paid_amount', 15, 2)->nullable();
            $table->dateTime('created_on')->nullable();
            $table->datetime('last_updated')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_rechecked')->default(false)->nullable();
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
        Schema::dropIfExists('transactions');
    }
};
