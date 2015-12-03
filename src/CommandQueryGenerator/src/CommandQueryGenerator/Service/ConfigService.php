<?php
/**
 * @author Radek Adamiec <radek@code-mine.com>.
 */

namespace CodeMine\CommandQueryGenerator\Service;


use Zend\Code\Generator\FileGenerator;

class ConfigService
{
    const CONFIG_NAME = 'command-query.global.php';
    private $objectPath;
    /**
     * @var \CodeMine\CommandQueryGenerator\Service\DirectoryService
     */
    private $directoryService;
    private $confPath;


    private function addToConfigObjects($objectName, $objectHandlerName, $objectHandlerFactoryName)
    {
        $conf = include $this->confPath;

        $fileGenerator = FileGenerator::fromReflectedFileName($this->confPath);


        $conf['service_manager']['factories'][$objectHandlerName] = $objectHandlerFactoryName;
        $conf['tactician']['handler-map'][$objectName]            = $objectHandlerName;
        $body                                                     = sprintf('return %s;', var_export($conf, TRUE));


        $newFile = new FileGenerator();
        $newFile->setUses($fileGenerator->getUses());
        $newFile->setBody($body);


        file_put_contents($this->confPath, $newFile->generate());

    }



    /**
     * ConfigService constructor.
     *
     * @param                                                 $objectPath
     * @param \CodeMine\CommandQueryGenerator\Service\DirectoryService $directoryService
     */
    public function __construct($objectPath, DirectoryService $directoryService)
    {
        $this->objectPath       = $objectPath;
        $this->directoryService = $directoryService;
        $configDir              = $this->directoryService->getPathForAutoloadConfigs();
        $confPath               = $configDir . DIRECTORY_SEPARATOR . self::CONFIG_NAME;
        $this->confPath         = $confPath;
    }


    public function addCommandQueryToConfig($objectName, $objectHandlerName, $objectHandlerFactoryName)
    {
        $this->makeSureConfigFileExist();

        $this->addToConfigObjects($objectName, $objectHandlerName, $objectHandlerFactoryName);
    }


    private function makeSureConfigFileExist()
    {


        if (FALSE === file_exists($this->confPath)) {
            $fileGenerator = new FileGenerator();
            $body          = $this->getConfigTemplate();

            $fileGenerator->setBody($body);
            file_put_contents($this->confPath, $fileGenerator->generate());
        }
    }


    /**
     * @return string
     */
    private function getConfigTemplate()
    {
        return file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR .
               'template' . DIRECTORY_SEPARATOR . 'config.template');
    }
}