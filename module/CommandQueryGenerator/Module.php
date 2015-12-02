<?php
namespace CommandQueryGenerator;

use Zend\Console\Adapter\AdapterInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\ModuleManager\ModuleEvent;
use Zend\ModuleManager\ModuleManager;

class Module implements ConsoleUsageProviderInterface
{


    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ],
            ],
        ];
    }

    public function getConsoleUsage(AdapterInterface $console)
    {
        return [
            'Generate Command',
            'generate command --module= --name= [--add-factory=]' => 'Generate command in given module',
            [
                '--module', 'Module name',
            ],
            [
                '--name', 'Command name',
            ],
            [
                '--add-factory', 'Add factory for command handler (Y/n)',
            ],
        ];
    }



}
