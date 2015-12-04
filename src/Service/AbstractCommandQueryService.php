<?php
/**
 * @author Radek Adamiec <radek@code-mine.com>.
 */

namespace CodeMine\CommandQueryGenerator\Service;


use CodeMine\CommandQuery\CommandQueryInterface;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\FileGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Server\Reflection\ReflectionClass;
use Zend\ServiceManager\FactoryInterface;

abstract class AbstractCommandQueryService
{
    protected $commandQueryName;
    protected $modulePath;
    protected $moduleName;
    /**
     * @var \CodeMine\CommandQueryGenerator\Service\DirectoryService
     */
    protected $directoryService;

    /**
     * AbstractCommandQueryService constructor.
     *
     * @param                                                 $commandQueryName
     * @param                                                 $moduleName
     * @param \CodeMine\CommandQueryGenerator\Service\DirectoryService $directoryService
     */
    public function __construct($commandQueryName, $moduleName, DirectoryService $directoryService)
    {

        $this->commandQueryName = $commandQueryName;
        $this->modulePath       = $directoryService->getPathForModule($moduleName);
        $this->moduleName       = $moduleName;
        $this->directoryService = $directoryService;
    }


    abstract public function getSuffixClass();

    abstract public function getDirectory();

    abstract public function getAbstractHandlerClassName();


    /**
     * @param $classToImplement
     * @param $classGenerator
     */
    protected function addMethodsFromInterface($classToImplement, ClassGenerator $classGenerator)
    {
        //Add methods from interface
        $reflection = new \ReflectionClass($classToImplement);
        foreach ($reflection->getMethods() as $method) {

            $staticFlag    = $method->isStatic() ? MethodGenerator::FLAG_STATIC : NULL;
            $publicFlag    = $method->isPublic() ? MethodGenerator::FLAG_PUBLIC : NULL;
            $privateFlag   = $method->isPrivate() ? MethodGenerator::FLAG_PRIVATE : NULL;
            $protectedFlag = $method->isProtected() ? MethodGenerator::FLAG_PROTECTED : NULL;


            //Get parameters for each method to add from interface
            $reflectionParameters = $method->getParameters();
            $parameters           = [];
            /** @var \ReflectionParameter $tmpParameter */
            foreach ($reflectionParameters as $tmpParameter) {
                //Need to do some magic to get type of the parameter :)
                if (NULL === $tmpParameter->getClass()) {
                    continue;
                }
                $classGenerator->addUse($tmpParameter->getClass()->name);
                $shortParameterTypeName = preg_replace('/(.*?)\\\/', '', $tmpParameter->getClass()->name);
                $parameters[]           = ['name' => $tmpParameter->getName(), 'type' => $shortParameterTypeName];
            }

            //Add the method to given class
            $classGenerator->addMethod(
                $method->getName(),
                $parameters,
                [
                    $staticFlag,
                    $publicFlag,
                    $privateFlag,
                    $protectedFlag,
                ]
            );
        }
    }


    protected function addMethodsFromAbstractClass($classToExtends, ClassGenerator $classGenerator)
    {
        //Add methods from interface
        $reflection = new \ReflectionClass($classToExtends);
        foreach ($reflection->getMethods() as $method) {
            if (FALSE === $method->isAbstract()) {
                continue;
            }

            $staticFlag    = $method->isStatic() ? MethodGenerator::FLAG_STATIC : NULL;
            $publicFlag    = $method->isPublic() ? MethodGenerator::FLAG_PUBLIC : NULL;
            $privateFlag   = $method->isPrivate() ? MethodGenerator::FLAG_PRIVATE : NULL;
            $protectedFlag = $method->isProtected() ? MethodGenerator::FLAG_PROTECTED : NULL;


            //Get parameters for each method to add from interface
            $reflectionParameters = $method->getParameters();
            $parameters           = [];
            /** @var \ReflectionParameter $tmpParameter */
            foreach ($reflectionParameters as $tmpParameter) {
                //Need to do some magic to get type of the parameter :)
                if (NULL === $tmpParameter->getClass()) {
                    continue;
                }

                $classGenerator->addUse($tmpParameter->getClass()->name);
                $shortParameterTypeName = preg_replace('/(.*?)\\\/', '', $tmpParameter->getClass()->name);
                $parameters[]           = ['name' => $tmpParameter->getName(), 'type' => $shortParameterTypeName];
            }

            //Add the method to given class
            $classGenerator->addMethod(
                $method->getName(),
                $parameters,
                [
                    $staticFlag,
                    $publicFlag,
                    $privateFlag,
                    $protectedFlag,
                ]
            );
        }
    }


    protected function createRealCommandQuery($commandPath, $name, $moduleName)
    {
        $this->directoryService->makeSureDirExist($commandPath);

        $this->directoryService->createAllNamespacedDir($name, $commandPath);


        $nameParts = explode('/', trim($name, '/'));

        $className          = sprintf('%s%s', $nameParts[count($nameParts) - 1], $this->getSuffixClass());
        $handlerName        = sprintf('%s%s', $className, 'Handler');
        $handlerFactoryName = sprintf('%s%s', $handlerName, 'Factory');


        unset($nameParts[count($nameParts) - 1]);


        //Set namespace for generated files
        $namespace      = sprintf('%s\\%s\\%s', $moduleName, $this->getDirectory(), implode('\\', $nameParts));
        $classGenerator = new ClassGenerator();
        $classGenerator->setNamespaceName(trim($namespace, '\\'));

        //Clone first class generator object so we don't need to set namespaces twice
        $handlerGenerator        = clone $classGenerator;
        $handlerFactoryGenerator = clone $classGenerator;


        //Set basic properties for command
        $classToImplement = CommandQueryInterface::class;
        $classGenerator->setName($className);
        $classGenerator->addUse($classToImplement);
        $classGenerator->setImplementedInterfaces(['CommandQueryInterface']);
        $this->addMethodsFromInterface($classToImplement, $classGenerator);


        //Set basic properties for command handler
        $classToImplement = $this->getAbstractHandlerClassName();
        $tmpRef           = new \ReflectionClass($classToImplement);
        $handlerGenerator->setName($handlerName);
        $handlerGenerator->addUse($classToImplement);
        $handlerGenerator->setExtendedClass($tmpRef->getShortName());
        $this->addMethodsFromAbstractClass($classToImplement, $handlerGenerator);


        //Set basic properties for command handler factory
        $classToImplement = FactoryInterface::class;
        $handlerFactoryGenerator->setName($handlerFactoryName);
        $handlerFactoryGenerator->addUse($classToImplement);
        $handlerFactoryGenerator->setImplementedInterfaces(['FactoryInterface']);
        $this->addMethodsFromInterface($classToImplement, $handlerFactoryGenerator);
        $method = $handlerFactoryGenerator->getMethod('createService');
        $handlerFactoryGenerator->addUse(sprintf('%s\%s',$handlerGenerator->getNamespaceName(), $handlerGenerator->getName()));
        $method->setBody(sprintf('return new %s();', $handlerGenerator->getName()));

        //GENERATE IT !!!
        $fileGenerator = FileGenerator::fromArray(['classes' => [$classGenerator]]);
        file_put_contents(sprintf('%s%s%s%s%s%s', $commandPath, DIRECTORY_SEPARATOR, implode(DIRECTORY_SEPARATOR, $nameParts), DIRECTORY_SEPARATOR, $className, '.php'), $fileGenerator->generate());

        $fileGenerator = FileGenerator::fromArray(['classes' => [$handlerGenerator]]);
        file_put_contents(sprintf('%s%s%s%s%s%s', $commandPath, DIRECTORY_SEPARATOR, implode(DIRECTORY_SEPARATOR, $nameParts), DIRECTORY_SEPARATOR, $handlerName, '.php'), $fileGenerator->generate());

        $fileGenerator = FileGenerator::fromArray(['classes' => [$handlerFactoryGenerator]]);
        file_put_contents(sprintf('%s%s%s%s%s%s', $commandPath, DIRECTORY_SEPARATOR, implode(DIRECTORY_SEPARATOR, $nameParts), DIRECTORY_SEPARATOR, $handlerFactoryName, '.php'), $fileGenerator->generate());

        return [
            sprintf('%s\%s', $classGenerator->getNamespaceName(), $classGenerator->getName()),
            sprintf('%s\%s', $handlerGenerator->getNamespaceName(), $handlerGenerator->getName()),
            sprintf('%s\%s', $handlerFactoryGenerator->getNamespaceName(), $handlerFactoryGenerator->getName()),
        ];
    }
}