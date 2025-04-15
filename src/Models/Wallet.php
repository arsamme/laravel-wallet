<?php

declare(strict_types=1);

namespace ArsamMe\Wallet\Models;

use ArsamMe\Wallet\Contracts\Services\CastServiceInterface;
use ArsamMe\Wallet\Contracts\Services\MathServiceInterface;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

use function array_key_exists;
use function config;

/**
 * Class Wallet.
 *
 * @property non-empty-string $uuid
 * @property class-string $holder_type
 * @property int|non-empty-string $holder_id
 * @property class-string $currency_type
 * @property int|non-empty-string $currency_id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property null|array $meta
 * @property int $decimal_places
 * @property Model $holder
 * @property string $currency
 * @property DateTimeInterface $created_at
 * @property DateTimeInterface $updated_at
 * @property DateTimeInterface $deleted_at
 *
 * @method int getKey()
 */
class Wallet extends Model implements \ArsamMe\Wallet\Contracts\Wallet {
    use SoftDeletes;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'holder_type',
        'holder_id',
        'name',
        'slug',
        'description',
        'meta',
        'balance',
        'frozen_amount',
        'decimal_places',
        'checksum',
        'created_at',
        'updated_at',
    ];

    /**
     * @var array<string, int|string>
     */
    protected $attributes = [
        'balance' => 0,
        'frozen_amount' => 0,
        'decimal_places' => 2,
    ];

    /**
     * @return array<string, string>
     */
    public function casts(): array {
        return [
            'decimal_places' => 'int',
            'meta' => 'json',
        ];
    }

    public function getTable(): string {
        if ('' === (string) $this->table) {
            $this->table = config('wallet.wallet.table', 'wallets');
        }

        return parent::getTable();
    }

    public function setNameAttribute(string $name): void {
        $this->attributes['name'] = $name;
        /**
         * Must be updated only if the model does not exist or the slug is empty.
         */
        if ($this->exists) {
            return;
        }
        if (array_key_exists('slug', $this->attributes)) {
            return;
        }
        $this->attributes['slug'] = Str::slug($name);
    }

    /**
     * @return MorphTo<Model, self>
     */
    public function holder(): MorphTo {
        return $this->morphTo();
    }

    public function transactions(): HasMany {
        // Retrieve the wallet instance using the `getWallet` method of the `CastServiceInterface`.
        // The `false` parameter indicates that the wallet should not be saved if it does not exist.
        $wallet = app(CastServiceInterface::class)->getWallet($this, false);

        // Retrieve all transactions related to the wallet using the `hasMany` method on the wallet instance.
        // The transaction model class is retrieved from the configuration using `config('wallet.transaction.model', Transaction::class)`.
        // The relationship is defined using the `wallet_id` foreign key.
        return $wallet->hasMany(config('wallet.transaction.model', Transaction::class), 'wallet_id');
    }

    public function getRawBalanceAttribute(): string {
        return (string) $this->getRawOriginal('balance', 0);
    }

    public function getRawFrozenAmountAttribute() {
        return $this->getRawOriginal('frozen_amount', 0);
    }

    public function getRawAvailableBalanceAttribute(): string {
        $mathService = app(MathServiceInterface::class);

        return (string) $mathService->sub($this->getRawBalanceAttribute(), $this->getRawFrozenAmountAttribute());
    }

    public function getBalanceAttribute(): string {
        $mathService = app(MathServiceInterface::class);

        return $mathService->floatValue($this->getRawBalanceAttribute(), $this->attributes['decimal_places']);
    }

    public function getFrozenAmountAttribute() {
        $mathService = app(MathServiceInterface::class);

        return $mathService->floatValue($this->getRawFrozenAmountAttribute(), $this->attributes['decimal_places']);
    }

    public function getAvailableBalanceAttribute() {
        $mathService = app(MathServiceInterface::class);

        return $mathService->sub($this->getRawAvailableBalanceAttribute(), $this->attributes['decimal_places']);
    }

    public function deposit(float|int|string $amount, ?array $meta = null): Transaction {
        // TODO: Implement deposit() method.
    }

    public function withdraw(float|int|string $amount, ?array $meta = null): Transaction {
        // TODO: Implement withdraw() method.
    }

    public function canWithdraw(float|int|string $amount, bool $allowZero = false): bool {
        // TODO: Implement canWithdraw() method.
    }

    public function walletTransactions(): HasMany {
        // TODO: Implement walletTransactions() method.
    }
}
