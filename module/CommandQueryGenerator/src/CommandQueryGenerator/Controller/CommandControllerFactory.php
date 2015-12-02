<?php
namespace CommandQueryGenerator\Controller;

use Zend\Mvc\Application;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @author Radek Adamiec <radek@code-mine.com>.
 */
class CommandControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {


        $config = $serviceLocator->getServiceLocator()->get('ApplicationConfig');
        $rootPath = dirname(__FILE__);
        for($i = 0; $i < 20; $i++){
            $rootPath = sprintf('%s%s%s', $rootPath, DIRECTORY_SEPARATOR, '..');
            if(TRUE === is_dir($rootPath.DIRECTORY_SEPARATOR.'public') && TRUE === file_exists($rootPath.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'index.php')){
                break;
            }
        }


        return new CommandController($config, $rootPath);
    }


}