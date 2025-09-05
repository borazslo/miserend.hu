<?php
use Externalapi\ElasticsearchApi;

try {
    //TODO: EZ CSAK A FEJLESZTÉSHEZ KELL!
    set_time_limit(300);
    ini_set('memory_limit', '512M');
    ElasticsearchApi::updateChurches();
} catch (Exception $e) {
    error_log("updateChurches() error: " . $e->getMessage());
    echo "A frissítés során hiba történt: " . $e->getMessage();
}