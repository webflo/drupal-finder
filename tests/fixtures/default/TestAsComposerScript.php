<?php

namespace DrupalFinder\Tests\Fixtures\Default;

use Composer\Script\Event;
use DrupalFinder\DrupalFinderComposerRuntime;

class TestAsComposerScript {

  public static function dumpDrupalFinder(Event $event) {
    $finder = new DrupalFinderComposerRuntime();
    $event->getIO()->writeRaw(json_encode([
      'getComposerRoot' => $finder->getComposerRoot(),
      'getVendorDir' => $finder->getVendorDir(),
      'getDrupalRoot' => $finder->getDrupalRoot(),
    ]));
  }

}
