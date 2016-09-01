<?php
namespace CodeMine\CommandQueryGenerator\Controller;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;


/**
 * @author Radek Adamiec <radek@code-mine.com>.
 */
class QueryControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = NULL)
    {

        $config = $container->get('ApplicationConfig');

        return new QueryController($config, $container);
    }


}