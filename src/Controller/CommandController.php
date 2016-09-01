<?php
namespace CodeMine\CommandQueryGenerator\Controller;

use CodeMine\CommandQueryGenerator\Service\CreateCommandService;
use CodeMine\CommandQueryGenerator\Service\DirectoryService;
use Interop\Container\ContainerInterface;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Mvc\Controller\AbstractActionController;

/**
 * @author Radek Adamiec <radek@code-mine.com>.
 */
class CommandController extends AbstractActionController
{


    /**
     * @var array
     */
    private $config;
    /**
     * @var \Interop\Container\ContainerInterface
     */
    private $container;

    /**
     * CommandController constructor.
     *
     * @param array                                 $config
     * @param \Interop\Container\ContainerInterface $container
     */
    public function __construct(array $config, ContainerInterface $container)
    {
        $this->config    = $config;
        $this->container = $container;
    }

    public function generateCommandAction()
    {
        /** @var AdapterInterface $console */
        $console = $this->container->get('console');
        if (!$console instanceof AdapterInterface) {
            throw new \RuntimeException('Cannot obtain console adapter. Are we running in a console?');
        }

        $service    = new DirectoryService($this->config);
        $moduleName = $this->getRequest()->getParam('module');
        $name       = $this->getRequest()->getParam('name');

        if (NULL === $name) {
            throw  new \RuntimeException('You must provide name for command');
        }

        try {

            $createCommandService = new CreateCommandService($name, $moduleName, $service);
            $createCommandService->createCommandClasses();


        } catch (\InvalidArgumentException $ex) {

            throw new \RuntimeException('Could not create command. ERROR MESSAGE:' . PHP_EOL . $ex->getMessage());
        }
    }


}