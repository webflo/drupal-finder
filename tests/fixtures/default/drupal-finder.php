<?php

use DrupalFinder\DrupalFinderComposerRuntime;

require __DIR__ . '/vendor/autoload.php';

$finder = new DrupalFinderComposerRuntime();

return json_encode([
    'getComposerRoot' => $finder->getComposerRoot(),
    'getVendorDir' => $finder->getVendorDir(),
    'getDrupalRoot' => $finder->getDrupalRoot(),
  ]
);
