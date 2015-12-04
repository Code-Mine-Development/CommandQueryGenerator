<?php
/**
 * @author Radek Adamiec <radek@code-mine.com>.
 */

namespace CodeMine\CommandQueryGenerator\Service;


class DirectoryService
{

    /**
     * @var array
     */
    private $config;
    private $rootPath;

    /**
     * DirectoryService constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config   = $config;
        $this->rootPath = self::getRootPath();
    }

    private static function getRootPath()
    {
        $rootPath = dirname(__FILE__);
        for($i = 0; $i < 20; $i++){
            $rootPath = sprintf('%s%s%s', $rootPath, DIRECTORY_SEPARATOR, '..');
            if(TRUE === is_dir($rootPath.DIRECTORY_SEPARATOR.'public') && TRUE === file_exists($rootPath.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'index.php')){
                break;
            }
        }
        return $rootPath;
    }

    /**
     * @param string $moduleName
     */
    public function getPathForModule($moduleName)
    {
        if (FALSE === in_array($moduleName, $this->config['modules'])) {
            throw new \InvalidArgumentException('Module not found');
        }


        foreach ($this->config['module_listener_options']['module_paths'] as $modulePath) {
            $possiblePath = sprintf('%s%s%s', $modulePath, DIRECTORY_SEPARATOR, $moduleName);
            if (TRUE === is_dir($possiblePath)) {
                return sprintf('%s%s%s%s%s%s%s', $this->rootPath, DIRECTORY_SEPARATOR,
                    $possiblePath, DIRECTORY_SEPARATOR, 'src', DIRECTORY_SEPARATOR, $moduleName);
            }
        }


    }


    public function getPathForAutoloadConfigs()
    {
        if (is_dir($this->rootPath . DIRECTORY_SEPARATOR . 'config') &&
            $this->rootPath . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'autoload'
        ) {
            return $this->rootPath . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'autoload';
        }
    }


    /**
     * @param $objectPath
     */
    public function makeSureDirExist($objectPath)
    {
        if (FALSE === is_dir($objectPath)) {
            mkdir($objectPath);
        }
    }

    /**
     * @param $name
     * @param $objectPath
     */
    public function createAllNamespacedDir($name, $objectPath)
    {
        $pathComponents = explode('/', trim($name, '/'));

        for ($i = 0; $i < count($pathComponents) - 1; $i++) {
            $objectPath = sprintf('%s%s%s', $objectPath, DIRECTORY_SEPARATOR, $pathComponents[$i]);
            @mkdir($objectPath);
            if (FALSE === is_dir($objectPath)) {
                throw new \RuntimeException('Could not create directory for command');
            }
        }
    }

}