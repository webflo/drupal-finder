<?php

/**
 * @file
 * Contains \DrupalFinder\DrupalFinder.
 */

namespace DrupalFinder;

use Composer\InstalledVersions;

class DrupalFinder
{
    /**
     * Get the Drupal root.
     *
     * @return string|bool
     *   The path to the Drupal root, if it was discovered. False otherwise.
     */
    public function getDrupalRoot()
    {
      $core = InstalledVersions::getInstallPath('drupal/core');
      return $core ? realpath(dirname($core)) : false;
    }

    /**
     * Get the Composer root.
     *
     * @return string|bool
     *   The path to the Composer root, if it was discovered. False otherwise.
     */
    public function getComposerRoot()
    {
        return realpath(dirname($this->getVendorDir()));
    }

    /**
     * Get the vendor path.
     *
     * @return string|bool
     *   The path to the vendor directory, if it was found. False otherwise.
     */
    public function getVendorDir()
    {
      // See https://getcomposer.org/doc/07-runtime.md#autoloader-path-in-binaries
      // PHPUnit replaces $_composer_autoload_path with its constant in vendor/phpunit/phpunit/phpunit
      $autoload_path = $GLOBALS['_composer_autoload_path'] ?? PHPUNIT_COMPOSER_INSTALL;
      return isset($autoload_path) ? realpath(dirname($autoload_path)) : false;
    }

}
