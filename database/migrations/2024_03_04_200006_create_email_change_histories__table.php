<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The database connection that should be used by the migration.
     *
     * @var string
     */
    protected $connection = 'backend_mysql';
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('email_change_history', function (Blueprint $table) {
            $table->id();
            $table->text('previous_email')->nullable();
            $table->text('new_email')->nullable();
            $table->timestamp('created')->nullable();
            $table->timestamp('updated')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_change_history');
    }
};
