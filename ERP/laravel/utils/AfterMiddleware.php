<?php

class AfterMiddleware {
    /**
     * The middleware that should be run after a request is processed.
     *
     * @var \Closure[]
     */
    protected $middlewares = [];

    /**
     * Register a renderable callback.
     *
     * @param  callable  $renderUsing
     * @return void
     */
    public function register(callable $middleware)
    {
        if (! $middleware instanceof Closure) {
            $middleware = Closure::fromCallable($middleware);
        }

        $this->middlewares[] = $middleware;
    }

    /**
     * Run all the registered middlewares
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->middlewares as $middleware) {
            $middleware();
        }
    }
}