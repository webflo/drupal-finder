<?php

use DrupalFinder\DrupalFinder;

require __DIR__ . '/foo/bar/autoload.php';

$finder = new DrupalFinder();

return json_encode([
    'getComposerRoot' => $finder->getComposerRoot(),
    'getVendorDir' => $finder->getVendorDir(),
    'getDrupalRoot' => $finder->getDrupalRoot(),
  ]
);
