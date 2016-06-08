<?php
/**
 * User: rakeshshrestha
 * Date: 9/06/2016
 * Time: 8:25 AM
 */
namespace Installer\Builder;

class Helper
{

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

    public static function getRandomToken()
    {
        return bin2hex(openssl_random_pseudo_bytes(4));
    }


    public static function getProjectName()
    {
        $projectName = '';
        $argv = $_SERVER['argv'];
        if (!empty($argv[1])) {
            $projectName = $argv[1];
        } else {
            $pathParts = explode("/", __DIR__);
            $projectName = $pathParts[count($pathParts) - 3];
        }

        return $projectName;
    }
    
}