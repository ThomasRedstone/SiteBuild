<?php
namespace RedstoneTechnology\SiteBuild\Utilities;

class File {
    public static function copy($source, $destination) {
        if(is_dir($source)) {
            $directory = opendir($source);
            if(!is_dir($destination)) {
                mkdir($destination);
            }
            while(FALSE !== ($file = readdir($directory))) {
                if(!in_array($file, array('.','..'))) {
                    $filepath = "$source/$file";
                    $filedestination = "$destination/$file";
                    if(is_dir($filepath)) {
                        self::copy($filepath,$filedestination);
                    }
                    else {
                        copy($filepath, $filedestination);
                    }
                }
            }
            closedir($directory);
        }
    }
}