<?php

use org\bovigo\vfs\vfsStream;

class DrupalFinderTest extends PHPUnit_Framework_TestCase {

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
  ];

  /**
   * @return array
   */
  protected function getDrupalComposerStructure() {
    $fileStructure = [
      'web' => static::$fileStructure,
      'composer.json' => json_encode([
        'require' => [
          'drupal/core' => '*'
        ],
        'extra' => [
          'installer-paths' => [
            'web/core' => [
              'type:drupal-core'
            ],
          ],
        ],
      ])
    ];
    unset($fileStructure['web']['composer.json']);
    return $fileStructure;
  }

  protected function setUp() {
    parent::setUp();
    $this->finder = new \DrupalFinder\DrupalFinder();
  }

  public function testDrupalDefaultStructure() {
    $root = vfsStream::setup('root', null, static::$fileStructure);

    $this->assertTrue($this->finder->locateRoot($root->url()));
    $this->assertSame('vfs://root', $this->finder->getDrupalRoot());
    $this->assertSame('vfs://root', $this->finder->getComposerRoot());

    $this->assertTrue($this->finder->locateRoot($root->url() . '/misc'));
    $this->assertSame('vfs://root', $this->finder->getDrupalRoot());
    $this->assertSame('vfs://root', $this->finder->getComposerRoot());

    $root = vfsStream::setup('root', null, ['project' => static::$fileStructure]);
    $this->assertFalse($this->finder->locateRoot($root->url()), 'Not in the scope of the project');
    $this->assertFalse($this->finder->getDrupalRoot());
    $this->assertFalse($this->finder->getComposerRoot());
  }

  public function testDrupalComposerStructure() {
    $fileStructure = $this->getDrupalComposerStructure();

    $root = vfsStream::setup('root', null, $fileStructure);
    $this->assertTrue($this->finder->locateRoot($root->url() . '/web'));
    $this->assertSame('vfs://root/web', $this->finder->getDrupalRoot());
    $this->assertSame('vfs://root', $this->finder->getComposerRoot());

    $this->assertTrue($this->finder->locateRoot($root->url() . '/web/misc'));
    $this->assertSame('vfs://root/web', $this->finder->getDrupalRoot());
    $this->assertSame('vfs://root', $this->finder->getComposerRoot());

    $this->assertTrue($this->finder->locateRoot($root->url()));
    $this->assertSame('vfs://root/web', $this->finder->getDrupalRoot());
    $this->assertSame('vfs://root', $this->finder->getComposerRoot());

    $root = vfsStream::setup('root', null, ['nested_folder' => $fileStructure]);
    $this->assertFalse($this->finder->locateRoot($root->url()));
    $this->assertFalse($this->finder->getDrupalRoot());
    $this->assertFalse($this->finder->getComposerRoot());
  }

  public function testWithRealFilesystem() {
    $root = $this->tempdir(sys_get_temp_dir());
    $this->dumpToFileSystem(static::$fileStructure, $root);

    $this->assertTrue($this->finder->locateRoot($root));
    $this->assertSame($root, $this->finder->getDrupalRoot());
    $this->assertSame($root, $this->finder->getComposerRoot());

    // Test symlink implementation
    $symlink = $this->tempdir(sys_get_temp_dir());
    symlink($root, $symlink . '/foo');

    $this->assertTrue($this->finder->locateRoot($symlink . '/foo'));
    $this->assertSame(realpath($root), $this->finder->getDrupalRoot());
    $this->assertSame(realpath($root), $this->finder->getComposerRoot());
  }

  public function testDrupalComposerStructureWithSymlink() {
    $root = $this->tempdir(sys_get_temp_dir());
    $this->dumpToFileSystem($this->getDrupalComposerStructure(), $root);

    $this->assertTrue($this->finder->locateRoot($root));
    $this->assertSame($root . '/web', $this->finder->getDrupalRoot());
    $this->assertSame($root, $this->finder->getComposerRoot());

    // Test symlink implementation
    $symlink = $this->tempdir(sys_get_temp_dir());
    symlink($root, $symlink . '/foo');

    $this->assertTrue($this->finder->locateRoot($symlink . '/foo'));
    $this->assertSame(realpath($root . '/web'), $this->finder->getDrupalRoot());
    $this->assertSame(realpath($root), $this->finder->getComposerRoot());
  }

  protected function dumpToFileSystem($fileStructure, $root) {
    foreach ($fileStructure as $name => $content) {
      if (is_array($content)) {
        mkdir($root . '/' . $name);
        $this->dumpToFileSystem($content, $root . '/' . $name);
      }
      else {
        file_put_contents($root . '/' . $name, $content);
      }
    }
  }

  protected function tempdir($dir, $prefix = '', $mode = 0700) {
    if (substr($dir, -1) != '/') {
      $dir .= '/';
    }
    do {
      $path = $dir . $prefix . mt_rand(0, 9999999);
    }
    while (!mkdir($path, $mode));
    return $path;
  }

}
