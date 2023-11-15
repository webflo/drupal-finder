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
     * Get the Drupal root path.
     */
    public function getDrupalRoot(): ?string
    {
      $core = InstalledVersions::getInstallPath('drupal/core');
      return $core ? realpath(dirname($core)) : null;
    }

    /**
     * Get the path to the Composer root directory.
     */
    public function getComposerRoot(): ?string
    {
      $vendor = $this->getVendorDir();
      return $vendor ? realpath(dirname($vendor)) : null;
    }

    /**
     * Get the vendor path.
     */
    public function getVendorDir(): ?string
    {
      $return = null;
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
