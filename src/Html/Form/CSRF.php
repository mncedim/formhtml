<?php
/**
 * Created by PhpStorm.
 * User: mncedim
 * Date: 15/09/24
 * Time: 12:16 PM
 */

namespace Mncedim\Html\Form;

/**
 * Class CSRF
 * @package Mncedim\Html\Form
 */
class CSRF
{
    /**
     * @var int
     */
    private $_time = 3;

    public function __construct()
    {
        $this->deleteExpiredTokens();
        if (!isset($_SESSION['form_security']['_csrf'])) {
            $_SESSION['form_security']['_csrf'] = [];
        }
    }

    /**
     * @param $time
     * @return bool
     */
    public function setTime($time)
    {
        if (is_int($time) && is_numeric($time)) {
            $this->_time = $time;

            return true;
        }
        return false;
    }

    /**
     * @param $token
     * @return bool
     */
    public function delete($token)
    {
        $this->deleteExpiredTokens();

        if ($this->get($token)) {
            unset($_SESSION['form_security']['_csrf'][$token]);
            return true;
        }

        return false;
    }

    public function deleteExpiredTokens()
    {
        if (!isset($_SESSION['form_security']['_csrf'])) {
            return;
        }
        foreach ($_SESSION['form_security']['_csrf'] AS $token => $time) {
            if (time() >= $time) {
                unset($_SESSION['form_security']['_csrf'][$token]);
            }
        }
    }

    /**
     * @param bool $time
     * @param int $multiplier
     * @return string
     */
    public function set($time = true, $multiplier = 3600)
    {
        if (function_exists('openssl_random_pseudo_bytes')) {
            $key = substr(bin2hex(openssl_random_pseudo_bytes(64)), 0, 64);
        } else {
            $key = sha1(mt_rand() . rand());
        }

        $value = (time() + (($time ? $this->_time : $time) * $multiplier));
        $_SESSION['form_security']['_csrf'][$key] = $value;

        return $key;
    }

    /**
     * @param $token
     * @return bool
     */
    public function get($token)
    {
        $this->deleteExpiredTokens();
        return isset($_SESSION['form_security']['_csrf'][$token]);
    }

    /**
     * @return mixed
     */
    public function last()
    {
        return end($_SESSION['form_security']['_csrf']);
    }

    public function debug()
    {
        if (!isset($_SESSION['form_security']['_csrf'])) {
            echo 'empty';
            return;
        }
        
        echo json_encode($_SESSION['form_security']['_csrf'], JSON_PRETTY_PRINT);
    }
} 