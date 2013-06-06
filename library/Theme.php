<?php

/**
 * The Theme class stiches together all of the parts of a page.
 */

use \Michelf\Markdown;
class Theme {
    private $config;
    private $content;
    private $header;
    private $page;
    
    public function __construct($config = NULL) {
        # Get Markdown class

        if($config == NULL) {
            $config = array(
                
            );
        }
    }
    
    public function buildPage($filepath) {
        $file = file_get_contents($filepath);
        $this->processSource($file);
        $this->content['header'] = $this->buildHead($this->header);
        $extension = pathinfo($filepath,PATHINFO_EXTENSION);
        if(in_array($extension, array('html','htm'))) {
            $this->content['body'] = $this->text;
        }
        else if(in_array($extension, array('md'))) {
            $this->content['body'] = Markdown::defaultTransform($this->text);
        }
        else {
            echo "<h2>$extension is not recognised</h2>";
        }
        $this->buildMenu('main');
        $this->populateTemplate('template');
        return $this->page;
    }
    
    private function processSource($file) {
        preg_match('~^(.*)\r\n\r\n\r\n(.*)$~s', $file, $matches);
        #echo '<pre>'.print_r($matches,1).'</pre>';
        $headerTemp = explode("\r\n",$matches[1]);
        $text = $matches[2];
        foreach ($headerTemp as $metafield) {
            preg_match('~(.*?):(.*)~',$metafield,$fields);
            $header[$fields[1]] = $fields[2];
        }
        $this->header = $header;
        $this->text = $text;
        
    }
    
    private function buildHead($header) {
        $content = '';
        foreach($header as $field => $metadata) {
            if($field == 'title') {
                $content .= "<title>$metadata</title>";
            }
            else {
                $content .= "<meta name='$field' content='$metadata'>";
            }
        }
        return $content;
    }
    private function populateTemplate($template) {
        $content = file_get_contents(APPLICATION_PATH."/content/themes/$template.html");
        foreach ($this->content as $id => $pageElement) {
            $patern = "~({!{$id}})~";
            echo "<h1>patern: $patern</h1><h1>id: $id</h1><h2>pageElement:</h2>$pageElement";
            $content = preg_replace($patern, $pageElement, $content);
        }
        
        $this->page = $content;
    }
    
    private function buildMenu($menuname) {
        $content = file_get_contents(APPLICATION_PATH."/content/menus/$menuname.txt");
        $menuTemp = explode("\r\n",$content);
        echo '<pre>'.print_r($menuTemp,1).'</pre>';
        foreach ($menuTemp as $menuitem) {
            preg_match('~(.*?):(.*)~',$menuitem,$item);
            echo '<pre>'.print_r($item,1).'</pre>';
            $id = $item[2];
            $menu[$id] = $item[1];
        }
        echo '<pre>'.print_r($menu,1).'</pre>';
        $menutext = '';
        foreach($menu as $name => $url) {
            $menutext .= "<li><a href='$url'>$name</a></li>";
        }
        $this->content['menu'] = $menutext;
        
    }
}