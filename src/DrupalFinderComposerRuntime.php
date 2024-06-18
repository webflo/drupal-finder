<?php

/**
 * @file
 * Contains \DrupalFinder\DrupalFinderComposerRuntime.
 */

namespace DrupalFinder;

use Composer\InstalledVersions;
use Composer\Autoload\ClassLoader;

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
        foreach (InstalledVersions::getAllRawData() as $data) {
            if (isset($data['versions']['drupal/core'])) {
                return realpath($data['root']['install_path']);
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
        foreach (ClassLoader::getRegisteredLoaders() as $vendorDir => $loader) {
            if ($loader->findFile(\Drupal::class)) {
                return realpath($vendorDir);
            }
        }
    }

}
