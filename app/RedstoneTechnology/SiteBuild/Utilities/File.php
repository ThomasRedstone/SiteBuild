<?php
namespace RedstoneTechnology\SiteBuild\Utilities;

/**
 * A collection of functions to work with files.
 * Class File
 * @package RedstoneTechnology\SiteBuild\Utilities
 */
class File
{
    /**
     * Copies one directory to another.
     * @param $source
     * @param $destination
     */
    public function copy($source, $destination)
    {
        if (is_dir($source)) {
            $directory = opendir($source);
            if (!is_dir($destination)) {
                mkdir($destination);
            }
            while (false !== ($file = readdir($directory))) {
                if (!in_array($file, array('.', '..'))) {
                    $filePath = "$source/$file";
                    $fileDestination = "$destination/$file";
                    if (is_dir($filePath)) {
                        self::copy($filePath, $fileDestination);
                    }
                    else {
                        copy($filePath, $fileDestination);
                    }
                }
            }
            closedir($directory);
        }
    }
}