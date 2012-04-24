<?php
/**
 * Template control class
 *
 * @author Mark
 */
class Template {
    private $twig;
    private $loader;
    private $variables;

    function __construct($templatePath){
        $this->loader = new Twig_Loader_Filesystem($_SERVER["DOCUMENT_ROOT"] . $templatePath);
        $this->twig = new Twig_Environment($this->loader, array(
            'cache' => $_SERVER["DOCUMENT_ROOT"] . $templatePath . '/compiled',
            'auto_reload' => true,
        ));
        $this->variables = array();
    }

    function render($templateFile){
        echo $this->twig->render($templateFile, $this->variables);
    }

    function addVariable($name,$variable){
        $this->variables[$name] = $variable;
    }
}

require_once('Twig/Autoloader.php');
Twig_Autoloader::register();