<?php

/**
 * @file
 * Contains \DrupalFinder\DrupalFinderComposerRuntime.
 */

namespace DrupalFinder;

use Composer\InstalledVersions;

class DrupalFinderComposerRuntime
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
        if (isset($GLOBALS['_composer_autoload_path'])) {
          $composerAutoloadPath = realpath($GLOBALS['_composer_autoload_path']);
          if (is_string($composerAutoloadPath)) {
            $installed = dirname($composerAutoloadPath) . '/composer/installed.php';
            if (file_exists($installed)) {
              $installed = require $installed;
              return realpath($installed['root']['install_path']);
            }
          }
        }
        $root = InstalledVersions::getRootPackage();
        return realpath($root['install_path']);
    }

    /**
     * Get the vendor path.
     */
    public function getVendorDir(): ?string
    {
      $installedVersions = new InstalledVersions();
      if (isset($GLOBALS['_composer_autoload_path'])) {
        $composerAutoloadPath = realpath($GLOBALS['_composer_autoload_path']);
        if (is_string($composerAutoloadPath)) {
          return dirname($composerAutoloadPath);
        }
      }
      $reflection = new \ReflectionClass(InstalledVersions::class);
      return realpath(dirname(dirname($reflection->getFileName())));
    }

}
