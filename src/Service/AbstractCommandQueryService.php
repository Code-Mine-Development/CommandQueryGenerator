<?php
/**
 * @author Radek Adamiec <radek@code-mine.com>.
 */

namespace CodeMine\CommandQueryGenerator\Service;


use CodeMine\CommandQuery\CommandQueryInputFilterAwareInterface;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\FileGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\ServiceManager\Factory\FactoryInterface;

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
     * @param                                                          $commandQueryName
     * @param                                                          $moduleName
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

    abstract public function getCommandQueryInterfaceToImplement();


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
                $par = $this->getParametersForFunction($tmpParameter);

                $parameters[] = $par;

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
                $par = $this->getParametersForFunction($tmpParameter);

                $parameters[] = $par;
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

        $handlerGenerator = new ClassGenerator();
        $handlerGenerator->setNamespaceName(trim($namespace, '\\'));

        $handlerFactoryGenerator = new ClassGenerator();
        $handlerFactoryGenerator->setNamespaceName(trim($namespace, '\\'));


        //Set basic properties for command
        $commandQueryInterfaceToImplement = $this->getCommandQueryInterfaceToImplement();
        $classGenerator->setName($className);
//        $classGenerator->addUse($commandQueryInterfaceToImplement);
        $tmpRef = new \ReflectionClass($commandQueryInterfaceToImplement);
        $classGenerator->setImplementedInterfaces([$tmpRef->getName()]);
        $this->addMethodsFromInterface($commandQueryInterfaceToImplement, $classGenerator);


        //Set basic properties for command handler
        $commandHandlerClassToImplement = $this->getAbstractHandlerClassName();
        $tmpRef                         = new \ReflectionClass($commandHandlerClassToImplement);
        $handlerGenerator->setName($handlerName);
        $handlerGenerator->setExtendedClass($tmpRef->getName());
        $this->addMethodsFromAbstractClass($commandHandlerClassToImplement, $handlerGenerator);


        //Set basic properties for command handler factory
        $commandHandlerFactoryClassToImplement = FactoryInterface::class;
        $handlerFactoryGenerator->setName($handlerFactoryName);
        $handlerFactoryGenerator->addUse($commandHandlerFactoryClassToImplement);

        $handlerFactoryGenerator->setImplementedInterfaces([FactoryInterface::class]);
        $this->addMethodsFromInterface($commandHandlerFactoryClassToImplement, $handlerFactoryGenerator);
//        $method = $handlerFactoryGenerator->getMethod('__invoke');
////        $method->setParameters()
//        $method->setBody(sprintf('return new %s();', $handlerGenerator->getName()));

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

    /**
     * @param $tmpParameter
     *
     * @return array
     */
    protected function getParametersForFunction(\ReflectionParameter $tmpParameter)
    {
        //Need to do some magic to get type of the parameter :)
        if (NULL === $tmpParameter->getClass()) {

            if ($tmpParameter->getType() != NULL) {
                $par = ['name' => $tmpParameter->getName(), 'type' => $tmpParameter->getType()];

            } else {
                $par = ['name' => $tmpParameter->getName()];
            }


            if ($tmpParameter->isDefaultValueAvailable()) {
                $par['defaultvalue'] = $tmpParameter->getDefaultValue();

                return $par;
            }

            return $par;

        } else {
            $par = ['name' => $tmpParameter->getName(), 'type' => $tmpParameter->getClass()->name];
            if ($tmpParameter->isDefaultValueAvailable()) {
                $par['defaultvalue'] = $tmpParameter->getDefaultValue();

                return $par;
            }

            return $par;

        }
    }
}