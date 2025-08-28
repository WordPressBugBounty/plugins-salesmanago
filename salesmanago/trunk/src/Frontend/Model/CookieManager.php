<?php

namespace bhr\Frontend\Model;

if(!defined('ABSPATH')) exit;

use \SALESmanago\Adapter\CookieManagerAdapter;

class CookieManager extends AbstractModel implements CookieManagerAdapter
{

    /**
     * @inheritDoc
     */
    public function setCookie($name, $value, $expiry = null, $httpOnly = false, $path = '/')
    {
        $_SESSION[$name] = $value;

        if(!empty($expiry) && $expiry >= time()) {
            setcookie($name, $value, $expiry, '/');
        } elseif(!empty($expiry)) {
            setcookie($name, $value, time() + $expiry, '/');
        } else {
            setcookie($name, $value, time() + self::COOKIE_TTL_10YRS, '/');
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteCookie($name)
    {
        if (isset($_COOKIE[$name])) {
            unset($_COOKIE[$name]);
        }

        if (isset($_SESSION[$name])) {
            unset($_SESSION[$name]);
        }

        setcookie($name, null, -1, '/');
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getCookie($name)
    {
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
    }
}
