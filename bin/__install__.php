#!/usr/bin/env php
<?php
namespace Installer;

/**
 * User: rakeshshrestha
 * Date: 6/06/2016
 * Time: 10:33 AM
 */
use \Phalcon\Di\FactoryDefault\Cli as CliDI,
    \Phalcon\Cli\Console as ConsoleApp,
    Installer\Builder\Config as ConfigBuilder
    ;


define('VERSION', '1.0.0');
define('APPLICATION_ENV', 'development');

// Define paths
defined('BASE_PATH') || define('BASE_PATH',  str_replace('/bin','',realpath(__DIR__)));
defined('APPLICATION_PATH') || define('APPLICATION_PATH', BASE_PATH . '/app');
defined('CONFIG_PATH') || define('CONFIG_PATH', APPLICATION_PATH . '/configs');

date_default_timezone_set('UTC');

// Update/ install dependencies, use composer
exec("composer install -d " . BASE_PATH);

// Require Composer autoload
require BASE_PATH . '/vendor/autoload.php';

// Loaders
$loader = new \Phalcon\Loader();


$loader->registerNamespaces([
    'Installer' => BASE_PATH .'/bin/',
]);

$loader->register();

// Load configurations
$configPath = CONFIG_PATH . '/default.php';

if (!is_readable($configPath)) {
    new ConfigBuilder();
}

$config = new \Phalcon\Config(include_once $configPath);

$overridePath = CONFIG_PATH . '/server.' . APPLICATION_ENV . '.php';

if (!is_readable($overridePath)) {
    throw new \Exception('Unable to read config from ' . $overridePath);
}

$override = new \Phalcon\Config(include_once $overridePath);

$config = $config->merge($override);

// Using the CLI factory default services container
$di = new CliDI();


$di->setShared('config', function () use ($config) {

    return $config;
});

$apiDefintion = new \Phalcon\Config\Adapter\Json(CONFIG_PATH . DIRECTORY_SEPARATOR . $config->application->apiDefinition);
$di->setShared('api_definition', function () use ($apiDefintion) {
    return $apiDefintion;
});

// Load database services
$di->set('database', function () use ($config, $di) {

    $dbClass = 'Phalcon\Db\Adapter\Pdo\\' . $config->database->adapter;
    $connection = new $dbClass(array(
        "host" => $config->database->host,
        "username" => $config->database->username,
        "password" => $config->database->password,
        "dbname" => $config->database->dbname
    ));

    //Assign the eventsManager to the db adapter instance
    $connection->setEventsManager($di->get('eventsManager'));

    return $connection;
});

// Create a console application
$console = new ConsoleApp();
$console->setDI($di);

// Process the console arguments
$arguments =[
    'task' => __NAMESPACE__ . '\Builder\\Build',
    'action' => 'main'
] ;

try {
    // Handle incoming arguments
    $console->handle($arguments);
} catch (\Phalcon\Exception $e) {
    echo $e->getMessage();
    exit(255);
}
