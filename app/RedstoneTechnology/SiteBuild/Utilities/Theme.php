<?php
namespace RedstoneTechnology\SiteBuild\Utilities;


/**
 * The Theme class stitches together all of the parts of a page.
 */


use Symfony\Component\Yaml\Parser;
use Michelf\Markdown;

class Theme {
    protected $yaml;
    protected $config;
    protected $content;
    protected $header;
    protected $page;
    protected $text;
    protected $menus = array();
    
    public function __construct($config = array()) {
        $this->config = $config;
        $this->yaml = new Parser();
    }
    
    public function buildPage($filePath) {
        $this->buildMenu('main');
        $file = file_get_contents($filePath);
        $this->processSource($file);
        $this->content['header'] = $this->buildHead($this->header);
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        switch($extension) {
            case 'html':
            case 'htm':
                $this->content['content'] = $this->text;
                break;
            case 'md':
                $this->content['content'] = Markdown::defaultTransform($this->text);
                break;
            default:
                echo "Extension '$extension' is not recognised\n";
                break;
        }
        $this->populateTemplate('template');
        return $this->page;
    }
    
    private function processSource($file) {
        #$this->header = $header;
        #{{tags:User Guides}}
        #{{date:2014-07-21}}
        #{{title:Browser Cache}}
        $header['title'] = preg_match('~{{title:(.*)}}~');
        $header['date'] = preg_match('~{{date:(.*)}}~');
        $header['tags'] = preg_match('~{{tags:(.*)}}~');
        $this->header = $header;
        $this->text = $file;
        
    }
    
    private function buildHead($header) {
        $content = '';
        foreach($header as $field => $metadata) {
            switch($field) {
                case 'title':
                    $content .= "<title>$metadata</title>\n";
                    break;
                case 'tags':
                case 'date':
                    break;
                default:
                    $content .= "<meta name='$field' content='$metadata'>";
                    break;
            }
        }
        return $content;
    }
    private function populateTemplate($template) {
        #die(var_dump($this->content));
        $content = file_get_contents(SITE_PATH."/content/themes/$template.html");
        foreach ($this->content as $id => $pageElement) {
            $pattern = "~({{{$id}}})~";
            echo "pattern: {$pattern}\nid: {$id}\npageElement:{$pageElement}\n'";
            $content = preg_replace($pattern, $pageElement, $content);
        }
        $this->page = $content;
    }
    
    private function buildMenu($menuName) {
        $menu = $this->yaml->parse(file_get_contents(SITE_PATH."/content/menus/$menuName.yml"));
        if(!empty($menu)) {
            $menuText = '';
            foreach ($menu as $name => $url) {
                $menuText .= "<li class='nav-item'><a href='$url'>$name</a></li>";
            }
            $this->content['menu'] = $menuText;
        } else {
            throw new \Exception("Menu {$menuName} is empty or file does not exist at path ".
                SITE_PATH."/content/menus/$menuName.yml");
        }
        
    }
}