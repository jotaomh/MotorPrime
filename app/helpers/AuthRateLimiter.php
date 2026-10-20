<?php

namespace App\Helpers;

class AuthRateLimiter {
    public static function check($key, $max = 5, $window = 300) {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $now = time();
        if (!isset($_SESSION['login_attempts'][$key])) {
            $_SESSION['login_attempts'][$key] = [];
        }
        $_SESSION['login_attempts'][$key] = array_filter($_SESSION['login_attempts'][$key], function($t) use ($now, $window) {
            return ($now - $t) < $window;
        });
        if (count($_SESSION['login_attempts'][$key]) >= $max) {
            return false;
        }
        return true;
    }

    public static function hit($key) {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (!isset($_SESSION['login_attempts'][$key])) {
            $_SESSION['login_attempts'][$key] = [];
        }
        $_SESSION['login_attempts'][$key][] = time();
    }

    public static function clear($key) {
        unset($_SESSION['login_attempts'][$key]);
    }
}
