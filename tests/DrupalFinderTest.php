<?php

namespace DrupalFinder\Tests;

use DrupalFinder\DrupalFinder;
use PHPUnit\Framework\TestCase;

class DrupalFinderTest extends TestCase {
  public function testPaths() {
    $finder = new DrupalFinder();
    $this->assertSame(dirname(__DIR__) . '/web', $finder->getDrupalRoot());
    $this->assertSame(dirname(__DIR__) . '/vendor', $finder->getVendorDir());
    $this->assertSame(dirname(__DIR__), $finder->getComposerRoot());
  }
}