<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountingJournalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounting_journals', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Change to increments('id') if preferred
            $table->unsignedBigInteger('ledger_id')->nullable();
            $table->foreign('ledger_id')->references('id')->on('ledgers')->onDelete('cascade'); // Update constraints based on your needs
            $table->bigInteger('balance');
            $table->char('currency', 5);
            $table->timestamps();

            // Add morphed_type and morphed_id only if you need polymorphic relationships
            //$table->char('morphed_type', 32)->nullable();
            //$table->integer('morphed_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounting_journals');
    }
}