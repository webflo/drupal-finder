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

}
