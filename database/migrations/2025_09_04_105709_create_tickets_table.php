<?php


declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('subject', 200);
            $table->text('body');
            $table->enum('status', ['new', 'open', 'pending', 'closed'])->default('new');
            $table->string('category', 50)->nullable();
            $table->text('note')->nullable();
            $table->text('explanation')->nullable();
            $table->decimal('confidence', 3, 2)->nullable();
            $table->timestamps();

            $table->index(['status', 'category']);
            $table->index('created_at');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
