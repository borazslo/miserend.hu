<?php

namespace Api;

class ReportFactory extends Api {
    
    public function create() {
        $api = new Api();
        $api->getInputJson();
        if(!$api->input['token']) {
            return new ReportByAnonym();
        } else {
            return new ReportByUser();
        }
    }
}
