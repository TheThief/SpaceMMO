<?php

/*
 * This file is part of Twig.
 *
 * (c) 2009 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Loads a templates from an array.
 *
 * When using this loader with a cache mechanism, you should know that a new cache
 * key is generated each time a templates content "changes" (the cache key being the
 * source code of the templates). If you don't want to see your cache grows out of
 * control, you need to take care of clearing the old cache file by yourself.
 *
 * @package    twig
 * @author     Fabien Potencier <fabien@symfony.com>
 */
class Twig_Loader_Array implements Twig_LoaderInterface
{
    protected $templates;

    /**
     * Constructor.
     *
     * @param array $templates An array of templates (keys are the names, and values are the source code)
     *
     * @see Twig_Loader
     */
    public function __construct(array $templates)
    {
        $this->templates = array();
        foreach ($templates as $name => $template) {
            $this->templates[$name] = $template;
        }
    }

    /**
     * Adds or overrides a templates.
     *
     * @param string $name     The templates name
     * @param string $template The templates source
     */
    public function setTemplate($name, $template)
    {
        $this->templates[$name] = $template;
    }

    /**
     * Gets the source code of a templates, given its name.
     *
     * @param  string $name The name of the templates to load
     *
     * @return string The templates source code
     */
    public function getSource($name)
    {
        if (!isset($this->templates[$name])) {
            throw new Twig_Error_Loader(sprintf('Template "%s" is not defined.', $name));
        }

        return $this->templates[$name];
    }

    /**
     * Gets the cache key to use for the cache for a given templates name.
     *
     * @param  string $name The name of the templates to load
     *
     * @return string The cache key
     */
    public function getCacheKey($name)
    {
        if (!isset($this->templates[$name])) {
            throw new Twig_Error_Loader(sprintf('Template "%s" is not defined.', $name));
        }

        return $this->templates[$name];
    }

    /**
     * Returns true if the templates is still fresh.
     *
     * @param string    $name The templates name
     * @param timestamp $time The last modification time of the cached templates
     */
    public function isFresh($name, $time)
    {
        return true;
    }
}
