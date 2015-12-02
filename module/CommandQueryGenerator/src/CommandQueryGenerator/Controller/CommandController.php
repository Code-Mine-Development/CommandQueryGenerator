<?php
namespace CommandQueryGenerator\Controller;

use CommandQueryGenerator\Service\GetPathForModuleService;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\FileGenerator;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Mvc\Controller\AbstractActionController;

/**
 * @author Radek Adamiec <radek@code-mine.com>.
 */
class CommandController extends AbstractActionController
{

    const CLASS_SUFFIX = 'Command';
    const CLASS_DIR    = 'Command';


    /**
     * @var array
     */
    private $config;
    /**
     * @var
     */
    private $rootPath;


    /**
     * CommandController constructor.
     *
     * @param array $config
     * @param       $rootPath
     */
    public function __construct(array $config, $rootPath)
    {
        $this->config   = $config;
        $this->rootPath = $rootPath;
    }

    public function generateCommandAction()
    {
        /** @var AdapterInterface $console */
        $console = $this->getServiceLocator()->get('console');
        if (!$console instanceof AdapterInterface) {
            throw new \RuntimeException('Cannot obtain console adapter. Are we running in a console?');
        }

        $service    = new GetPathForModuleService($this->config, $this->rootPath);
        $moduleName = $this->getRequest()->getParam('module');
        $name       = $this->getRequest()->getParam('name');

        if (NULL === $name) {
            throw  new \RuntimeException('You must provide name for command');
        }

        try {
            $modulePath  = $service->getPath($moduleName);
            $commandPath = $modulePath . DIRECTORY_SEPARATOR . self::CLASS_DIR;

            $this->makeSureCommandDirExist($commandPath);

            $this->createAllNamespacedDirForCommand($name, $commandPath);

            $this->createCommandClasses($name, $commandPath, $moduleName);


        } catch (\InvalidArgumentException $ex) {
            throw new \RuntimeException('Cannot find path for module. Please check module name');
        }
    }

    /**
     * @param $commandPath
     */
    private function makeSureCommandDirExist($commandPath)
    {
        if (FALSE === is_dir($commandPath)) {
            mkdir($commandPath);
        }
    }

    /**
     * @param $name
     * @param $commandPath
     */
    private function createAllNamespacedDirForCommand($name, $commandPath)
    {
        $pathComponents = explode('/', trim($name, '/'));

        for ($i = 0; $i < count($pathComponents) - 1; $i++) {
            $commandPath = sprintf('%s%s%s', $commandPath, DIRECTORY_SEPARATOR, $pathComponents[$i]);
            @mkdir($commandPath);
            if (FALSE === is_dir($commandPath)) {
                throw new \RuntimeException('Could not create directory for command');
            }
        }
    }

    /**
     * @param $name
     * @param $commandPath
     */
    private function createCommandClasses($name, $commandPath, $moduleName)
    {
        $nameParts = explode('/', trim($name, '/'));

        $className          = sprintf('%s%s', $nameParts[count($nameParts) - 1], self::CLASS_SUFFIX);
        $handlerName        = sprintf('%s%s', $className, 'Handler');
        $handlerFactoryName = sprintf('%s%s', $handlerName, 'Factory');


        unset($nameParts[count($nameParts) - 1]);

        $namespace      = sprintf('%s\\%s\\%s', $moduleName, self::CLASS_DIR, implode('\\', $nameParts));
        $classGenerator = new ClassGenerator();
        $classGenerator->setName($className);
        $classGenerator->setNamespaceName(trim($namespace, '\\'));

        $handlerGenerator = clone $classGenerator;
        $handlerGenerator->setName($handlerName);

        $handlerFactoryGenerator = clone $classGenerator;
        $handlerFactoryGenerator->setName($handlerFactoryName);

        $fileGenerator = FileGenerator::fromArray(['classes'=>[$classGenerator]]);
        file_put_contents(sprintf('%s%s%s%s%s%s', $commandPath, DIRECTORY_SEPARATOR, implode(DIRECTORY_SEPARATOR, $nameParts), DIRECTORY_SEPARATOR, $className, '.php'), $fileGenerator->generate());

        $fileGenerator = FileGenerator::fromArray(['classes'=>[$handlerGenerator]]);
        file_put_contents(sprintf('%s%s%s%s%s%s', $commandPath, DIRECTORY_SEPARATOR, implode(DIRECTORY_SEPARATOR, $nameParts), DIRECTORY_SEPARATOR, $handlerName, '.php'), $fileGenerator->generate());

        $fileGenerator = FileGenerator::fromArray(['classes'=>[$handlerFactoryGenerator]]);
        file_put_contents(sprintf('%s%s%s%s%s%s', $commandPath, DIRECTORY_SEPARATOR, implode(DIRECTORY_SEPARATOR, $nameParts), DIRECTORY_SEPARATOR, $handlerFactoryName, '.php'), $fileGenerator->generate());

    }

}