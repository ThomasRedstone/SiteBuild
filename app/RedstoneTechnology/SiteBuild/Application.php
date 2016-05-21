<?php
/**
 * Created by PhpStorm.
 * User: thomasredstone
 * Date: 11/12/2015
 * Time: 21:17
 */

namespace RedstoneTechnology\SiteBuild;

use RedstoneTechnology\SiteBuild\Commands\SiteBuild;
use RedstoneTechnology\SiteBuild\Utilities;
use \League\Plates\Engine;

/**
 * Class Application
 * @package RedstoneTechnology\SiteBuild
 */
class Application extends \Symfony\Component\Console\Application
{
    /**
     * Gets the name of the command based on input.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input The input interface
     *
     * @return string The command name
     */
    protected function getCommandName(\Symfony\Component\Console\Input\InputInterface $input)
    {
        // This should return the name of your command.
        return 'SiteBuild';
    }

    /**
     * Gets the default commands that should always be available.
     *
     * @return array An array of default Command instances
     */
    protected function getDefaultCommands()
    {
        // Keep the core default commands to have the HelpCommand
        // which is used when using the --help option
        $defaultCommands = parent::getDefaultCommands();

        $templates = new Engine();
        $theme = new Utilities\Theme($templates);
        $file = new Utilities\File();
        $defaultCommands[] = new Commands\SiteBuild(
            new Utilities\SiteBuild($theme, $file)
        );

        return $defaultCommands;
    }

    /**
     * Overridden so that the application doesn't expect the command
     * name to be the first argument.
     */
    public function getDefinition()
    {
        $inputDefinition = parent::getDefinition();
        // clear out the normal first argument, which is the command name
        $inputDefinition->setArguments();

        return $inputDefinition;
    }
}