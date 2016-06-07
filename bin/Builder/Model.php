<?php
/**
 * User: rakeshshrestha
 * Date: 7/06/2016
 * Time: 10:08 AM
 */

namespace Installer\Builder;

use \Phalcon\Builder\Model as ModelBuilder;
use Phalcon\Utils;

class Model extends ModelBuilder
{
    const NAMESPACE_DEFAULT = 'App\\Model';
    const EXTENDS_DEFAULT = '\Phalcon\Mvc\Model';
    const EXTENDS_DATETIME_TRACKING = '\App\Mvc\DateTrackingModel';
    const OUTPUT_PATH = 'library/App/Model';
    const INCLUDE_GET_SET = 1;
    const INCLUDE_PHP_DOC = 1;

    protected $namespace = 'App\\Model';
    protected $extends = self::EXTENDS_DATETIME_TRACKING;
    protected $output = self::OUTPUT_PATH;
    protected $schema;


    public function getConfig($type = null)
    {
        $di = \Phalcon\DI::getDefault();
        return $di->getShared('config');
    }

    public static function getDefaultOptions($resource, $schema = null)
    {
        $className = Utils::camelize($resource);
        $fileName = \Phalcon\Text::uncamelize($className);

        return [
                'name' => $resource,
                'schema' => $schema,
                'className' => $className,
                'fileName' => $fileName,
                'genSettersGetters' => self::INCLUDE_GET_SET,
                'genDocMethods' => self::INCLUDE_PHP_DOC,
                'namespace' => self::NAMESPACE_DEFAULT,
                'directory' => BASE_PATH,
                'modelsDir' => APPLICATION_PATH . DIRECTORY_SEPARATOR . self::OUTPUT_PATH,
                'extends' => self::EXTENDS_DEFAULT,
                'excludeFields' => null,
                'force' => 1,
                'mapColumn' => null,
                'abstract' => null
            ];
        
    }

    public function build()
    {
        $modelFilePath = $this->options->get('modelsDir') . DIRECTORY_SEPARATOR . $this->options->get('className') . '.php';
        if (file_exists($modelFilePath)) unlink($modelFilePath);

        parent::build();
    }

    /**
     * @return mixed
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param mixed $namespace
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * @return mixed
     */
    public function getExtends()
    {
        return $this->extends;
    }

    /**
     * @param mixed $extends
     */
    public function setExtends($extends)
    {
        $this->extends = $extends;
    }

    /**
     * @return mixed
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param mixed $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

    /**
     * @return mixed
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * @param mixed $schema
     */
    public function setSchema($schema)
    {
        $this->schema = $schema;
    }



}