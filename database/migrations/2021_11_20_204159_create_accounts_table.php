<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('currency_id');
            $table->string('tatum_account_id', 50);
            $table->string('tatum_customer_id', 50);
            $table->string('derivation_key', 50)->nullable();
            $table->text('mnemonic')->nullable();
            $table->string('xpub')->nullable();
            $table->string('private_key', 100)->nullable();
            $table->integer('memo')->nullable();
            $table->string('deposit_address', 100)->nullable();
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
        Schema::dropIfExists('accounts');
    }
}
