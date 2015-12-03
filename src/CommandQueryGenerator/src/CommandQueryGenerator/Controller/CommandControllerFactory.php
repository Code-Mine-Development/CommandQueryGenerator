<?php
namespace CodeMine\CommandQueryGenerator\Controller;


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



        return new CommandController($config);
    }


}