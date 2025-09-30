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
            
            if(isset($endpointClass->fields)) {
                $docs['input'] = [];
                foreach($endpointClass->fields as $field => $details) {
                    $docs['input'][$field] = [
                        (isset($details['required']) && $details['required'] ? 'required' : 'optional'),
                        isset($details['validation']) 
                            ? (is_array($details['validation']) 
                                ? implode('<br>', array_map(
                                    fn($k, $v) => $k . ': ' . (is_array($v) ? json_encode($v) : $v),
                                    array_keys($details['validation']),
                                    $details['validation']
                                ))
                                : $details['validation']
                              )
                            : '', // type
                        isset($details['description']) ? $details['description'] : '', // description
                        isset($details['default']) 
                            ? ($details['default'] === false ? 'false' 
                                : ($details['default'] === true ? 'true' 
                                    : $details['default'])) 
                            : '', // default
                        
                    ];
                }
            }
            
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
