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
                        $this->copy($filePath, $fileDestination);
                    }
                    else {
                        copy($filePath, $fileDestination);
                    }
                }
            }
            closedir($directory);
        }
    }

    /**
     * Gets the current working directory.
     * @return string
     */
    public function getCurrentDirectory()
    {
        return getcwd();
    }

    /**
     * Recursively deletes a directory, and all its contents.
     * @param $directory
     * @return bool
     */
    public function deleteDirectory($directory)
    {
        $files = array_diff(scandir($directory), array('.', '..'));
        foreach ($files as $file) {
            if(is_dir("{$directory}/{$file}")) {
                $this->deleteDirectory("{$directory}/{$file}");
            } else {
                unlink("{$directory}/{$file}");
            }
        }
        return rmdir($directory);
    }

    /**
     * @param $file
     * @return bool
     */
    public function exists($file)
    {
        return file_exists($file);
    }
}