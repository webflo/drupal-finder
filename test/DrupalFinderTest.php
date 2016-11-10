<?php

use org\bovigo\vfs\vfsStream;

class DrupalFinderTest extends PHPUnit_Framework_TestCase {

  protected static $fileStructure = [
    'autoload.php' => '',
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

  public function testDrupalDefaultStructure() {
    $root = vfsStream::setup('root', null, static::$fileStructure);

    $finder = new \DrupalFinder\DrupalFinder();
    $this->assertEquals('vfs://root', $finder->locateRoot($root->url()));
    $this->assertEquals('vfs://root', $finder->locateRoot($root->url() . '/misc'));
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
    unset($fileStructure['web']['autoload.php']);

    $root = vfsStream::setup('root', null, $fileStructure);
    $finder = new \DrupalFinder\DrupalFinder();
    $this->assertEquals('vfs://root/web', $finder->locateRoot($root->url() . '/web'));
    $this->assertEquals('vfs://root/web', $finder->locateRoot($root->url() . '/web/misc'));
    $this->assertEquals('vfs://root/web', $finder->locateRoot($root->url()));
  }

}
