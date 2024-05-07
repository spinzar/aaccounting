<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountingJournalTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounting_journal_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('transaction_group')->nullable();
            $table->unsignedBigInteger('journal_id');
            $table->foreign('journal_id')->references('id')->on('accounting_journals');
            $table->bigInteger('amount'); // Consider using a package like spatie/laravel-money for currency support
            $table->char('currency', 5);
            $table->text('memo')->nullable();
            $table->json('tags')->nullable(); // Consider a separate tags table for better management
            $table->morphs('ref'); // Polymorphic relationship for referencing other models
            $table->timestamps();
            $table->dateTime('post_date');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounting_journal_transactions');
    }
}
