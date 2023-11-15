<?php

use DrupalFinder\DrupalFinder;

require __DIR__ . '/vendor/autoload.php';

$finder = new DrupalFinder();

return json_encode([
    'getComposerRoot' => $finder->getComposerRoot(),
    'getVendorDir' => $finder->getVendorDir(),
    'getDrupalRoot' => $finder->getDrupalRoot(),
  ]
);
