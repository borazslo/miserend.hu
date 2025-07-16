<?php

namespace Html;

class ApiDocs extends Html {

    /**
     * Az API endpoint osztályok nevei.
     * @var array
     */
    public $endpoints = [];
    
    
    public function __construct() {

        $endpointNames = self::collectApiEndpoints();
        foreach($endpointNames as $name) {
            $className = 'Api\\' . $name;
            $endpointClass = new $className();

            $endpoint = new class {};
            $endpoint->name = $name;
            $docs = method_exists($endpointClass, 'docs') ? $endpointClass->docs() : [];
            foreach($docs as $key => $value) {
                $endpoint->$key = $value;
            }
            $endpoint->title = $endpoint->title ?? $name; // Default title if not set
            $endpoint->requiredVersion = $endpointClass->requiredVersion ?? null; // Default to null if not set
            
            $this->endpoints[] = $endpoint;
            

        }
        

        //printr($this->endpoints);
        return;
    }

    /**
     * Összegyűjti az összes API endpoint osztály nevét.
     * Azaz visszaadja a classes/api könyvtárban található összes PHP fájl által ténylegesen definiált osztály nevét (kivéve api.php).
     *
     * @return array Az endpoint osztályok nevei (string)
     */
    public static function collectApiEndpoints() {
        $dir = __DIR__ . '/../api/';
        $files = scandir($dir);
        $result = [];
        foreach ($files as $file) {
            if (substr($file, -4) === '.php' && $file !== 'api.php') {
                $before = get_declared_classes();
                include_once($dir . $file);
                $after = get_declared_classes();
                $new = array_diff($after, $before);
                foreach ($new as $class) {
                    $result[] = preg_replace('/^Api\\\/', '', $class);
                }
            }
        }

        // Az Api osztály nem külön endpoint, ezért kivesszük a listából
        if (($key = array_search('Api', $result)) !== false) {
            unset($result[$key]);
            $result = array_values($result);
        }
        sort($result);
        return $result;
    }
}
