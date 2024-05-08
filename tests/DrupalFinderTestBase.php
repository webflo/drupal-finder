<?php

namespace DrupalFinder\Tests;

use DrupalFinder\DrupalFinder;
use Exception;
use PHPUnit\Framework\SkippedTestError;
use PHPUnit\Framework\TestCase;

/**
 * @deprecated in drupal-finder:1.3.0 and is removed from drupal-finder:2.0.0.
 */
abstract class DrupalFinderTestBase extends TestCase
{
    /**
     * @var string
     */
    protected $envNameDrupal;

    /**
     * @var string
     */
    protected $envNameComposer;

    /**
     * @var string
     */
    protected $envNameVendor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->envNameDrupal = DrupalFinder::ENV_DRUPAL_ROOT;
        $this->envNameComposer = DrupalFinder::ENV_COMPOSER_ROOT;
        $this->envNameVendor = DrupalFinder::ENV_VENDOR_DIR;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // Unset variables to ensure their values don't carry over into other
        // tests that are going to run.
        putenv('DRUPAL_FINDER_DRUPAL_ROOT');
        putenv('DRUPAL_FINDER_COMPOSER_ROOT');
        putenv('DRUPAL_FINDER_VENDOR_DIR');
    }

    public function testOnlyDrupalEnvironmentVariable() {
        $finder = new DrupalFinder();
        $fileStructure = [
            'web' => [],
        ];

        $root = $this->tempdir(sys_get_temp_dir());
        $this->dumpToFileSystem($fileStructure, $root);

        $drupal_root = $root . '/web';

        putenv("{$this->envNameDrupal}=$drupal_root");

        // DrupalFinder::locateRoot should be true if the Drupal root is known.
        $this->assertTrue($finder->locateRoot($root));
        $this->assertSame($finder->getDrupalRoot(), $drupal_root);
    }

    public function testOnlyVendorEnvironmentVariable() {
        $finder = new DrupalFinder();
        $fileStructure = [
            'vendor' => [],
        ];

        $root = $this->tempdir(sys_get_temp_dir());
        $this->dumpToFileSystem($fileStructure, $root);

        $vendor_dir = $root . '/vendor';

        putenv("{$this->envNameVendor}=$vendor_dir");

        // DrupalFinder::locateRoot should be false since Drupal root is unknown.
        $this->assertFalse($finder->locateRoot($root));
        $this->assertSame($finder->getVendorDir(), $vendor_dir);
    }

    public function testOnlyComposerEnvironmentVariable() {
        $finder = new DrupalFinder();
        $fileStructure = [];

        $root = $this->tempdir(sys_get_temp_dir());
        $this->dumpToFileSystem($fileStructure, $root);

        $composer_dir = $root;

        putenv("{$this->envNameComposer}=$composer_dir");

        // DrupalFinder::locateRoot should be false since Drupal root is unknown.
        $this->assertFalse($finder->locateRoot($root));
        $this->assertSame($finder->getComposerRoot(), $composer_dir);
    }

    protected function dumpToFileSystem($fileStructure, $root)
    {
        $fileStructure = $this->prepareFileStructure($fileStructure);
        foreach ($fileStructure as $name => $content) {
            if (is_array($content)) {
                mkdir($root . '/' . $name);
                $this->dumpToFileSystem($content, $root . '/' . $name);
            } else {
                file_put_contents($root . '/' . $name, $content);
            }
        }
    }

    protected function prepareFileStructure($fileStructure)
    {
        foreach ($fileStructure as $name => $content) {
            if (($name === 'composer.json' || $name === 'composer.lock') && is_array($content)) {
                $fileStructure[$name] = json_encode($content, JSON_UNESCAPED_SLASHES);
            } elseif (is_array($content)) {
                $fileStructure[$name] = $this->prepareFileStructure($content);
            }
        }
        return $fileStructure;
    }

    protected function tempdir($dir, $prefix = '', $mode = 0700)
    {
        if (substr($dir, -1) != '/') {
            $dir .= '/';
        }
        do {
            $path = $dir . $prefix . mt_rand(0, 9999999);
        } while (!mkdir($path, $mode));
        register_shutdown_function(
            [static::class, 'tempdir_remove'],
            $path
        );

        return realpath($path);
    }

    public static function tempdir_remove($path)
    {
        if (is_link($path)) {
            if (defined('PHP_WINDOWS_VERSION_BUILD')) {
                rmdir($path);
            } else {
                unlink($path);
            }

            return;
        }

        foreach (scandir($path) as $child) {
            if (in_array($child, ['.', '..'])) {
                continue;
            }
            $child = "$path/$child";
            is_dir($child) ? static::tempdir_remove($child) : unlink($child);
        }
        rmdir($path);
    }

    /**
     * @param $target
     * @param $link
     *
     * @throws SkippedTestError
     */
    protected function symlink($target, $link)
    {
        try {
            return symlink($target, $link);
        } catch (Exception $e) {
            if (defined('PHP_WINDOWS_VERSION_BUILD')
                && strstr($e->getMessage(), WIN_ERROR_PRIVILEGE_NOT_HELD)
            ) {
                $this->markTestSkipped(<<<'MESSAGE'
No privilege to create symlinks. Run test as Administrator (elevated process).
MESSAGE
                );
            }
            throw $e;
        }
    }
}

define('WIN_ERROR_PRIVILEGE_NOT_HELD', '1314');
