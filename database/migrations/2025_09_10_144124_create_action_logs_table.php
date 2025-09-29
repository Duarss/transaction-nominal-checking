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
        Schema::create('action_logs', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_code');
            $table->foreign('transaction_code')->references('doc_id')->on('transactions')->onDelete('cascade');
            $table->decimal('nominal_before', 15, 2)->nullable(); // can be null if the transaction is not found
            $table->decimal('nominal_after', 15, 2)->nullable(); // not null if there's a mismatch with the desired nominal
            $table->string('status'); // approved (by admin) or rechecked (by branch admin) and recheck_pending
            $table->string('done_by');
            $table->foreign('done_by')->references('username')->on('users');
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
        Schema::dropIfExists('action_logs');
    }
};
