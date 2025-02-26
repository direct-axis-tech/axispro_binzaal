<?php

namespace App\Traits;

use App\Contracts\Cart as CartContract;

trait Cart {
    /** 
     * @var string $cart_id used for identifying the cart uniquely
     */
    public $cart_id;

    /** 
     * @var CartContract $backup used to keep a copy of itself
     */
    public $backup;

    /** 
     * @var string $sessionAccessor used for identifying the key used in session
     */
    public $sessionAccessor;

    /**
     * Get the cart_id used for identifying the cart uniquely
     *
     * @return string
     */
    public function getCartId(): string
    {
        return $this->cart_id;
    }

    /**
     * Get the backup used to keep a copy of itself
     *
     * @return CartContract
     */
    public function getBackup(): CartContract
    {
        return $this->backup;
    }

    /**
     * Set the backup used to keep a copy of itself
     *
     * @return CartContract
     */
    public function setBackup(CartContract $cart): void
    {
        $this->backup = $cart;
    }

    /**
     * get the sessionAccessor used for accessing the cart from session
     *
     * @return string
     */
    public function getSessionAccessor(): string
    {
        return $this->sessionAccessor;
    }

    /**
     * Rollback the processing
     *
     * @param string $msg
     * @param string $exit
     * @return void
     */
    public function rollback(string $msg = null, bool $exit = true): void
    {
        if ($msg) {
            display_error($msg);
        }

        cancel_transaction();
        
        $_SESSION[$this->getSessionAccessor()] = $this->getBackup();

        if ($exit) {
            exit();
        }
    }
}