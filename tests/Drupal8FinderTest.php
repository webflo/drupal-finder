<?php

namespace DrupalFinder\Tests;

use org\bovigo\vfs\vfsStream;
use DrupalFinder\DrupalFinder;

class Drupal8FinderTest extends DrupalFinderTestBase
{
    protected static $fileStructure = [
        'autoload.php' => '',
        'composer.json' => [
            'extra' => [
                'installer-paths' => [
                    'core' => [
                        'type:drupal-core'
                    ]
                ]
            ]
        ],
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

    protected static $fileStructureDrupal_8_8_x = [
        'autoload.php' => '',
        'composer.json' => [
            'name' => 'drupal/drupal',
            'require' => [
                'drupal/core' => 'self.version',
            ],
            'extra' => [
                'installer-paths' => [
                    'vendor/drupal/core' => [
                        'type:drupal-core',
                    ],
                ],
            ],
        ],
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
            'composer.json' => [
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
            ],
            'vendor' => [],
        ];
        unset($fileStructure['web']['composer.json']);
        unset($fileStructure['web']['vendor']);

        return $fileStructure;
    }

    protected function tearDown()
    {
        parent::tearDown();
        // Unset variables to ensure their values don't carry over into other
        // tests that are going to run.
        putenv('DRUPAL_FINDER_DRUPAL_ROOT');
        putenv('DRUPAL_FINDER_COMPOSER_ROOT');
        putenv('DRUPAL_FINDER_VENDOR_DIR');
    }

    public function testDrupalDefaultStructure()
    {
        $finder = new DrupalFinder();
        $root = vfsStream::setup('root', null, $this->prepareFileStructure(static::$fileStructure));

        $this->assertTrue($finder->locateRoot($root->url()));
        $this->assertSame('vfs://root', $finder->getDrupalRoot());
        $this->assertSame('vfs://root', $finder->getComposerRoot());
        $this->assertSame('vfs://root/vendor', $finder->getVendorDir());

        $this->assertTrue($finder->locateRoot($root->url() . '/misc'));
        $this->assertSame('vfs://root', $finder->getDrupalRoot());
        $this->assertSame('vfs://root', $finder->getComposerRoot());
        $this->assertSame('vfs://root/vendor', $finder->getVendorDir());

        $root = vfsStream::setup(
            'root',
            null,
            ['project' => $this->prepareFileStructure(static::$fileStructure)]
        );
        $this->assertFalse(
            $finder->locateRoot($root->url()),
            'Not in the scope of the project'
        );
        $this->assertFalse($finder->getDrupalRoot());
        $this->assertFalse($finder->getComposerRoot());
        $this->assertFalse($finder->getVendorDir());
    }

    public function testDrupalDefaultStructure_8_8_x()
    {
        $finder = new DrupalFinder();
        $root = vfsStream::setup('root', null, $this->prepareFileStructure(static::$fileStructureDrupal_8_8_x));

        $this->assertTrue($finder->locateRoot($root->url()));
        $this->assertSame('vfs://root', $finder->getDrupalRoot());
        $this->assertSame('vfs://root', $finder->getComposerRoot());
        $this->assertSame('vfs://root/vendor', $finder->getVendorDir());

        $this->assertTrue($finder->locateRoot($root->url() . '/misc'));
        $this->assertSame('vfs://root', $finder->getDrupalRoot());
        $this->assertSame('vfs://root', $finder->getComposerRoot());
        $this->assertSame('vfs://root/vendor', $finder->getVendorDir());

        $root = vfsStream::setup(
          'root',
          null,
          ['project' => $this->prepareFileStructure(static::$fileStructure)]
        );
        $this->assertFalse(
          $finder->locateRoot($root->url()),
          'Not in the scope of the project'
        );
        $this->assertFalse($finder->getDrupalRoot());
        $this->assertFalse($finder->getComposerRoot());
        $this->assertFalse($finder->getVendorDir());
    }

    public function testDrupalComposerStructure()
    {
        $fileStructure = $this->getDrupalComposerStructure();
        $this->assertComposerStructure($fileStructure);
    }

    public function testDrupalComposerStructureWithCustomRoot()
    {
        $finder = new DrupalFinder();
        $fileStructure = [
            'src' => static::$fileStructure,
            'composer.json' => [
                'require' => [
                    'drupal/core' => '*',
                ],
                'extra' => [
                    'installer-paths' => [
                        'src/core' => [
                            'type:drupal-core',
                        ],
                    ],
                ],
            ],
            'vendor' => [],
        ];
        unset($fileStructure['src']['composer.json']);
        unset($fileStructure['src']['vendor']);

        $fileStructure = $this->prepareFileStructure($fileStructure);
        $root = vfsStream::setup('root', null, $fileStructure);
        $this->assertTrue($finder->locateRoot($root->url() . '/src'));
        $this->assertSame('vfs://root/src', $finder->getDrupalRoot());
        $this->assertSame('vfs://root', $finder->getComposerRoot());
        $this->assertSame('vfs://root/vendor', $finder->getVendorDir());

        $this->assertTrue($finder->locateRoot($root->url() . '/src/misc'));
        $this->assertSame('vfs://root/src', $finder->getDrupalRoot());
        $this->assertSame('vfs://root', $finder->getComposerRoot());
        $this->assertSame('vfs://root/vendor', $finder->getVendorDir());

        $this->assertTrue($finder->locateRoot($root->url()));
        $this->assertSame('vfs://root/src', $finder->getDrupalRoot());
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

    public function testDrupalComposerStructureWithoutRequire()
    {
        $fileStructure = [
            'web' => static::$fileStructure,
            'composer.json' => [
                'extra' => [
                    'installer-paths' => [
                        'web/core' => [
                            'drupal/core',
                        ],
                    ],
                ],
            ],
        ];
        unset($fileStructure['web']['composer.json']);
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

    public function testDrupalDefaultStructureWithRealFilesystem()
    {
        $finder = new DrupalFinder();
        $root = $this->tempdir(sys_get_temp_dir());
        $this->dumpToFileSystem(static::$fileStructure, $root);

        $this->assertTrue($finder->locateRoot($root));
        $this->assertSame($root, $finder->getDrupalRoot());
        $this->assertSame($root, $finder->getComposerRoot());
        $this->assertSame($root . '/vendor', $finder->getVendorDir());

        // Test symlink implementation
        $symlink = $this->tempdir(sys_get_temp_dir());
        $this->symlink($root, $symlink . '/foo');

        $this->assertTrue($finder->locateRoot($symlink . '/foo'));
        $this->assertSame($root, $finder->getDrupalRoot());
        $this->assertSame($root, $finder->getComposerRoot());
        $this->assertSame($root . '/vendor', $finder->getVendorDir());
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
        $this->dumpToFileSystem(static::$fileStructure, $root);

        $module = $this->tempdir(sys_get_temp_dir());
        $module_link = $root . '/modules/foo';
        $this->symlink($module, $module_link);

        $this->assertTrue($finder->locateRoot($module_link));
        $this->assertSame($root, realpath($finder->getDrupalRoot()));
        $this->assertSame($root, realpath($finder->getComposerRoot()));
        $this->assertSame($root . '/vendor', realpath($finder->getVendorDir()));
    }

    public function testDrupalWithCustomVendor()
    {
        $finder = new DrupalFinder();
        $root = $this->tempdir(sys_get_temp_dir());
        $fileStructure = static::$fileStructure;
        $fileStructure['composer.json'] = [
            'config' => [
                'vendor-dir' => 'vendor-foo'
            ]
        ];
        $fileStructure['vendor-foo'] = [];
        $this->dumpToFileSystem($fileStructure, $root);

        $this->assertTrue($finder->locateRoot($root));
        $this->assertSame($root, realpath($finder->getDrupalRoot()));
        $this->assertSame($root, realpath($finder->getComposerRoot()));
        $this->assertSame($root . '/vendor-foo', realpath($finder->getVendorDir()));
    }

    public function testDrupalEnvironmentVariable() {
        $finder = new DrupalFinder();
        $fileStructure = [
            'web' => [],
        ];

        $root = $this->tempdir(sys_get_temp_dir());
        $this->dumpToFileSystem($fileStructure, $root);

        $drupal_root = $root . '/web';

        putenv("{$this->envNameDrupal}=$drupal_root");

        $this->assertTrue($finder->locateRoot($root));
        $this->assertSame($finder->getDrupalRoot(), $drupal_root);
    }

    public function testVendorEnvironmentVariable() {
        $finder = new DrupalFinder();
        $fileStructure = [
            'vendor' => [],
        ];

        $root = $this->tempdir(sys_get_temp_dir());
        $this->dumpToFileSystem($fileStructure, $root);

        $vendor_dir = $root . '/vendor';

        putenv("{$this->envNameVendor}=$vendor_dir");

        $this->assertFalse($finder->locateRoot($root));
        $this->assertSame($finder->getVendorDir(), $vendor_dir);
    }

    public function testComposerEnvironmentVariable() {
        $finder = new DrupalFinder();
        $fileStructure = [];

        $root = $this->tempdir(sys_get_temp_dir());
        $this->dumpToFileSystem($fileStructure, $root);

        $composer_dir = $root;

        putenv("{$this->envNameComposer}=$composer_dir");

        $this->assertFalse($finder->locateRoot($root));
        $this->assertSame($finder->getComposerRoot(), $composer_dir);
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
