<?php
/**
 * @author Radek Adamiec <radek@code-mine.com>.
 */

namespace CodeMine\CommandQueryGenerator\Service;


use CodeMine\CommandQuery\AbstractQueryHandler;
use CodeMine\CommandQuery\CommandQueryInterface;

class CreateQueryService extends AbstractCommandQueryService
{

    const CLASS_SUFFIX = 'Query';
    const CLASS_DIR    = 'Query';


    /**
     *
     */
    public function createQueryService()
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
        return AbstractQueryHandler::class;
    }

    public function getCommandQueryInterfaceToImplement()
    {
        return CommandQueryInterface::class;
    }


}