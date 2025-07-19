<?php

namespace Html;

class ApiDocs extends Html {

    /**
     * Az API endpoint osztályok nevei.
     * @var array
     */
    public $endpoints = [];
    
    
    public function __construct() {

        $endpointNames = \Api\Api::collectApiEndpoints();
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
        

        
        return;
    }

    
}
