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
        $root = InstalledVersions::getRootPackage();
        return realpath($root['install_path']);
    }

    /**
     * Get the vendor path.
     */
    public function getVendorDir(): ?string
    {
      $reflection = new \ReflectionClass(InstalledVersions::class);
      return realpath(dirname(dirname($reflection->getFileName())));
    }

}
