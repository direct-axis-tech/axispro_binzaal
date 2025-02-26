<?php

namespace App\Contracts;

interface Cart {
    /**
     * Get the cart_id used for identifying the cart uniquely
     *
     * @return string
     */
    public function getCartId(): string;

    /**
     * Get the backup used to keep a copy of itself
     *
     * @return Cart
     */
    public function getBackup(): Cart;

    /**
     * Set the backup used to keep a copy of itself
     *
     * @return Cart
     */
    public function setBackup(Cart $cart): void;

    /**
     * get the sessionAccessor used for accessing the cart from session
     *
     * @return string
     */
    public function getSessionAccessor(): string;

    /**
     * Rollback the processing
     *
     * @param string $msg
     * @param string $exit
     * @return void
     */
    public function rollback(string $msg = null, bool $exit = true): void;
}