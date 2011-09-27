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
 * Loads templates from the filesystem.
 *
 * @package    twig
 * @author     Fabien Potencier <fabien@symfony.com>
 */
class Twig_Loader_Filesystem implements Twig_LoaderInterface
{
    protected $paths;
    protected $cache;

    /**
     * Constructor.
     *
     * @param string|array $paths A path or an array of paths where to look for templates
     */
    public function __construct($paths)
    {
        $this->setPaths($paths);
    }

    /**
     * Returns the paths to the templates.
     *
     * @return array The array of paths where to look for templates
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * Sets the paths where templates are stored.
     *
     * @param string|array $paths A path or an array of paths where to look for templates
     */
    public function setPaths($paths)
    {
        if (!is_array($paths)) {
            $paths = array($paths);
        }

        $this->paths = array();
        foreach ($paths as $path) {
            $this->addPath($path);
        }
    }

    /**
     * Adds a path where templates are stored.
     *
     * @param string $path A path where to look for templates
     */
    public function addPath($path)
    {
        // invalidate the cache
        $this->cache = array();

        if (!is_dir($path)) {
            throw new Twig_Error_Loader(sprintf('The "%s" directory does not exist.', $path));
        }

        $this->paths[] = $path;
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
        return file_get_contents($this->findTemplate($name));
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
        return $this->findTemplate($name);
    }

    /**
     * Returns true if the templates is still fresh.
     *
     * @param string    $name The templates name
     * @param timestamp $time The last modification time of the cached templates
     */
    public function isFresh($name, $time)
    {
        return filemtime($this->findTemplate($name)) < $time;
    }

    protected function findTemplate($name)
    {
        // normalize name
        $name = preg_replace('#/{2,}#', '/', strtr($name, '\\', '/'));

        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        $this->validateName($name);

        foreach ($this->paths as $path) {
            if (is_file($path.'/'.$name)) {
                return $this->cache[$name] = $path.'/'.$name;
            }
        }

        throw new Twig_Error_Loader(sprintf('Unable to find templates "%s" (looked into: %s).', $name, implode(', ', $this->paths)));
    }

    protected function validateName($name)
    {
        if (false !== strpos($name, "\0")) {
            throw new Twig_Error_Loader('A templates name cannot contain NUL bytes.');
        }

        $parts = explode('/', $name);
        $level = 0;
        foreach ($parts as $part) {
            if ('..' === $part) {
                --$level;
            } elseif ('.' !== $part) {
                ++$level;
            }

            if ($level < 0) {
                throw new Twig_Error_Loader(sprintf('Looks like you try to load a templates outside configured directories (%s).', $name));
            }
        }
    }
}
