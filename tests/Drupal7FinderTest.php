<?php

namespace DrupalFinder\Tests;

use DrupalFinder\DrupalFinder;
use org\bovigo\vfs\vfsStream;

/**
 * @deprecated in drupal-finder:1.3.0 and is removed from drupal-finder:2.0.0.
 */
class Drupal7FinderTest extends DrupalFinderTestBase
{
    protected static $fileStructure = [
        'includes' => [
            'common.inc' => '',
        ],
        'misc' => [
            'drupal.js' => '',
        ],
        'sites' => [
            'all' => [
                'modules' => []
            ]
        ]
    ];

    /**
     * @return array
     */
    protected function getDrupalComposerStructure()
    {
        $fileStructure = [
            'web' => static::$fileStructure,
            'composer.json' => [
                'require' => [
                    'drupal/drupal' => '*',
                ],
                'extra' => [
                    'installer-paths' => [
                        'web/' => [
                            'type:drupal-core',
                        ],
                    ],
                ],
            ],
            'vendor' => [],
        ];
        return $fileStructure;
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
            'composer.json' => [
                'extra' => [
                    'installer-paths' => [
                        'web' => [
                            'drupal/drupal',
                        ],
                    ],
                ],
            ],
        ];
        $this->assertComposerStructure($fileStructure);
    }

    public function testNoDrupalRootWithRealFilesystem()
    {
        $finder = new DrupalFinder();
        $root = $this->tempdir(sys_get_temp_dir());

        $this->assertFalse($finder->locateRoot($root));
        $this->assertFalse($finder->getDrupalRoot());
        $this->assertFalse($finder->getComposerRoot());
        $this->assertFalse($finder->getVendorDir());
    }

    public function testDrupalComposerStructureWithRealFilesystem()
    {
        $finder = new DrupalFinder();
        $root = $this->tempdir(sys_get_temp_dir());
        $this->dumpToFileSystem($this->getDrupalComposerStructure(), $root);

        $this->assertTrue($finder->locateRoot($root));
        $this->assertSame($root . '/web', $finder->getDrupalRoot());
        $this->assertSame($root, $finder->getComposerRoot());
        $this->assertSame($root . '/vendor', $finder->getVendorDir());

        // Test symlink implementation
        $symlink = $this->tempdir(sys_get_temp_dir());
        $this->symlink($root, $symlink . '/foo');

        $this->assertTrue($finder->locateRoot($symlink . '/foo'));
        $this->assertSame($root . '/web', $finder->getDrupalRoot());
        $this->assertSame($root, $finder->getComposerRoot());
        $this->assertSame($root . '/vendor', $finder->getVendorDir());
    }

    public function testDrupalWithLinkedModule()
    {
        $finder = new DrupalFinder();
        $root = $this->tempdir(sys_get_temp_dir());
        $this->dumpToFileSystem($this->getDrupalComposerStructure(), $root);

        $module = $this->tempdir(sys_get_temp_dir());
        $module_link = $root . '/web/sites/all/modules/foo';
        $this->symlink($module, $module_link);

        $this->assertTrue($finder->locateRoot($module_link));
        $this->assertSame($root . '/web', realpath($finder->getDrupalRoot()));
        $this->assertSame($root, realpath($finder->getComposerRoot()));
        $this->assertSame($root . '/vendor', realpath($finder->getVendorDir()));
    }

    public function testDrupalWithCustomVendor()
    {
        $finder = new DrupalFinder();
        $root = $this->tempdir(sys_get_temp_dir());
        $fileStructure = $this->getDrupalComposerStructure();
        $composerJson = $fileStructure['composer.json'];
        $composerJson['config']['vendor-dir'] = 'vendor-foo';
        $fileStructure['composer.json'] = $composerJson;
        $fileStructure['vendor-foo'] = [];
        $this->dumpToFileSystem($fileStructure, $root);

        $this->assertTrue($finder->locateRoot($root));
        $this->assertSame($root . '/web', realpath($finder->getDrupalRoot()));
        $this->assertSame($root, realpath($finder->getComposerRoot()));
        $this->assertSame($root . '/vendor-foo', realpath($finder->getVendorDir()));
    }

    /**
     * @param $fileStructure
     */
    protected function assertComposerStructure($fileStructure)
    {
        $finder = new DrupalFinder();
        $fileStructure = $this->prepareFileStructure($fileStructure);
        $root = vfsStream::setup('root', null, $fileStructure);
        $this->assertTrue($finder->locateRoot($root->url() . '/web'));
        $this->assertSame('vfs://root/web', $finder->getDrupalRoot());
        $this->assertSame('vfs://root', $finder->getComposerRoot());
        $this->assertSame('vfs://root/vendor', $finder->getVendorDir());

        $this->assertTrue($finder->locateRoot($root->url() . '/web/misc'));
        $this->assertSame('vfs://root/web', $finder->getDrupalRoot());
        $this->assertSame('vfs://root', $finder->getComposerRoot());
        $this->assertSame('vfs://root/vendor', $finder->getVendorDir());

        $this->assertTrue($finder->locateRoot($root->url()));
        $this->assertSame('vfs://root/web', $finder->getDrupalRoot());
        $this->assertSame('vfs://root', $finder->getComposerRoot());
        $this->assertSame('vfs://root/vendor', $finder->getVendorDir());

        $root = vfsStream::setup(
            'root',
            null,
            ['nested_folder' => $fileStructure]
        );
        $this->assertFalse($finder->locateRoot($root->url()));
        $this->assertFalse($finder->getDrupalRoot());
        $this->assertFalse($finder->getComposerRoot());
        $this->assertFalse($finder->getVendorDir());
    }
}
