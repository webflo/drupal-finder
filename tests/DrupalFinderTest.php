<?php

use org\bovigo\vfs\vfsStream;

class DrupalFinderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \DrupalFinder\DrupalFinder
     */
    protected $finder;

    protected static $fileStructure = [
      'autoload.php' => '',
      'composer.json' => '',
      'core' => [
        'includes' => [
          'common.inc' => '',
        ],
        'misc' => [
          'drupal.js' => '',
        ],
        'core.services.yml' => '',
      ],
      'modules' => [],
      'vendor' => [],
    ];

    /**
     * @return array
     */
    protected function getDrupalComposerStructure()
    {
        $fileStructure = [
          'web' => static::$fileStructure,
          'composer.json' => json_encode([
            'require' => [
              'drupal/core' => '*',
            ],
            'extra' => [
              'installer-paths' => [
                'web/core' => [
                  'type:drupal-core',
                ],
              ],
            ],
          ]),
          'vendor' => [],
        ];
        unset($fileStructure['web']['composer.json']);
        unset($fileStructure['web']['vendor']);

        return $fileStructure;
    }

    protected function setUp()
    {
        parent::setUp();
        $this->finder = new \DrupalFinder\DrupalFinder();
    }

    public function testDrupalDefaultStructure()
    {
        $root = vfsStream::setup('root', null, static::$fileStructure);

        $this->assertTrue($this->finder->locateRoot($root->url()));
        $this->assertSame('vfs://root', $this->finder->getDrupalRoot());
        $this->assertSame('vfs://root', $this->finder->getComposerRoot());
        $this->assertSame('vfs://root/vendor', $this->finder->getVendorDir());

        $this->assertTrue($this->finder->locateRoot($root->url() . '/misc'));
        $this->assertSame('vfs://root', $this->finder->getDrupalRoot());
        $this->assertSame('vfs://root', $this->finder->getComposerRoot());
        $this->assertSame('vfs://root/vendor', $this->finder->getVendorDir());

        $root = vfsStream::setup(
            'root',
            null,
            ['project' => static::$fileStructure]
        );
        $this->assertFalse(
            $this->finder->locateRoot($root->url()),
            'Not in the scope of the project'
        );
        $this->assertFalse($this->finder->getDrupalRoot());
        $this->assertFalse($this->finder->getComposerRoot());
        $this->assertFalse($this->finder->getVendorDir());
    }

    public function testDrupalComposerStructure()
    {
        $fileStructure = $this->getDrupalComposerStructure();
        $this->assertComposerStructure($fileStructure);
    }

    public function testDrupalComposerStructureWithoutRequire()
    {
        $fileStructure = [
          'web' => static::$fileStructure,
          'composer.json' => json_encode([
            'extra' => [
              'installer-paths' => [
                'web/core' => [
                  'drupal/core',
                ],
              ],
            ],
          ]),
        ];
        unset($fileStructure['web']['composer.json']);
        $this->assertComposerStructure($fileStructure);
    }

    public function testNoDrupalRootWithRealFilesystem()
    {
        $root = $this->tempdir(sys_get_temp_dir());

        $this->assertFalse($this->finder->locateRoot($root));
        $this->assertFalse($this->finder->getDrupalRoot());
        $this->assertFalse($this->finder->getComposerRoot());
        $this->assertFalse($this->finder->getVendorDir());
    }

    public function testDrupalDefaultStructureWithRealFilesystem()
    {
        $root = $this->tempdir(sys_get_temp_dir());
        $this->dumpToFileSystem(static::$fileStructure, $root);

        $this->assertTrue($this->finder->locateRoot($root));
        $this->assertSame($root, $this->finder->getDrupalRoot());
        $this->assertSame($root, $this->finder->getComposerRoot());
        $this->assertSame($root . '/vendor', $this->finder->getVendorDir());

        // Test symlink implementation
        $symlink = $this->tempdir(sys_get_temp_dir());
        $this->symlink($root, $symlink . '/foo');

        $this->assertTrue($this->finder->locateRoot($symlink . '/foo'));
        $this->assertSame($root, $this->finder->getDrupalRoot());
        $this->assertSame($root, $this->finder->getComposerRoot());
        $this->assertSame($root . '/vendor', $this->finder->getVendorDir());
    }

    public function testDrupalComposerStructureWithRealFilesystem()
    {
        $root = $this->tempdir(sys_get_temp_dir());
        $this->dumpToFileSystem($this->getDrupalComposerStructure(), $root);

        $this->assertTrue($this->finder->locateRoot($root));
        $this->assertSame($root . '/web', $this->finder->getDrupalRoot());
        $this->assertSame($root, $this->finder->getComposerRoot());
        $this->assertSame($root . '/vendor', $this->finder->getVendorDir());

        // Test symlink implementation
        $symlink = $this->tempdir(sys_get_temp_dir());
        $this->symlink($root, $symlink . '/foo');

        $this->assertTrue($this->finder->locateRoot($symlink . '/foo'));
        $this->assertSame($root . '/web', $this->finder->getDrupalRoot());
        $this->assertSame($root, $this->finder->getComposerRoot());
        $this->assertSame($root . '/vendor', $this->finder->getVendorDir());
    }

    public function testDrupalWithLinkedModule()
    {
        $root = $this->tempdir(sys_get_temp_dir());
        $this->dumpToFileSystem(static::$fileStructure, $root);

        $module = $this->tempdir(sys_get_temp_dir());
        $module_link = $root . '/modules/foo';
        $this->symlink($module, $module_link);

        $this->assertTrue($this->finder->locateRoot($module_link));
        $this->assertSame($root, realpath($this->finder->getDrupalRoot()));
        $this->assertSame($root, realpath($this->finder->getComposerRoot()));
        $this->assertSame($root . '/vendor', realpath($this->finder->getVendorDir()));
    }

    public function testDrupalWithCustomVendor()
    {
        $root = $this->tempdir(sys_get_temp_dir());
        $fileStructure = static::$fileStructure;
        $fileStructure['composer.json'] = json_encode([
            'config' => [
                'vendor-dir' => 'vendor-foo'
            ]
        ], JSON_UNESCAPED_SLASHES);
        $fileStructure['vendor-foo'] = [];
        $this->dumpToFileSystem($fileStructure, $root);

        $this->assertTrue($this->finder->locateRoot($root));
        $this->assertSame($root, realpath($this->finder->getDrupalRoot()));
        $this->assertSame($root, realpath($this->finder->getComposerRoot()));
        $this->assertSame($root . '/vendor-foo', realpath($this->finder->getVendorDir()));
    }

    protected function dumpToFileSystem($fileStructure, $root)
    {
        foreach ($fileStructure as $name => $content) {
            if (is_array($content)) {
                mkdir($root . '/' . $name);
                $this->dumpToFileSystem($content, $root . '/' . $name);
            } else {
                file_put_contents($root . '/' . $name, $content);
            }
        }
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
            ['DrupalFinderTest', 'tempdir_remove'],
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
     * @throws PHPUnit_Framework_SkippedTestError
     */
    private function symlink($target, $link)
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

    /**
     * @param $fileStructure
     */
    protected function assertComposerStructure($fileStructure)
    {
        $root = vfsStream::setup('root', null, $fileStructure);
        $this->assertTrue($this->finder->locateRoot($root->url() . '/web'));
        $this->assertSame('vfs://root/web', $this->finder->getDrupalRoot());
        $this->assertSame('vfs://root', $this->finder->getComposerRoot());
        $this->assertSame('vfs://root/vendor', $this->finder->getVendorDir());

        $this->assertTrue($this->finder->locateRoot($root->url() . '/web/misc'));
        $this->assertSame('vfs://root/web', $this->finder->getDrupalRoot());
        $this->assertSame('vfs://root', $this->finder->getComposerRoot());
        $this->assertSame('vfs://root/vendor', $this->finder->getVendorDir());

        $this->assertTrue($this->finder->locateRoot($root->url()));
        $this->assertSame('vfs://root/web', $this->finder->getDrupalRoot());
        $this->assertSame('vfs://root', $this->finder->getComposerRoot());
        $this->assertSame('vfs://root/vendor', $this->finder->getVendorDir());

        $root = vfsStream::setup(
          'root',
          null,
          ['nested_folder' => $fileStructure]
        );
        $this->assertFalse($this->finder->locateRoot($root->url()));
        $this->assertFalse($this->finder->getDrupalRoot());
        $this->assertFalse($this->finder->getComposerRoot());
        $this->assertFalse($this->finder->getVendorDir());
    }
}

define('WIN_ERROR_PRIVILEGE_NOT_HELD', '1314');
