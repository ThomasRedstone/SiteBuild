<?php

/**
 * This project aims to build a static website by reading in a range of files
 * such as menus and content written in either HTML or Markdown,
 * 
 */

error_reporting(-1);
define('APPLICATION_PATH', dirname(__FILE__));
/**
 * Including '.APPLICATION_PATH.'library/Initialise.php,
 * and running it's init() function to setup the application
 */
include APPLICATION_PATH.'/library/Initialise.php';
Initialise::init();


$theme = new Theme();

/**
 * Setting some variables to allow for easily read paths later on.
 */
$directory = APPLICATION_PATH.'/content/pages/';
$outputsuffix = '/content/output/'.date("Y-m-d").'/';
$outputdirectory = APPLICATION_PATH.$outputsuffix;

/**
 * This if will test for the existance of the $outputdirectory, if it
 * does not exist it will create it with 077 permissions, but if that fails
 * it will fall to the else statement, and display an error.
 */
if(file_exists($outputdirectory) || mkdir($outputdirectory,0777)) {
    /**
     * A quick check and fix to make sure that $outputdirectory can be written,
     * and hopefully a fix to make it writable if the chmod fails, an error
     * message will be shown.
     */
    if(is_writable($outputdirectory) || chmod($outputdirectory, 0777)) {
        $files = glob("{$directory}*.*");
        print_r($files);
        foreach($files as $file) {
            $content = $theme->buildPage($file);
            $outputfilename = pathinfo(basename($file), PATHINFO_FILENAME).'.html';
            $outputfile = "{$outputdirectory}".$outputfilename;
            $f = fopen($outputfile, "w");
            echo "<h1>Output for $file</h1><a href='/sitebuild{$outputsuffix}{$outputfilename}'>".basename($file)."</a>";
            fwrite($f, $content);
            fclose($f);
            unset($f);
        }
        Utilities::copy(APPLICATION_PATH.'/content/resources/', $outputdirectory);
    }
    else {
        echo '<h1>Making the outpud directory writable failed, check the parent folder\'s permissions</h1>';
    }
}
else {
    echo '<h1>Creating output directory failed, check the file permissions</h1>';
}