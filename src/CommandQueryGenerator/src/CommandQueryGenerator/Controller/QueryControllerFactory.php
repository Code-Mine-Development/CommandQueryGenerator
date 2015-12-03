<?php
namespace CodeMine\CommandQueryGenerator\Controller;

use Zend\Mvc\Application;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @author Radek Adamiec <radek@code-mine.com>.
 */
class QueryControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {


        $config = $serviceLocator->getServiceLocator()->get('ApplicationConfig');



        return new QueryController($config);
    }


}