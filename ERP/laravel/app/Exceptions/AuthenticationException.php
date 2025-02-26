<?php

namespace App\Exceptions;

use \Illuminate\Auth\AuthenticationException as Exception;

class AuthenticationException extends Exception {

    /**
     * Render an exception into a response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function render($request) {
        $request->session()->put('timeout', [
            'uri' => app('url')->full(),
            'post' => []
        ]);

        return $request->expectsJson()
            ? response()->json([
                'message' => $this->getMessage(),
                'redirect_to' => $this->redirectTo()
            ], 401)
            : redirect()->guest($this->redirectTo() ?? route('login'));
    }
}