<?php
namespace RedstoneTechnology\SiteBuild\Utilities;

use Symfony\Component\Yaml\Parser;

/**
 * Class Theme
 * The Theme class stitches together all of the parts of a page.
 * @package RedstoneTechnology\SiteBuild\Utilities
 */
class Theme {
    protected $yaml;
    protected $config;
    protected $content;
    protected $header;
    protected $page;
    protected $text;
    protected $menus = array();
    protected $template;
    
    public function __construct(\League\Plates\Engine $template, $config = array())
    {
        $this->template = $template;
        $this->config = $config;
        $this->yaml = new Parser();
    }

    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    public function addFolder($name, $folder)
    {
        $this->template->addFolder($name, $folder);
    }
    
    public function buildPage($filePath) {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        switch($extension) {
            case 'php':
                $this->page = $this->template->render(
                    preg_replace(
                        '~/~', '::', str_replace(
                            '.php', '', str_replace(
                                SITE_PATH.'/', '', $filePath
                            )
                        ), 1
                    )
                );
                return $this->page;
                break;
            case 'html':
            case 'htm':
            $file = file_get_contents($filePath);
            $this->processSource($file);
            $this->content['header'] = $this->buildHead($this->header);
            $this->content['content'] = $this->text;
                break;
            case 'md':
                $this->content['content'] = \Parsedown::defaultTransform($this->text);
                break;
            default:
                echo "Extension '$extension' is not recognised\n";
                break;
        }
        return $this->page;
    }
    
    private function processSource($file) {
        #$this->header = $header;
        #{{tags:User Guides}}
        #{{date:2014-07-21}}
        #{{title:Browser Cache}}
        #{{template:business}}
        $header['title']    = $this->getFirstMatch('~{{title:(.*)}}~', $file);
        $header['date']     = $this->getFirstMatch('~{{date:(.*)}}~', $file);
        $header['tags']     = $this->getFirstMatch('~{{tags:(.*)}}~', $file);
        $header['template'] = $this->getFirstMatch('~{{template:(.*)}}~', $file);
        $this->header = $header;
        $this->text = $file;
    }

    protected function getFirstMatch($rule, $data)
    {
        $result = preg_match($rule, $data, $matches);
        if($result === 1) {
            return $matches[1];
        }
        return false;
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
                case 'template':
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
        $content = file_get_contents(SITE_PATH."/themes/$template.html");
        foreach ($this->content as $id => $pageElement) {
            $pattern = "~({{{$id}}})~";
            $content = preg_replace($pattern, $pageElement, $content);
        }
        $this->page = $content;
    }
    
    public function buildMenu($menuName) {
        $menu = $this->yaml->parse(file_get_contents(SITE_PATH."/menus/$menuName.yaml"));
        if(!empty($menu)) {
            $this->content['menu'] = $menu;
        } else {
            throw new \Exception("Menu {$menuName} is empty or file does not exist at path ".
                SITE_PATH."/menus/$menuName.yaml");
        }
        $this->template->addData(['menu' => $menu]);
    }
}