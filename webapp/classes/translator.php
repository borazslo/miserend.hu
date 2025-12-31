<?php

class Translator {
    private static $translations = [];
    private static $inited = false;

    public function __construct($lang = null)
    {
        // backward-compatible: allow creating an instance but delegate to static init
        self::init($lang);
    }

    public static function init($lang = null)
    {
        if (self::$inited) {
            return;
        }

        if (!$lang) {
            $accept = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
            if (preg_match('/^([a-zA-Z-]+)/', $accept, $m)) {
                $lang = explode('-', $m[1])[0];
            } else {
                $lang = 'en';
            }
        }

        $lang = preg_replace('/[^a-z]/', '', strtolower($lang)) ?: 'en';
        $path = __DIR__ . '/../i18n/' . $lang . '.json';

        // fallback to English if requested language file is missing
        if (!is_file($path) || !is_readable($path)) {
            $fallback = __DIR__ . '/../i18n/en.json';
            if (is_file($fallback) && is_readable($fallback)) {
                $path = $fallback;
            } else {
                // nothing to load, mark initialized to avoid repeated attempts
                self::$inited = true;
                return;
            }
        }

        $json = @file_get_contents($path);
        if ($json !== false) {
            $data = json_decode($json, true);
            if (is_array($data)) {
                self::$translations = array_merge(self::$translations, $data);
            }
        }

        self::flattenTranslations();
        self::$inited = true;
    }

    public static function translate($key, $default = null)
    {        
        if(is_array($key)) {
            foreach($key as &$k) {
                $k = self::translate($k, $default);
            }            
            return $key;
        }
        else 
            return self::$translations[$key] ?? ($default ?? $key);
    }

    private static function flattenTranslations() {
        // Flatten nested translation arrays into dot-separated keys
        $flattened = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator(self::$translations));
        foreach ($iterator as $value) {
            $keys = [];
            for ($depth = 0; $depth <= $iterator->getDepth(); $depth++) {
                $keys[] = $iterator->getSubIterator($depth)->key();
            }
            $flatKey = implode('.', $keys);
            $flattened[$flatKey] = $value;
        }
        self::$translations = $flattened;
    }
}