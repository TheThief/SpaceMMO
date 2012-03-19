<?php
/**
 * Template control class
 *
 * @author Mark
 */
class Template {
    private $twig;
    private $variables;

    function __construct($templatePath){

    }

}

require_once('Twig/Autoloader.php');
Twig_Autoloader::register();