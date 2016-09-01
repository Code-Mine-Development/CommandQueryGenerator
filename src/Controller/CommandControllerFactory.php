<?php
namespace CodeMine\CommandQueryGenerator\Controller;


use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @author Radek Adamiec <radek@code-mine.com>.
 */
class CommandControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = NULL)
    {

        $config = $container->get('ApplicationConfig');

        return new CommandController($config, $container);
    }



}