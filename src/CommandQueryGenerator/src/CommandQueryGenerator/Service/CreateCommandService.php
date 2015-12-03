<?php
/**
 * @author Radek Adamiec <radek@code-mine.com>.
 */

namespace CommandQueryGenerator\Service;


use CodeMine\CommandQuery\AbstractCommandHandler;

class CreateCommandService extends AbstractCommandQueryService
{

    const CLASS_SUFFIX = 'Command';
    const CLASS_DIR    = 'Command';


    /**
     * @param $name
     * @param $commandPath
     * @param $moduleName
     */
    public function createCommandClasses()
    {
        $name        = $this->commandQueryName;
        $commandPath = $this->modulePath . DIRECTORY_SEPARATOR . self::CLASS_DIR;
        $moduleName  = $this->moduleName;


        list($commandFullClassName, $commandHandlerFullClassName, $commandHandlerFactoryFullClassName) =
            $this->createRealCommandQuery($commandPath, $name, $moduleName);

        $configService = new ConfigService($commandPath, $this->directoryService);

        $configService->addCommandQueryToConfig($commandFullClassName, $commandHandlerFullClassName, $commandHandlerFactoryFullClassName);


    }

    public function getSuffixClass()
    {
        return self::CLASS_SUFFIX;
    }

    public function getDirectory()
    {
        return self::CLASS_DIR;
    }

    public function getAbstractHandlerClassName()
    {
        return AbstractCommandHandler::class;
    }


}