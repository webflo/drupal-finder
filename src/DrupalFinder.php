<?php

/**
 * @file
 * Contains \DrupalFinder\DrupalFinder.
 */

namespace DrupalFinder;

class DrupalFinder
{
    /**
     * Drupal web public directory.
     *
     * @var string
     */
    private $drupalRoot;

    /**
     * Drupal package composer directory.
     *
     * @var bool
     */
    private $composerRoot;

    /**
     * Drupal root key used when doing env vars check.
     *
     * @var string
     */
    const DRUPAL_ROOT_KEY = 'drupal_root';

    /**
     * Composer root key used when doing env vars check.
     *
     * @var string
     */
    const COMPOSER_ROOT_KEY = 'composer_root';

    /**
     * Vendor dir key used when doing env vars check.
     *
     * @var string
     */
    const VENDOR_DIR_KEY = 'vendor_dir';

    /**
     * Composer vendor directory.
     *
     * @var string
     *
     * @see https://getcomposer.org/doc/06-config.md#vendor-dir
     */
    private $vendorDir;

    public function locateRoot($start_path)
    {
        $this->drupalRoot = false;
        $this->composerRoot = false;
        $this->vendorDir = false;

        // If valid environment variables have been specified which indicate
        // the paths of composer root, drupal root, and vendor directory.
        if ($this->validExplicitPaths()) {
            return true;
        }

        foreach (array(true, false) as $follow_symlinks) {
            $path = $start_path;
            if ($follow_symlinks && is_link($path)) {
                $path = realpath($path);
            }
            // Check the start path.
            if ($this->isValidRoot($path)) {
                return true;
            } else {
                // Move up dir by dir and check each.
                while ($path = $this->shiftPathUp($path)) {
                    if ($follow_symlinks && is_link($path)) {
                        $path = realpath($path);
                    }
                    if ($this->isValidRoot($path)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Returns parent directory.
     *
     * @param string
     *   Path to start from
     *
     * @return string|false
     *   Parent path of given path or false when $path is filesystem root
     */
    private function shiftPathUp($path)
    {
        $parent = dirname($path);

        return in_array($parent, ['.', $path]) ? false : $parent;
    }

    /**
     * @param $path
     *
     * @return bool
     */
    protected function isValidRoot($path)
    {
        if (!empty($path) && is_dir($path) && file_exists($path . '/autoload.php') && file_exists($path . '/' . $this->getComposerFileName())) {
            // Additional check for the presence of core/composer.json to
            // grant it is not a Drupal 7 site with a base folder named "core".
            $candidate = 'core/includes/common.inc';
            if (file_exists($path . '/' . $candidate) && file_exists($path . '/core/core.services.yml')) {
                if (file_exists($path . '/core/misc/drupal.js') || file_exists($path . '/core/assets/js/drupal.js')) {
                    $this->composerRoot = $path;
                    $this->drupalRoot = $path;
                    $this->vendorDir = $this->composerRoot . '/vendor';
                }
            }
        }
        if (!empty($path) && is_dir($path) && file_exists($path . '/' . $this->getComposerFileName())) {
            $json = json_decode(
                file_get_contents($path . '/' . $this->getComposerFileName()),
                true
            );

            if (is_null($json)) {
                throw new \Exception('Unable to decode ' . $path . '/' . $this->getComposerFileName());
            }

            if (is_array($json)) {
                if (isset($json['extra']['installer-paths']) && is_array($json['extra']['installer-paths'])) {
                    foreach ($json['extra']['installer-paths'] as $install_path => $items) {
                        if (in_array('type:drupal-core', $items) ||
                            in_array('drupal/core', $items) ||
                            in_array('drupal/drupal', $items)) {
                            $this->composerRoot = $path;
                            // @todo: Remove this magic and detect the major version instead.
                            if (($install_path == 'core') || ((isset($json['name'])) && ($json['name'] == 'drupal/drupal'))) {
                                $install_path = '';
                            } elseif (substr($install_path, -5) == '/core') {
                                $install_path = substr($install_path, 0, -5);
                            }
                            $this->drupalRoot = rtrim($path . '/' . $install_path, '/');
                            $this->vendorDir = $this->composerRoot . '/vendor';
                        }
                    }
                }
            }
        }
        if ($this->composerRoot && file_exists($this->composerRoot . '/' . $this->getComposerFileName())) {
            $json = json_decode(
                file_get_contents($path . '/' . $this->getComposerFileName()),
                true
            );
            if (is_array($json) && isset($json['config']['vendor-dir'])) {
                $this->vendorDir = $this->composerRoot . '/' . $json['config']['vendor-dir'];
            }
        }

        return $this->drupalRoot && $this->composerRoot && $this->vendorDir;
    }

    /**
     * @return string
     */
    public function getDrupalRoot()
    {
        return $this->drupalRoot;
    }

    /**
     * @return string
     */
    public function getComposerRoot()
    {
        return $this->composerRoot;
    }

    /**
     * @return string
     */
    protected function getComposerFileName()
    {
        return trim(getenv('COMPOSER')) ?: 'composer.json';
    }

    /**
     * @return string
     */
    public function getVendorDir()
    {
        return $this->vendorDir;
    }

    /**
     * Try to extract valid paths from environment variables.
     *
     * @return bool
     *   True if valid fallback environment variables were specified. False otherwise.
     */
    protected function validExplicitPaths() {
        $env_vars = [
            self::COMPOSER_ROOT_KEY => 'DRUPAL_FINDER_COMPOSER_ROOT',
            self::DRUPAL_ROOT_KEY => 'DRUPAL_FINDER_DRUPAL_ROOT',
            self::VENDOR_DIR_KEY => 'DRUPAL_FINDER_VENDOR_DIR',
        ];

        $paths = [];

        // First, validate that all of the expected environment variables exist,
        // and that they each point to a valid directory. If even one is not
        // set, then consider the environment variable fallback to be invalid.
        foreach ($env_vars as $path_key => $path_var) {
            $path = getenv($path_var);
            if (!is_string($path) || !is_dir($path)) {
                return false;
            }
            $paths[$path_key] = $path;
        }

        // Set directory properties.
        $this->composerRoot = $paths[self::COMPOSER_ROOT_KEY];
        $this->drupalRoot = $paths[self::DRUPAL_ROOT_KEY];
        $this->vendorDir = $paths[self::VENDOR_DIR_KEY];

        return true;
    }
}
