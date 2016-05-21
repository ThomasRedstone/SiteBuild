<?php

/**
 * This project aims to build a static website by reading in a range of files
 * such as menus and content written in either HTML or Markdown,
 *
 * Created by PhpStorm.
 * User: thomas
 * Date: 21/03/15
 * Time: 23:22
 */

namespace RedstoneTechnology\SiteBuild\Commands;

use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SiteBuild
 * @package RedstoneTechnology\SiteBuild\Commands
 */
class SiteBuild extends Command
{
    protected $serverBuild;

    /**
     * SiteBuild constructor.
     * @param \RedstoneTechnology\SiteBuild\Utilities\SiteBuild $siteBuild
     */
    public function __construct(\RedstoneTechnology\SiteBuild\Utilities\SiteBuild $siteBuild)
    {
        $this->siteBuild = $siteBuild;
        parent::__construct();
    }

    /**
     * Configures the command.
     */
    protected function configure()
    {
        $this
            ->setName('SiteBuild')
            ->setDescription('Builds a static website using a collection of files')
            ->addOption(
                'name',
                null,
                InputOption::VALUE_REQUIRED,
                'Name website'
            )
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getOption('name');
        $this->siteBuild->build($name, $input, $output);
    }
}
