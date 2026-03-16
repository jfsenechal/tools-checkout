<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique()->comment('Unique tool identifier');
            $table->string('qr_code')->nullable()->comment('QR code image filename');
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['available', 'checked_out', 'maintenance', 'retired'])
                ->default('available');
            $table->string('location')->nullable()->comment('Storage location');
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->date('purchase_date')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('model')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tools');
    }
};
