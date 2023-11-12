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
     *   The path to the Drupal root, if it was discovered. False otherwise.
     */
    public function getDrupalRoot(): string|bool
    {
      $core = InstalledVersions::getInstallPath('drupal/core');
      return $core ? realpath(dirname($core)) : false;
    }

    /**
     * Get the Composer root.
     *
     * @return
     *   The path to the Composer root, if it was discovered. False otherwise.
     */
    public function getComposerRoot(): string|bool
    {
        return realpath(dirname($this->getVendorDir()));
    }

    /**
     * Get the vendor path.
     *
     * @return
     *   The path to the vendor directory, if it was found. False otherwise.
     */
    public function getVendorDir(): string|bool
    {
      $return = false;
      if (isset($GLOBALS['_composer_autoload_path'])) {
        // See https://getcomposer.org/doc/07-runtime.md#autoloader-path-in-binaries
        $return = realpath(dirname($GLOBALS['_composer_autoload_path']));
      } elseif (defined('PHPUNIT_COMPOSER_INSTALL')) {
        // PHPUnit replaces $_composer_autoload_path with its constant in vendor/phpunit/phpunit/phpunit
        $return = realpath(dirname(PHPUNIT_COMPOSER_INSTALL));
      }
      return $return;
    }

}
