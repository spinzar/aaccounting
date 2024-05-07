<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->uuid('id')->primary(); // Change to increments('id') if preferred
            $table->string('name');
            $table->string('account_number')->nullable(); // Optional
            $table->enum('type', ['asset', 'liability', 'equity', 'income', 'expense'])->nullable(); // Optional
            $table->text('description')->nullable(); // Optional
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public  function down()
    {
        Schema::dropIfExists('accounts');
    }
}