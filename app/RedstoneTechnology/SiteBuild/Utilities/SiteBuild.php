<?php

namespace RedstoneTechnology\SiteBuild\Utilities;

use Symfony\Component\Yaml\Parser;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Helper\QuestionHelper;

class SiteBuild
{
    protected $script = '';
    protected $config = [];
    protected $name;
    protected $outputSuffix;
    protected $outputDirectory;

    public function __construct($theme)
    {
        $this->theme = $theme;
    }

    public function build($name, $input, $output)
    {
        $yaml = new Parser();
        $this->name = $name;

        $appConfigPath = $this->getConfigPath('app');
        if (!is_file($appConfigPath)) {
            throw new \Exception("The application config file at \"{$appConfigPath}\" does not exist");
        }
        $yaml = new Parser();
        $appConfig = $yaml->parse(file_get_contents($appConfigPath));
        if (!is_dir($name)) {
            throw new \Exception("A directory with \"{$name}\" must exists, with a config.yml file.");
        }
        chdir($name);

        /**
         * Setting some variables to allow for easily read paths later on.
         */
        define('SITE_PATH', getcwd());
        $this->theme->addFolder('templates', SITE_PATH.'/content/themes/');
        $this->theme->addFolder('pages', SITE_PATH.'/content/pages/');
        $directory = SITE_PATH.'/content/pages/';
        $this->outputSuffix = '/content/output/'.date("Y-m-d").'/';
        $this->outputDirectory = SITE_PATH.$this->outputSuffix;

        /**
         * This if will test for the existance of the $outputDirectory, if it
         * does not exist it will create it with 077 permissions, but if that fails
         * it will fall to the else statement, and display an error.
         */
        if (file_exists($this->outputDirectory) || mkdir($this->outputDirectory, 0775, true)) {
            /**
             * A quick check and fix to make sure that $outputDirectory can be written,
             * and hopefully a fix to make it writable if the chmod fails, an error
             * message will be shown.
             */
            if (is_writable($this->outputDirectory) || chmod($this->outputDirectory, 0775)) {
                $this->processFiles($directory, '');
                File::copy(SITE_PATH.'/content/resources/', $this->outputDirectory);
            }
            else {
                echo "Making the output directory writable failed, check the parent folder\'s permissions\n";
            }
        }
        else {
            echo "Creating output directory failed, check the file permissions\n";
        }
    }

    protected function processFiles($directory, $targetDirectory)
    {
        #echo "Directory is {$directory}\n";
        $files = glob("{$directory}*");
        #print_r($files);
        $this->theme->buildMenu('main');
        foreach($files as $file) {
            if($file === $directory) {
                continue;
            }
            if(is_dir($file)) {
                #echo "File {$file} is a directory\n";
                $directoryPath = str_replace(SITE_PATH.'/content/pages/','',$file)."/";
                #echo "Target directory is {$directoryPath}\n";
                $this->processFiles("{$file}/", $directoryPath);
                continue;
            }
            $content = $this->theme->buildPage($file);
            $outputFilename = pathinfo(basename($file), PATHINFO_FILENAME);
            $outputFile = "{$this->outputDirectory}{$targetDirectory}{$outputFilename}".
                ($outputFilename === 'index' ? '' : '/index').'.html';
            #echo "outputFile: $outputFile\n";
            if(!is_dir(dirname($outputFile))) {
                mkdir(dirname($outputFile), 0755, true);
            }
            $f = fopen($outputFile, "w");
            #echo "<h1>Output for $file</h1><a href='/sitebuild{$this->outputSuffix}{$outputFilename}'>".basename($file)."</a>";
            fwrite($f, $content);
            fclose($f);
            unset($f);
        }
    }

    protected function getConfigPath($config)
    {
        $fileInfo = pathinfo($config);
        if (empty($fileInfo['extension'])) {
            $config .= '.yml';
        }
        if($config === 'app.yml' && is_file(APP_PATH."/../{$this->name}/{$config}")) {
            return APP_PATH."/../{$this->name}/{$config}";
        }
        if (is_file($config)) {
            return $config;
        }
        if (is_file(APP_PATH . "/{$config}")) {
            return APP_PATH . "/{$config}";
        }
        echo "Can't seem to find path for {$config}\n";
        return realpath("./{$config}") . "\n";
    }
}

