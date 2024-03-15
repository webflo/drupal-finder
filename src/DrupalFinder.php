<?php

/**
 * @file
 * Contains \DrupalFinder\DrupalFinder.
 */

namespace DrupalFinder;

/**
 * @deprecated in drupal-finder:1.3.0 and is removed from drupal-finder:2.0.0.
 *   Use \DrupalFinder\DrupalFinderComposerRuntime instead.
 */
class DrupalFinder
{
    /**
     * Drupal root environment variable.
     */
    const ENV_DRUPAL_ROOT = 'DRUPAL_FINDER_DRUPAL_ROOT';

    /**
     * Composer root environment variable.
     */
    const ENV_COMPOSER_ROOT = 'DRUPAL_FINDER_COMPOSER_ROOT';

    /**
     * Vendor directory environment variable.
     */
    const ENV_VENDOR_DIR = 'DRUPAL_FINDER_VENDOR_DIR';

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
     * Composer vendor directory.
     *
     * @var string
     *
     * @see https://getcomposer.org/doc/06-config.md#vendor-dir
     */
    private $vendorDir;

    /**
     * Initialize finder.
     *
     * Optionally pass the starting path.
     *
     * @param string|null $start_path
     *   The path to begin the search from.
     *
     * @throws \Exception
     * @todo Make $start_path mandatory in v2.
     */
    public function __construct($start_path = null) {
        // Initialize path variables to false, indicating their locations are
        // not yet known.
        $this->drupalRoot = false;
        $this->composerRoot = false;
        $this->vendorDir = false;

        // If a starting path was provided, attempt to locate and set path
        // variables.
        if (!empty($start_path)) {
            $this->discoverRoots($start_path);
        }
    }

    /**
     * Locate Drupal, Composer, and vendor directory paths.
     *
     * @param string $start_path
     *   The path to begin the search from.
     *
     * @return bool
     *   True if the Drupal root was identified, false otherwise.
     *
     * @throws \Exception
     *
     * @deprecated Will be removed in v2. Future usage should instantiate
     *   a new DrupalFinder object by passing the starting path to its
     *   constructor.
     */
    public function locateRoot($start_path)
    {
        $this->discoverRoots($start_path);
        return !empty($this->getDrupalRoot());
    }

    /**
     * Get the Drupal root.
     *
     * @return string|bool
     *   The path to the Drupal root, if it was discovered. False otherwise.
     */
    public function getDrupalRoot()
    {
        $environment_path = $this->getValidEnvironmentVariablePath(self::ENV_DRUPAL_ROOT);

        return !empty($environment_path) ? $environment_path : $this->drupalRoot;
    }

    /**
     * Get the Composer root.
     *
     * @return string|bool
     *   The path to the Composer root, if it was discovered. False otherwise.
     */
    public function getComposerRoot()
    {
        $environment_path = $this->getValidEnvironmentVariablePath(self::ENV_COMPOSER_ROOT);
        return !empty($environment_path) ? $environment_path : $this->composerRoot;
    }

    /**
     * Get the vendor path.
     *
     * @return string|bool
     *   The path to the vendor directory, if it was found. False otherwise.
     */
    public function getVendorDir()
    {
        $environment_path = $this->getValidEnvironmentVariablePath(self::ENV_VENDOR_DIR);
        return !empty($environment_path) ? $environment_path : $this->vendorDir;
    }

    /**
     * Discover all valid paths.
     *
     * @param $start_path
     *   The path to start the search from.
     *
     * @throws \Exception
     */
    protected function discoverRoots($start_path) {
        // Since we are discovering, reset all path variables.
        $this->drupalRoot = false;
        $this->composerRoot = false;
        $this->vendorDir = false;

        foreach (array(true, false) as $follow_symlinks) {
            $path = $start_path;
            if ($follow_symlinks && is_link($path)) {
                $path = realpath($path);
            }

            // Check the start path.
            if ($this->findAndValidateRoots($path)) {
                return;
            } else {
                // Move up dir by dir and check each.
                while ($path = $this->shiftPathUp($path)) {
                    if ($follow_symlinks && is_link($path)) {
                        $path = realpath($path);
                    }
                    if ($this->findAndValidateRoots($path)) {
                        return;
                    }
                }
            }
        }
    }

    /**
     * Determine if a valid Drupal root exists.
     *
     * In addition, set any valid path properties if they are found.
     *
     * @param $path
     *   The starting path to search from.
     *
     * @return bool
     *   True if all roots were discovered and validated. False otherwise.
     */
    protected function findAndValidateRoots($path)
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

        return $this->allPathsDiscovered();
    }

    /**
     * @return string
     */
    protected function getComposerFileName()
    {
        return trim(getenv('COMPOSER')) ?: 'composer.json';
    }

    /**
     * Helper function to quickly determine whether or not all paths were discovered.
     *
     * @return bool
     *   True if all paths have been discovered, false if one or more haven't been found.
     */
    protected function allPathsDiscovered() {
        return !empty($this->drupalRoot) && !empty($this->composerRoot) && !empty($this->vendorDir);
    }

    /**
     * Helper function to quickly determine whether or not all paths are known.
     *
     * @return bool
     *   True if all paths are known, false if one or more paths are unknown.
     */
    protected function allPathsKnown() {
        return !empty($this->getDrupalRoot()) && !empty($this->getComposerRoot()) && !empty($this->getVendorDir());
    }

    /**
     * Get path stored in environment variable.
     *
     * @param string $variable
     *   The name of the environment variable to retrieve the path from.
     *
     * @return false|string
     *   A path if it is valid. False otherwise.
     */
    protected function getValidEnvironmentVariablePath($variable) {
        $path = getenv($variable);
        if (is_string($path) && is_dir($path)) {
            return $path;
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

}
