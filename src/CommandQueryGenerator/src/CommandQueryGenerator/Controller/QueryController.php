<?php
namespace CodeMine\CommandQueryGenerator\Controller;


use CodeMine\CommandQueryGenerator\Service\CreateQueryService;
use CodeMine\CommandQueryGenerator\Service\DirectoryService;
use Zend\Console\Adapter\AdapterInterface;
use Zend\Mvc\Controller\AbstractActionController;

/**
 * @author Radek Adamiec <radek@code-mine.com>.
 */
class QueryController extends AbstractActionController
{


    /**
     * @var array
     */
    private $config;

    /**
     * CommandController constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }


    public function generateQueryAction()
    {
        /** @var AdapterInterface $console */
        $console = $this->getServiceLocator()->get('console');
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

            $createCommandService = new CreateQueryService($name, $moduleName, $service);
            $createCommandService->createQueryService();


        } catch (\InvalidArgumentException $ex) {

            throw new \RuntimeException('Could not create query. ERROR MESSAGE:' . PHP_EOL . $ex->getMessage());
        }
    }


}