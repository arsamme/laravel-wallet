<?php

namespace ArsamMe\Wallet\Contracts\Models;

use ArsamMe\Wallet\Contracts\Exceptions\ExceptionInterface;
use ArsamMe\Wallet\Data\TransactionExtra;
use ArsamMe\Wallet\Exceptions\BalanceIsEmptyException;
use ArsamMe\Wallet\Exceptions\InsufficientFundsException;
use ArsamMe\Wallet\Exceptions\InvalidAmountException;
use ArsamMe\Wallet\Exceptions\TransactionFailedException;
use ArsamMe\Wallet\Models\Transaction;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\RecordsNotFoundException;

interface Wallet {
    /**
     * Deposit the specified amount of money into the wallet.
     *
     * @param float|int|string $amount The amount to deposit.
     * @param TransactionExtra|null $extra
     * @return Transaction The created transaction.
     *
     * @throws InvalidAmountException If the amount is invalid.
     * @throws RecordsNotFoundException If the wallet is not found.
     * @throws TransactionFailedException If the transaction fails.
     * @throws ExceptionInterface If an exception occurs.
     */
    public function deposit(float|int|string $amount, ?TransactionExtra $extra = null): Transaction;

    /**
     * Withdraw the specified amount of money from the wallet.
     *
     * @param float|int|string $amount The amount to withdraw.
     * @param TransactionExtra|null $extra
     * @return Transaction The created transaction.
     *
     * @throws InvalidAmountException If the amount is invalid.
     * @throws BalanceIsEmptyException If the balance is empty.
     * @throws InsufficientFundsException If the amount exceeds the balance.
     * @throws RecordsNotFoundException If the wallet is not found.
     * @throws TransactionFailedException If the transaction fails.
     * @throws ExceptionInterface If an exception occurs.
     */
    public function withdraw(float|int|string $amount, ?TransactionExtra $extra = null): Transaction;

    /**
     * Checks if the wallet can safely withdraw the specified amount.
     *
     * @param  float|int|string  $amount  The amount to withdraw.
     * @return bool Returns true if the wallet can withdraw the specified amount, false otherwise.
     */
    public function canWithdraw(float|int|string $amount): bool;

    /**
     * Returns the balance of the wallet as a string.
     *
     * The balance is the total amount of funds held by the wallet.
     *
     * @return non-empty-string The balance of the wallet.
     */
    public function getRawBalance(): string;

    /**
     * Returns the balance of the wallet as a string.
     *
     * The balance is the total amount of funds held by the wallet.
     *
     * @return non-empty-string The balance of the wallet.
     */
    public function getBalanceAttribute(): string;

    public function getBalanceFloatAttribute(): float;

    public function getBalanceIntAttribute(): int;

    public function getRawFrozenAmount(): string;

    public function getFrozenAmountAttribute(): string;

    public function getFrozenAmountFloatAttribute(): float;

    public function getFrozenAmountIntAttribute(): int;

    public function getRawAvailableBalance(): string;

    public function getAvailableBalanceAttribute(): string;

    public function getAvailableBalanceFloatAttribute(): float;

    public function getAvailableBalanceIntAttribute(): int;

    /**
     * Represents a relationship where a wallet has many transactions.
     *
     * @return HasMany<Transaction> A collection of transactions associated with this wallet.
     */
    public function walletTransactions(): HasMany;
}
