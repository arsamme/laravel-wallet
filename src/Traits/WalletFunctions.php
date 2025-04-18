<?php

declare(strict_types=1);

namespace ArsamMe\Wallet\Traits;

use ArsamMe\Wallet\Contracts\Services\AtomicServiceInterface;
use ArsamMe\Wallet\Contracts\Services\CastServiceInterface;
use ArsamMe\Wallet\Contracts\Services\ConsistencyServiceInterface;
use ArsamMe\Wallet\Contracts\Services\MathServiceInterface;
use ArsamMe\Wallet\Contracts\Services\RegulatorServiceInterface;
use ArsamMe\Wallet\Contracts\Services\WalletServiceInterface;
use ArsamMe\Wallet\Contracts\Wallet;
use ArsamMe\Wallet\Exceptions\AmountInvalid;
use ArsamMe\Wallet\Exceptions\BalanceIsEmpty;
use ArsamMe\Wallet\Exceptions\InsufficientFunds;
use ArsamMe\Wallet\Exceptions\ModelNotFoundException;
use ArsamMe\Wallet\Exceptions\TransactionFailedException;
use ArsamMe\Wallet\Models\Transaction;
use ArsamMe\Wallet\Models\Wallet as WalletModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\RecordsNotFoundException;

use function app;
use function config;

trait WalletFunctions {
    /**
     * Magic Laravel framework method that makes it possible to call property balance.
     *
     * This method is called by Laravel's magic getter when the `balance` property is accessed.
     * It returns the current balance of the wallet as a string.
     *
     * @return non-empty-string The current balance of the wallet as a string.
     *
     * @throws ModelNotFoundException If the wallet does not exist and `$save` is set to `false`.
     *
     * @see Wallet
     * @see WalletModel
     */
    public function getRawBalanceAttribute(): string {
        // Get the wallet object from the model.
        // This method uses the CastServiceInterface to retrieve the wallet object from the model.
        // The second argument, `$save = false`, prevents the service from saving the wallet if it does not exist.
        // This is useful to avoid unnecessary database queries when retrieving the balance.
        $wallet = app(CastServiceInterface::class)->getWallet($this);

        // Get the current balance of the wallet using the Regulator service.
        // This method uses the RegulatorServiceInterface to retrieve the current balance of the wallet.
        // The Regulator service is responsible for calculating the balance of the wallet based on the transactions.
        // The balance is always returned as a string to preserve the accuracy of the decimal value.
        // Return the balance as a string.
        return app(RegulatorServiceInterface::class)->getBalance($wallet);
    }

    public function getRawFrozenAmountAttribute(): string {
        $wallet = app(CastServiceInterface::class)->getWallet($this);

        return app(RegulatorServiceInterface::class)->getFrozenAmount($wallet);
    }

    public function getRawAvailableBalanceAttribute(): string {
        $wallet = app(CastServiceInterface::class)->getWallet($this);

        return app(RegulatorServiceInterface::class)->getAvailableBalance($wallet);
    }

    public function getBalanceAttribute(): string {
        $wallet = app(CastServiceInterface::class)->getWallet($this);

        return app(MathServiceInterface::class)->floatValue($this->getRawBalanceAttribute(), $wallet->decimal_places);
    }

    public function getFrozenAmountAttribute(): string {
        $wallet = app(CastServiceInterface::class)->getWallet($this);

        return app(MathServiceInterface::class)->floatValue($this->getRawFrozenAmountAttribute(), $wallet->decimal_places);
    }

    public function getAvailableBalanceAttribute(): string {
        $wallet = app(CastServiceInterface::class)->getWallet($this);

        return app(MathServiceInterface::class)->floatValue($this->getRawAvailableBalanceAttribute(), $wallet->decimal_places);
    }

    /**
     * Returns all transactions related to the wallet.
     *
     * This method retrieves all transactions associated with the wallet.
     * It uses the `getWallet` method of the `CastServiceInterface` to retrieve the wallet instance.
     * The `false` parameter indicates that the wallet should not be saved if it does not exist.
     * The method then uses the `hasMany` method on the wallet instance to retrieve all transactions related to the wallet.
     * The transaction model class is retrieved from the configuration using `config('wallet.transaction.model', Transaction::class)`.
     * The relationship is defined using the `wallet_id` foreign key.
     *
     * @return HasMany<Transaction> Returns a `HasMany` relationship of transactions related to the wallet.
     */
    public function walletTransactions(): HasMany {
        // Retrieve the wallet instance using the `getWallet` method of the `CastServiceInterface`.
        // The `false` parameter indicates that the wallet should not be saved if it does not exist.
        $wallet = app(CastServiceInterface::class)->getWallet($this);

        // Retrieve all transactions related to the wallet using the `hasMany` method on the wallet instance.
        // The transaction model class is retrieved from the configuration using `config('wallet.transaction.model', Transaction::class)`.
        // The relationship is defined using the `wallet_id` foreign key.
        return $wallet->hasMany(config('wallet.transaction.model', Transaction::class), 'wallet_id');
    }

    /**
     * Deposit funds into the wallet.
     *
     * This method executes the deposit transaction within an atomic block to ensure data consistency.
     *
     * @param  float|int|string  $amount  The amount to deposit.
     * @param  array|null  $meta  Additional metadata for the transaction. This can be used to store
     *                            information about the type of deposit, the source of the funds, or any other relevant details.
     * @return Transaction The transaction object representing the deposit.
     */
    public function deposit(float|int|string $amount, ?array $meta = null): Transaction {
        $wallet = app(CastServiceInterface::class)->getWallet($this);

        // Execute the deposit transaction within an atomic block to ensure data consistency.
        return app(WalletServiceInterface::class)->deposit($wallet, $amount, $meta);
    }

    /**
     * Withdraw funds from the system.
     *
     * This method wraps the withdrawal in an atomic block to ensure atomicity and consistency of the withdrawal.
     * It checks if the withdrawal is possible before attempting it.
     *
     * @param  float|int|string  $amount  The amount to withdraw.
     * @param  array|null  $meta  Additional information for the transaction.
     * @return Transaction The created transaction.
     *
     * @see AtomicServiceInterface
     * @see ConsistencyServiceInterface
     * @see TransactionFailedException
     * @see AmountInvalid
     * @see BalanceIsEmpty
     * @see InsufficientFunds
     * @see RecordsNotFoundException
     */
    public function withdraw(float|int|string $amount, ?array $meta = null): Transaction {
        $wallet = app(CastServiceInterface::class)->getWallet($this);

        // Execute the deposit transaction within an atomic block to ensure data consistency.
        return app(WalletServiceInterface::class)->withdraw($wallet, $amount, $meta);
    }

    public function freeze(float|int|string|null $amount = null): bool {
        $wallet = app(CastServiceInterface::class)->getWallet($this);

        // Execute the deposit transaction within an atomic block to ensure data consistency.
        return app(WalletServiceInterface::class)->freeze($wallet, $amount);
    }

    public function unFreeze(float|int|string|null $amount = null): bool {
        $wallet = app(CastServiceInterface::class)->getWallet($this);

        // Execute the deposit transaction within an atomic block to ensure data consistency.
        return app(WalletServiceInterface::class)->unFreeze($wallet, $amount);
    }

    /**
     * Checks if the user can withdraw funds based on the provided amount.
     *
     * This method retrieves the math service instance and calculates the total balance of the wallet.
     * It then checks if the withdrawal is possible using the consistency service.
     *
     * @param  float|int|string  $amount  The amount to be withdrawn.
     * @param  bool  $allowZero  Flag to allow zero balance for withdrawal. Defaults to false.
     * @return bool Returns true if the withdrawal is possible; otherwise, false.
     */
    public function canWithdraw(float|int|string $amount, bool $allowZero = false): bool {
        // Get the math service instance.
        $mathService = app(MathServiceInterface::class);

        // Get the wallet and calculate the total balance.
        $wallet = app(CastServiceInterface::class)->getWallet($this);
        $amount = $mathService->intValue($amount, $wallet->decimal_places);

        $balance = $this->getRawBalanceAttribute();

        // Check if the withdrawal is possible.
        return app(ConsistencyServiceInterface::class)->canWithdraw($balance, $amount, $allowZero);
    }
}
