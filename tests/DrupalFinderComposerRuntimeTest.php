<?php

namespace DrupalFinder\Tests;

use PHPUnit\Framework\TestCase;

class DrupalFinderComposerRuntimeTest extends TestCase {

  protected const installFixtures = 'Execute "composer install-fixtures" first.';

  /**
   * @runInSeparateProcess
   */
  public function testDefault() {
    $basePath = realpath( __DIR__ . '/fixtures/default');
    $this->assertDirectoryExists($basePath . '/vendor', static::installFixtures);
    $this->assertDirectoryExists($basePath . '/web', static::installFixtures);

    $result = json_decode(require $basePath  . '/drupal-finder.php', TRUE);
    $this->assertSame($result['getComposerRoot'], $basePath);
    $this->assertSame($result['getVendorDir'], $basePath . '/vendor');
    $this->assertSame($result['getDrupalRoot'], $basePath . '/web');
  }

  /**
   * @runInSeparateProcess
   */
  public function testCustomVendor() {
    $basePath = realpath( __DIR__ . '/fixtures/custom-vendor');
    $this->assertDirectoryExists($basePath . '/foo/bar', static::installFixtures);
    $this->assertDirectoryExists($basePath . '/foo/bar/drupal', static::installFixtures);

    $result = json_decode(require $basePath  . '/drupal-finder.php', TRUE);
    $this->assertSame($result['getComposerRoot'], $basePath);
    $this->assertSame($result['getVendorDir'], $basePath . '/foo/bar');
    $this->assertSame($result['getDrupalRoot'], $basePath . '/foo/bar/drupal');
  }

}
