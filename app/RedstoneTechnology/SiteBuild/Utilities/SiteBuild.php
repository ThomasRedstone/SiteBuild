<?php

namespace RedstoneTechnology\SiteBuild\Utilities;

use Symfony\Component\Yaml\Parser;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Helper\QuestionHelper;

/**
 * Class SiteBuild
 * @package RedstoneTechnology\SiteBuild\Utilities
 */
class SiteBuild
{
    protected $script = '';
    protected $config = [];
    protected $name;
    protected $outputSuffix;
    protected $outputDirectory;
    protected $currentDirectory;

    /**
     * SiteBuild constructor.
     * @param Theme $theme
     * @param File $file
     */
    public function __construct(Theme $theme, File $file)
    {
        $this->theme = $theme;
        $this->file = $file;
        $this->currentDirectory = $this->file->getCurrentDirectory();
    }

    /**
     * Performs the task of building a static website.
     * @param $name
     * @param $input
     * @param $output
     * @throws \Exception
     */
    public function build($name, $input, $output)
    {
        $yaml = new Parser();
        $this->name = $name;
        if(is_null($name) === true && is_file("{$this->currentDirectory}/app.yml") === true) {
            $this->name = basename($this->currentDirectory);
            $this->currentDirectory = dirname($this->currentDirectory);
        } elseif (!is_dir($name)) {
            throw new \Exception("A directory with \"{$name}\" must exists, with a config.yaml file.");
        }

        $appConfigPath = $this->getConfigPath('app');
        if (!is_file($appConfigPath)) {
            throw new \Exception("The application config file at \"{$appConfigPath}\" does not exist");
        }
        $yaml = new Parser();
        $appConfig = $yaml->parse(file_get_contents($appConfigPath));
        chdir("{$this->currentDirectory}/{$this->name}");

        /**
         * Setting some variables to allow for easily read paths later on.
         */
        define('SITE_PATH', getcwd());
        $this->theme->addFolder('templates', SITE_PATH.'/themes/');
        $this->theme->addFolder('pages', SITE_PATH.'/pages/');
        $directory = SITE_PATH.'/pages/';
        $this->outputSuffix = '/output/'.date("Y-m-d_h.i").'/';
        $this->outputDirectory = SITE_PATH.$this->outputSuffix;

        /**
         * This if will test for the existance of the $outputDirectory, if it
         * does not exist it will create it with 077 permissions, but if that fails
         * it will fall to the else statement, and display an error.
         */
        if ($this->file->exists($this->outputDirectory) || mkdir($this->outputDirectory, 0775, true)) {
            /**
             * A quick check and fix to make sure that $outputDirectory can be written,
             * and hopefully a fix to make it writable if the chmod fails, an error
             * message will be shown.
             */
            if (is_writable($this->outputDirectory) || chmod($this->outputDirectory, 0775)) {
                $this->processFiles($directory, '');
                $this->file->copy(SITE_PATH.'/resources/', $this->outputDirectory);
                if($this->file->exists(SITE_PATH.'/output/latest')) {
                    $this->file->deleteDirectory(SITE_PATH.'/output/latest');
                }
                $this->file->copy($this->outputDirectory, SITE_PATH.'/output/latest');
            }
            else {
                echo "Making the output directory writable failed, check the parent folder\'s permissions\n";
            }
        }
        else {
            echo "Creating output directory failed, check the file permissions\n";
        }
    }

    /**
     * Loops through a given directory recursively, building template files and saving the results into the target
     * directory.
     * @param $directory
     * @param $targetDirectory
     * @throws \Exception
     */
    protected function processFiles($directory, $targetDirectory)
    {
        $files = glob("{$directory}*");
        $this->theme->buildMenu('main');
        foreach($files as $file) {
            if($file === $directory) {
                continue;
            }
            if(is_dir($file)) {
                #echo "File {$file} is a directory\n";
                $directoryPath = str_replace(SITE_PATH.'/pages/','',$file)."/";
                #echo "Target directory is {$directoryPath}\n";
                $this->processFiles("{$file}/", $directoryPath);
                continue;
            }
            $content = $this->theme->buildPage($file);
            $outputFilename = pathinfo(basename($file), PATHINFO_FILENAME);
            $outputFile = "{$this->outputDirectory}{$targetDirectory}{$outputFilename}".
                ($outputFilename === 'index' ? '' : '/index').'.html';
            if(!is_dir(dirname($outputFile))) {
                mkdir(dirname($outputFile), 0755, true);
            }
            $f = fopen($outputFile, "w");
            fwrite($f, $content);
            fclose($f);
            unset($f);
        }
    }

    /**
     * Checks a few possible locations of the config file, and returns the first one it finds, throwing an exception
     * if a config file cannot be found.
     * @param $config
     * @return string
     * @throws \Exception
     */
    protected function getConfigPath($config)
    {
        $fileInfo = pathinfo($config);
        if (empty($fileInfo['extension'])) {
            $config .= '.yml';
        }
        if($config === 'app.yaml' && is_file(APP_PATH."/../{$this->name}/{$config}")) {
            return APP_PATH."/../{$this->name}/{$config}";
        }
        if(is_file("{$this->currentDirectory}/{$this->name}/{$config}")) {
            return "{$this->currentDirectory}/{$this->name}/{$config}";
        }
        if (is_file($config)) {
            return $config;
        }
        if (is_file(APP_PATH . "/{$config}")) {
            return APP_PATH . "/{$config}";
        }
        throw new \Exception("Can't seem to find path for {$config}");
    }
}

