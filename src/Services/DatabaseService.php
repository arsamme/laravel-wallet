<?php

namespace ArsamMe\Wallet\Services;

use ArsamMe\Wallet\Contracts\ExceptionInterface;
use ArsamMe\Wallet\Contracts\Services\DatabaseServiceInterface;
use ArsamMe\Wallet\Exceptions\TransactionFailedException;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\RecordsNotFoundException;
use Throwable;
use ArsamMe\Wallet\Exceptions\TransactionRollbackException;

class DatabaseService implements DatabaseServiceInterface
{
    private ConnectionInterface $connection;

    public function __construct(ConnectionResolverInterface $connectionResolver)
    {
        $this->connection = $connectionResolver->connection(config('wallet.database.connection'));
    }

    public function getConnection(): ConnectionInterface
    {
        return $this->connection;
    }

    /**
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function transaction(callable $callback): mixed
    {
        try {
            $connection = $this->connection;
            if ($connection->transactionLevel() > 0) {
                return $callback();
            }

            return $connection->transaction(function () use ($callback) {
                $result = $callback();

                if ($result === false || (is_countable($result) && count($result) === 0)) {
                    throw new TransactionRollbackException($result);
                }

                return $result;
            });
        } catch (TransactionRollbackException $rollbackException) {
            return $rollbackException->getResult();
        } catch (RecordsNotFoundException|ExceptionInterface $exception) {
            throw $exception;
        } catch (Throwable $throwable) {
            throw new TransactionFailedException(
                'Transaction failed. Message: ' . $throwable->getMessage(),
                ExceptionInterface::TRANSACTION_FAILED,
                $throwable
            );
        }
    }
}