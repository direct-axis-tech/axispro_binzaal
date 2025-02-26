<?php

use Illuminate\Session\SessionManager as LaravelSessionManager;

class SessionManager {
    function sessionStart()
    {
        $sessionManager = app(LaravelSessionManager::class);
        $sessionDriver = $sessionManager->driver();
        $config = $sessionManager->getSessionConfig();

        // the life time configuration is in minutes so convert to seconds
        $lifetime = $config['lifetime'] * 60;
        
        // Set the configuration for garbage collection
        ini_set('session.gc_maxlifetime', $lifetime);
        ini_set('session.gc_probability', $config['lottery'][0]);
        ini_set('session.gc_divisor', $config['lottery'][1]);
        
        // Laravel use session ID of length 40 so we will use the same to
        // make it work on both FA and laravel
        ini_set('session.sid_length', '40');

        // Set the serialize handler to php_serialize so both laravel and FA can both use,
        // the same session
        ini_set('session.serialize_handler', 'php_serialize');

        // Set the handler for saving and retrieving session data
        session_set_save_handler($sessionDriver->getHandler(), true);

        // Set the path were session will be stored
        session_save_path($config['files']);
        
        // Set the cookie name
        session_name($config['cookie']);

        // Set session cookie options
        session_set_cookie_params(
            $config['expire_on_close'] ? 0 : $lifetime,
            $config['path'],
            $config['domain'],
            $config['secure'],
            $config['http_only']
        );

        session_start();

        // Make sure the session hasn't expired, and destroy it if it has
        if ($this->isValidSession()) {
            $current_userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
            // store the client IP and user agent in the session to check against simple hijacking attempt
            if (!isset($_SESSION['IPaddress'])) {
                $_SESSION['IPaddress'] = getCurrentClientIP();
            }
            if (!isset($_SESSION['userAgent'])) {
                $_SESSION['userAgent'] = $current_userAgent;
            }

            // regenerate the session ID if it is probably a hijacking attempt or give a 0.1% chance anyway 
            if ($this->isProbableHijackingAttempt()) {
                app('activityLogger')
                    ->notice(
                        "Different environment detected. Possible hijacking attempt",
                        [
                            "old_ip" => $_SESSION['IPaddress'],
                            "old_ua" => $_SESSION['userAgent'],
                            "curr_ua" => $current_userAgent
                        ]
                    );
                $this->regenerateSession();
            }
        } else {
            $_SESSION = array();
            session_destroy();
            session_start();
        }
    }

    function isProbableHijackingAttempt()
    {
        $ipAddress = getCurrentClientIP();
        $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;

        if ($_SESSION['IPaddress'] != $ipAddress) {
            return  true;
        }

        if ($_SESSION['userAgent'] != $userAgent) {
            return  true;
        }
        return false;
    }

    function regenerateSession()
    {
        // If there is already a new id we don't need to generate another
        if (isset($_SESSION['NEW_SESSION_ID'])) {
            return;
        }

        // Set current session destroyed timestamp
        $_SESSION['DESTROYED'] = time();
        
        // Create new session without destroying the old one
        session_regenerate_id();
        
        // Grab a new session ID
        $new_session_id = session_id();
        $_SESSION['NEW_SESSION_ID'] = $new_session_id;


        // Write and close current session;
        session_commit();
        
        // Set session ID to the new one, and start it back up again
        session_id($new_session_id);
        session_start();

        // Now we unset the new session id and expiration values for the session. Coz we don't need them here
        unset($_SESSION['NEW_SESSION_ID']);
        unset($_SESSION['DESTROYED']);
    }

    function isValidSession()
    {
        if (isset($_SESSION['DESTROYED'])) {
            // check if session was destroyed before 300 sec
            if ($_SESSION['DESTROYED'] < time() - 300) {
                // Should not happen usually. This could be attack or due to unstable network.
                return false;
            }
            if (isset($_SESSION['NEW_SESSION_ID'])) {
                // Not fully expired yet. Could be lost cookie by unstable network.
                // Try again to set proper session ID cookie.
                // NOTE: Do not try to set session ID again if you would like to remove
                // authentication flag.
                session_commit();
                session_id($_SESSION['NEW_SESSION_ID']);
                // New session ID should exist
                session_start();

                // Now we unset the new session id and expiration values for the session. Coz we don't need them here
                unset($_SESSION['NEW_SESSION_ID']);
                unset($_SESSION['DESTROYED']);
            } else {
                $this->regenerateSession();
            }
        }
        return true;
    }
}