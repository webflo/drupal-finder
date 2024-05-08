<?php

use DrupalFinder\DrupalFinderComposerRuntime;

require __DIR__ . '/foo/bar/autoload.php';

$finder = new DrupalFinderComposerRuntime();

return json_encode([
    'getComposerRoot' => $finder->getComposerRoot(),
    'getVendorDir' => $finder->getVendorDir(),
    'getDrupalRoot' => $finder->getDrupalRoot(),
  ]
);
