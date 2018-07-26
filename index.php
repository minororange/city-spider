<?php
require './vendor/autoload.php';
$eloquent = new \util\Eloquent();
$eloquent->run();

try {
    $citySpider = new \util\CitySpider();
    $citySpider->run();
} catch (\GuzzleHttp\Exception\GuzzleException|\Exception $e) {
}