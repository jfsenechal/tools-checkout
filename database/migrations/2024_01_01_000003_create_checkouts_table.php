<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checkouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tool_id')->constrained()->cascadeOnDelete();
            $table->foreignId('worker_id')->constrained()->cascadeOnDelete();
            $table->timestamp('checked_out_at');
            $table->timestamp('expected_return_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->foreignId('checked_out_by')->nullable()->comment('Admin user who processed checkout');
            $table->foreignId('returned_by')->nullable()->comment('Admin user who processed return');
            $table->enum('condition_out', ['excellent', 'good', 'fair', 'poor'])->default('good');
            $table->enum('condition_in', ['excellent', 'good', 'fair', 'poor'])->nullable();
            $table->text('checkout_notes')->nullable();
            $table->text('return_notes')->nullable();
            $table->boolean('is_overdue')->default(false);
            $table->timestamps();

            $table->index(['tool_id', 'returned_at']);
            $table->index(['worker_id', 'returned_at']);
            $table->index('checked_out_at');
            $table->index('is_overdue');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkouts');
    }
};
