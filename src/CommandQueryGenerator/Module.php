<?php
namespace CommandQueryGenerator;

use Zend\Console\Adapter\AdapterInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;


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
            'generate query --module= --name= [--add-factory=]'   => 'Generate query in given module',
            [
                '--module', 'Module name (Example: Application)',
            ],
            [
                '--name', 'Command name (Example: User/Create, CreateUser, Domain/Create/User)',
            ],
            [
                '--add-factory', 'Add factory for command handler (Y/n) - Not yet supported',
            ],
        ];
    }


}
