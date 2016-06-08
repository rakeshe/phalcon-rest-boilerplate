<?php
namespace Installer\Builder;
/**
 * User: rakeshshrestha
 * Date: 6/06/2016
 * Time: 2:05 PM
 */
class Config
{
    protected $projectName;
    protected $dbAdapter = "Mysql";
    protected $dbHost = "localhost";
    protected $dbUser = "root";
    protected $dbPassword = "";
    protected $dbName = "ouffer_preview";
    protected $allowedOrigins = "*";
    protected $apiToken = "super_secret";
    protected $definitionFile;

    protected $defaultConfigs;
    protected $defaultServerConfigs;


    public function __construct()
    {


        // Lets ask some questions
        self::printMessage("Code Generator for Phalcon Rest Api\nBugs/comments: rakeshshrestha@luxgroup.com");

        $this->projectName = $this->request(" Api Host ? ", $this->getProjectName());
        $this->dbAdapter = $this->request(" Database adaptert? default ", $this->dbAdapter);
        $this->dbHost = $this->request(" Database host? default ", $this->dbHost);
        $this->dbUser = $this->request(" Database user? default ", $this->dbUser);
        $this->dbPassword = $this->request(" Database password? ");
        $this->dbName = $this->request(" Database name? default ", $this->dbName);
        $this->allowedOrigins = $this->request(" Allowed Origins? default ", $this->allowedOrigins);
        $this->apiToken = $this->request(" Api token? default ", $this->getRandomToken());

        while (empty($definitionFile)) {
            $definitionFile = $this->request(" File path to api config definition (json) file? ");
        }
        $this->definitionFile = $definitionFile;
        $this
            ->setDefaultConfigs()
            ->setDefaultServerConfigs()
            ->write();

        $thisHost = "http://" . $this->getProjectName() . ".api";
        $endMsg = "Your " . $this->getProjectName() . " Api is all setup\nNext steps:\nSetup Virtual host for {$thisHost}" .
            "\nand restart your webserver.\n\nApi Documentation:\n{$thisHost}/documentation.html\n\njson file for Postman:" .
            "\n{$thisHost}/export/postman.json\n\nBugs/comments: rakeshshrestha@luxgroup.com";
        self::printMessage($endMsg);

    }

    public static function printMessage($message)
    {
        $messages = explode("\n", $message);
        printf("+%'-50s+\n",  "");
        printf("+%-50s+\n",  "");
        foreach ($messages as $msg){
            printf("+%-50s+\n",  "   ". $msg);
        }
        printf("+%-50s+\n",  "");
        printf("+%'-50s+\n",  "");

    }
    /**
     * @param mixed $defaultConfigs
     */
    public function setDefaultConfigs()
    {
        $this->setupDefinitionFile();
        $this->defaultConfigs = "<?php
return [
    'application' => [
        'title' => '" . $this->projectName . " REST Api',
        'description' => 'This is " . $this->projectName . " REST Api application.',
        'baseUri' => '/',
        'viewsDir' => __DIR__ . '/../views/',
        'apiDefinition' => '" . $this->getDefinitionFileName() . "'
    ],
    'authentication' => [
        'secret' => '" . $this->apiToken . "',
        'expirationTime' => 86400 * 7, // One week till token expires
    ]
];";
        return $this;
    }

    public function setupDefinitionFile(){
        if(file_exists($this->definitionFile)){
            copy($this->definitionFile, CONFIG_PATH .'/' . basename($this->definitionFile));
        } else {
            die('Api config definition not found ' . $this->definitionFile);
        }
    }

    public function getDefinitionFile(){
        return CONFIG_PATH .'/' . basename($this->definitionFile);
    }

    public function getDefinitionFileName(){
        return basename($this->definitionFile);
    }
    /**
     * @param mixed $defaultServerConfigs
     */
    public function setDefaultServerConfigs()
    {
        $this->defaultServerConfigs = "<?php
return [
    'debug' => true,
    'hostName' => 'http://" . $this->projectName . ".api',
    'clientHostName' => 'http://" . $this->projectName . ".api',
    'database' => [
        'adapter' => '" . $this->dbAdapter . "',
        'host' => '" . $this->dbHost . "',
        'username' => '" . $this->dbUser . "',
        'password' => '" . $this->dbPassword . "',
        'dbname' => '" . $this->dbName . "',
    ],
    'cors' => [
        'allowedOrigins' => ['" . $this->allowedOrigins . "']
    ]
];";

        return $this;
    }

    protected function write()
    {

        // Create config files

        $defaultConfigFile = fopen(CONFIG_PATH . '/default.php', 'w');
        fputs($defaultConfigFile, $this->defaultConfigs);

        $devConfigFile = fopen(CONFIG_PATH . '/server.development.php', 'w');
        fputs($devConfigFile, $this->defaultServerConfigs);
        fclose($devConfigFile);
        fclose($defaultConfigFile);
        return $this;

    }


// Functions to keep things simpler
    protected function request($promptStr, $defaultVal = false)
    {
        ;

        if ($defaultVal) {
            echo $promptStr . "[" . $defaultVal . "] : ";
        } else {
            echo $promptStr . ": ";
        }
        $name = chop(fgets(STDIN));
        if (empty($name)) {
            return $defaultVal;
        } else {
            return $name;
        }
    }

    protected function getProjectName()
    {
        $argv = $_SERVER['argv'];
        if (!empty($argv[1])) {
            $projectName = $argv[1];
        } else {
            $pathParts = explode("/", __DIR__);
            $projectName = $pathParts[count($pathParts) - 3];
        }

        return $projectName;
    }


    public static function getRandomToken()
    {
        return bin2hex(openssl_random_pseudo_bytes(4));
    }


}