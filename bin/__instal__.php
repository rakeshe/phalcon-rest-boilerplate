#!/usr/bin/env php
<?php
/**
 * User: rakeshshrestha
 * Date: 6/06/2016
 * Time: 10:33 AM
 */
// Init
$basePath = str_replace("/bin", "", __DIR__);

// Lets ask some questions
$projectName = request(" Api Host ? ", getProjectName());
$dbAdapter = request(" Database adaptert? default ","Mysql");
$dbHost = request(" Database host? default ","localhost");
$dbUser = request(" Database user? default ","root");
$dbPassword = request(" Database password? ");
$dbName = request(" Database name? default ","ouffer_preview");
$allowedOrigins = request(" Allowed Origins? default ","*");
$apiToken = request(" Api token? default ",getRandomToken());

while(empty($configFile)){
    $configFile  = request(" File path to api config (json) file? ");
}

$defaultConfigs = "<?php
return [
    'application' => [
        'title' => '{$projectName} REST Api',
        'description' => 'This is {$projectName} REST Api application.',
        'baseUri' => '/',
        'viewsDir' => __DIR__ . '/../views/',
    ],
    'authentication' => [
        'secret' => '{$apiToken}',
        'expirationTime' => 86400 * 7, // One week till token expires
    ]
];

";
$devConfigs = "<?php
return [
    'debug' => true,
    'hostName' => 'http://{$projectName}.api',
    'clientHostName' => 'http://{$projectName}.api',
    'database' => [
        'adapter' => '{$dbAdapter}',
        'host' => '{$dbHost}',
        'username' => '{$dbUser}',
        'password' => '{$dbPassword}',
        'dbname' => '{$dbName}',
    ],
    'cors' => [
        'allowedOrigins' => ['{$allowedOrigins}']
    ]
]";

// Create config files

$defaultConfigFile = fopen($basePath .'/app/configs/default.php', 'w');
fputs($defaultConfigFile, $defaultConfigs);

$devConfigFile = fopen($basePath .'/app/configs/server.development.php', 'w');
fputs($devConfigFile, $devConfigs);
fclose($devConfigFile);
fclose($defaultConfigFile);

exit(0); // Finish


// Functions to keep things simpler
function request($promptStr,$defaultVal=false){;

    if($defaultVal) {
        echo $promptStr. "[". $defaultVal. "] : ";
    }
    else {
        echo $promptStr. ": ";
    }
    $name = chop(fgets(STDIN));
    if(empty($name)) {
        return $defaultVal;
    }
    else {
        return $name;
    }
}

function getProjectName(){
    $argv = $_SERVER['argv'];
    $projectName = '';
    if(!empty($argv[1])) {
        $projectName = $argv[1];
    }else{
        $pathParts = explode("/", __DIR__);
        $projectName = $pathParts[count($pathParts)-2];
    }

    return $projectName;
}


function getRandomToken(){
    return bin2hex(openssl_random_pseudo_bytes(4));
}