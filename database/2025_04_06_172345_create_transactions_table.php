<?php

declare(strict_types=1);

use ArsamMe\Wallet\Models\Transaction;
use ArsamMe\Wallet\Models\Wallet;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create($this->table(), static function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('wallet_id')->constrained($this->walletsTable())->cascadeOnUpdate()->restrictOnDelete();
            $table->enum('type', ['deposit', 'withdraw'])->index();
            $table->decimal('amount', 64, 0);
            $table->jsonb('meta')->nullable();
            $table->string('checksum');
            $table->softDeletesTz();
            $table->timestampsTz();
        });
    }

    public function down(): void {
        Schema::dropIfExists($this->table());
    }

    private function table(): string {
        return (new Transaction)->getTable();
    }

    private function walletsTable(): string {
        return (new Wallet)->getTable();
    }
};
