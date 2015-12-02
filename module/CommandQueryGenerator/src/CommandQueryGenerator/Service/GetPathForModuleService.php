<?php
/**
 * @author Radek Adamiec <radek@code-mine.com>.
 */

namespace CommandQueryGenerator\Service;


class GetPathForModuleService
{

    /**
     * @var array
     */
    private $config;
    private $rootPath;

    /**
     * GetPathForModuleService constructor.
     *
     * @param array $config
     * @param       $rootPath
     */
    public function __construct(array $config, $rootPath)
    {
        $this->config   = $config;
        $this->rootPath = $rootPath;
    }

    /**
     * @param string $moduleName
     */
    public function getPath($moduleName)
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

}