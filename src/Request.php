<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App;

class Request
{
    public static function Integer($name)
    {
        $value = self::get($name);
        if ('' != $value && !is_numeric($value)) {
            throw new Exception("Required '$name' is not an Integer.");
        }

        return $value;
    }

    public static function IntegerRequired($name)
    {
        $value = self::getRequired($name);
        if (!is_numeric($value)) {
            throw new Exception("Required '$name' is not an Integer.");
        }

        return $value;
    }

    public static function IntegerwDefault($name, $default = false)
    {
        $value = self::getwDefault($name, $default);
        if (!is_numeric($value)) {
            throw new Exception("Required '$name' is not an Integer.");
        }

        return $value;
    }

    public static function Text($name)
    {
        $value = self::get($name);
        $value = sanitize($value);

        return $value;
    }

    public static function TextwDefault($name, $default = false)
    {
        $value = self::getwDefault($name, $default);
        $value = sanitize($value);

        return $value;
    }

    public static function TextRequired($name)
    {
        $value = self::getRequired($name);
        $value = sanitize($value);

        return $value;
    }

    public static function InArray($name, $array)
    {
        $value = self::get($name);
        if (!$value) {
            return false;
        }

        if (!\in_array($value, $array)) {
            throw new Exception("Array '$name' is not in Array.");
        }

        return $value;
    }

    public static function InArrayRequired($name, $array)
    {
        $value = self::get($name);
        if (!\in_array($value, $array)) {
            throw new Exception("Required '$name' is not in Array.");
        }

        return $value;
    }

    public static function Simpletext($name)
    {
        $value = self::get($name);
        if ('' != $value && !preg_match('/^[0-9a-zA-Z_-]+$/i', $value)) {
            throw new Exception("Variable '$name' is not a SimpleText.");
        }

        return $value;
    }

    public static function SimpletextwDefault($name, $default = false)
    {
        $value = self::getwDefault($name, $default);
        if ('' != $value && !preg_match('/^[0-9a-zA-Z_-]+$/i', $value)) {
            throw new Exception("Variable '$name' is not a SimpleText.");
        }

        return $value;
    }

    public static function SimpletextRequired($name)
    {
        $value = self::getRequired($name);
        if (!preg_match('/^[0-9a-zA-Z_-]+$/i', $value)) {
            throw new Exception("Required '$name' is not a SimpleText.");
        }

        return $value;
    }

    public static function DateRequired($name)
    {
        $value = self::getRequired($name);
        if (false == strtotime($value)) {
            throw new Exception("Required '$name' is not a Date.");
        }

        return date('Y-m-d', strtotime($value));
    }

    public static function DatewDefault($name, $default = false)
    {
        $value = self::getwDefault($name, $default);
        if (false == strtotime($value)) {
            throw new Exception("Required '$name' is not a Date.");
        }

        return date('Y-m-d', strtotime($value));
    }

    public static function getwDefault($name, $default = false)
    {
        if ($value = self::get($name)) {
            return $value;
        } else {
            return $default;
        }
    }

    public static function getRequired($name)
    {
        if (!$value = self::get($name)) {
            throw new Exception("Required '$name' is required.");
        } else {
            return $value;
        }
    }

    public static function get($name)
    {
        if (isset($_REQUEST[$name])) {
            return $_REQUEST[$name];
        } else {
            return false;
        }
    }
}
