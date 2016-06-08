<?php
namespace Installer\Builder;

//use \Phalcon\Builder\Model as Builder;
use Installer\Builder\Model as ModelBuilder;
use Phalcon\Utils;
use SplFileObject;


class BuildTask extends \Phalcon\Cli\Task
{

    const PATH_BOOTSTRAP = 'Bootstrap';
    const PATH_COLLECTIONS = 'Collections';
    const PATH_CONTROLLERS = 'Controllers';
    const PATH_MODELS = 'Model';
    const PATH_RESOURCES = 'Resources';
    const PATH_TRANSFORMERS = 'Transformers';
    const OUTPUT_PATH = 'library/App';

    protected $apiDefinition;


    public function mainAction()
    {
        $this->apiDefinition = $this->di->getShared('api_definition');

        // Build Objects: Model, Resources, Transformers, Controllers, then Bootstrap
        $bootstrapStubDefinition = '';
        $bootstrapStub = '';
        foreach ($this->apiDefinition as $collectionType => $items) {
            foreach ($items as $resource => $options) {
                $className = Utils::camelize($resource);
                $modelName = !empty($options->model) ? $options->model : $resource;
                $modelName = \Phalcon\Text::uncamelize($modelName);

                // Build Model
                $modelBuilder = new ModelBuilder(ModelBuilder::getDefaultOptions($modelName));
                $modelBuilder->build();

                // Build Resource
                $file = $this->getObjectFilePath($className, self::PATH_RESOURCES);
                $code = self::getStub('resources') . self::getStubResource($resource, $options) . ";\n}\n}";
                $this->write($file, $code);

                // Build Transformer
                $file = $this->getObjectFilePath($className, self::PATH_TRANSFORMERS);
                $code = self::getStub('transformers') . self::getStubTransformer($className);
                $this->write($file, $code);
                
                // @todo Build Controller
                
                // Bootrapable items
                $scope = self::getConfigItem($options, 'scope', 'factory');
                $collectionClassName = Utils::camelize($resource) . Utils::camelize($collectionType);
                $bootstrapStubDefinition .= "use App\\" . Utils::camelize($collectionType) . "s\\" . $collectionClassName . ";\n";
                $bootstrapStub .= "\n\t\t\t->collection(" . $collectionClassName . "::" . $scope . "('/" . $resource . "s'))";

            }
        }

        // Build Bootstrap
        $file = $this->getObjectFilePath('Collection', self::PATH_BOOTSTRAP);
        $code = self::getStub('bootstrap') . $bootstrapStubDefinition . self::getStubBootstrap() . $bootstrapStub . ";\n\t}\n}";
        $this->write($file, $code);
    }

    public static function getStub($locator)
    {
        $file = BASE_PATH . '/bin/Stub/' . $locator . '.def.stub';
        return file_exists($file) ? file_get_contents($file) : '';
    }


    public static function getStubResource($resource, $options)
    {
        $name = Utils::camelize($resource);
        $modelClassName = self::getConfigItem($options, 'model', $name);
        $transformerClassName = self::getConfigItem($options, 'transformer', $name) . 'Transformer';

        $code = "\nuse App\\Model\\" . $modelClassName . ";";
        $code .= "\nuse App\\Transformers\\" . $transformerClassName . ";";

        $code .= '
        
class ' . $name . 'Resource extends Resource {

    public function initialize()
    {
        $this
            ->name(\'' . $name . '\')
            ->model(' . $modelClassName . '::class)
            ->transformer(' . $transformerClassName . '::class)
            ->expectsJsonData()
            ->itemKey(\'' . $resource . '\')
            ->collectionKey(\'' . $resource . 's\')';
        $code .= "\n\t\t\t" . self::getConfigListItem($options, 'deny', 'AclRoles::');

        if (!empty($options->endpoints)) {
            foreach ($options->endpoints as $endpoint => $option) {
                $code .= "\n\t\t\t->endpoint(Endpoint::" . $endpoint . "()" .
                    "\n\t\t\t\t" . self::getConfigListItem($option, 'allow', 'AclRoles::') .
                    "\n\t\t\t\t->description('" . self::getConfigItem($option, 'description', '') . "')\n\t\t\t)";
            }
        }
        return $code;

    }

    public static function getStubTransformer($className)
    {

        $code = '
use App\Model\\' . $className . ';

class ' . $className . 'Transformer extends ModelTransformer
{
    protected $modelClass = ' . $className . '::class;
}
';

        return $code;
    }

    public static function getStubBootstrap()
    {
        return 'class CollectionBootstrap implements BootstrapInterface
{
    public function run(Api $api, DiInterface $di, Config $config)
    {
        $api
            ->collection(ExportCollection::factory(\'/export\'))
            ->resource(UserResource::factory(\'/users\'))
            ';
    }

    public static function getConfigItem(\Phalcon\Config $config, $item, $defaultValue)
    {
        return !empty($config->$item) ? $config->$item : $defaultValue;
    }

    public static function getConfigListItem(\Phalcon\Config $config, $item, $parent = null, $delimiter = ',')
    {
        if (!empty($config->$item)) {
            $prefix = '->' . $item . '(';
            $parent = null === $parent ? '' : $parent;
            $glue = $delimiter . ' ' . $parent;
            $foo = $prefix . $parent . implode($glue, explode($delimiter, $config->$item)) . ')';
            return $prefix . $parent . implode($glue, explode($delimiter, $config->$item)) . ")";
        }
    }


    protected function getObjectFilePath($className, $classType)
    {
        // Remove trailing 's'
        $classNameSuffix = substr($classType, -1) == 's' ? rtrim($classType, 's') : $classType;
        return APPLICATION_PATH . DIRECTORY_SEPARATOR . self::OUTPUT_PATH . DIRECTORY_SEPARATOR . $classType . DIRECTORY_SEPARATOR . $className . $classNameSuffix . ".php";
    }

    protected function write($file, $payload)
    {

        $writer = new SplFileObject($file, 'w');

        if (!$writer->fwrite($payload)) {
            throw new BuilderException(
                sprintf('Unable to write to %s. Check write-access of a file.', $writer->getRealPath())
            );
        }

        echo $writer->getRealPath(), PHP_EOL;
    }


}
