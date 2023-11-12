# Drupal Finder

[![GitHub Workflow Status](https://img.shields.io/github/workflow/status/webflo/drupal-finder/CI)](https://github.com/webflo/drupal-finder/actions/workflows/ci.yml)
[![Packagist](https://img.shields.io/packagist/v/webflo/drupal-finder.svg)](https://packagist.org/packages/webflo/drupal-finder)

Drupal Finder provides a class to locate a Drupal installation in a given path.

## Usage

```PHP
$drupalFinder = new \DrupalFinder\DrupalFinder();

$drupalRoot = $drupalFinder->getDrupalRoot();
$composerRoot = $drupalFinder->getComposerRoot();
$vendorDir = $drupalFinder->getVendorDir();
```

## Examples

- [Drupal Console Launcher](https://github.com/hechoendrupal/drupal-console-launcher)
- [Drush Launcher](https://github.com/drush-ops/drush-launcher)

## License

GPL-2.0+
