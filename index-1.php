<?php

use Bolt\App;
use Bolt\Encryption;

require_once './vendor/autoload.php';

if (php_sapi_name() !== 'cli') {
    exit;
}

$app = new App();

$app->registerCommand('-encrypt', function (array $argv) use ($app) {

    if( empty($argv[2]) || empty($argv[3]) || empty($argv[4])){
        $app->getPrinter()->display("usage: bolt -encrypt [source-folder] [key] [output-folder]");
        return;
    }
    $enc = new Encryption;
    $enc->encrypt($argv[2], $argv[3], $argv[4]);
});

$app->registerCommand('-help', function (array $argv) use ($app) {
    $app->getPrinter()->display("usage: bolt -encrypt [source-folder] [key] [output-folder]");
});
$app->registerCommand('-h', function (array $argv) use ($app) {
    $app->getPrinter()->display("usage: bolt -encrypt [source-folder] [key] [output-folder]");
});

$app->runCommand($argv);