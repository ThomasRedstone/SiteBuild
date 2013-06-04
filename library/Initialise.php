<?php

/**
 * The Initialise class does everything to setup up the application, before
 * it starts to do its job.
 */



class Initialise {
    /**
     * autoload is a PSR-0-compatible class autoloader.
     * @param string $class
     */
    public static function autoload($class){
        //echo '<h1>Trying to do an autoload of '.APPLICATION_PATH."/library/$class.php".'</h1>';
        require APPLICATION_PATH."/library/".preg_replace('{\\\\|_(?!.*\\\\)}', DIRECTORY_SEPARATOR, ltrim($class, '\\')).'.php';
    }
    
    public static function init() {
        echo '<h1>Registering autoload</h1>';
        spl_autoload_register( array('Initialise', 'autoload') );
        
    }
}