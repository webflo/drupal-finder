# Drupal Finder

[![GitHub Workflow Status](https://img.shields.io/github/workflow/status/webflo/drupal-finder/CI)](https://github.com/webflo/drupal-finder/actions/workflows/ci.yml)
[![Packagist](https://img.shields.io/packagist/v/webflo/drupal-finder.svg)](https://packagist.org/packages/webflo/drupal-finder)

Drupal Finder provides a class to locate a Drupal installation in a given path.

## Usage

```PHP
$drupalFinder = new \DrupalFinder\DrupalFinder();
if ($drupalFinder->locateRoot(getcwd())) {
    $drupalRoot = $drupalFinder->getDrupalRoot();
    $composerRoot = $drupalFinder->getComposerRoot();
    $vendorDir = $drupalFinder->getVendorDir();
}
```

### Environment variables

If a set of environment variables is specified, then Drupal Finder uses those
values to determine the paths of the pertinent directories:

- `DRUPAL_FINDER_DRUPAL_ROOT`
- `DRUPAL_FINDER_COMPOSER_ROOT`
- `DRUPAL_FINDER_VENDOR_DIR`

For example:

- `DRUPAL_FINDER_DRUPAL_ROOT=/var/www/web`
- `DRUPAL_FINDER_COMPOSER_ROOT=/var/www`
- `DRUPAL_FINDER_VENDOR_DIR=/var/www/vendor`

This is useful for situations where you are containerizing an application,
directories may be in odd places, or a composer.json might be missing since it
is unneeded in a final build artifact.

You are not required to set all the environment variables to use this
feature. If you set an environment variable, then its associated getter
function will return the value assigned to the environment variable.

## Examples

- [Drupal Console Launcher](https://github.com/hechoendrupal/drupal-console-launcher)
- [Drush Launcher](https://github.com/drush-ops/drush-launcher)

## License

GPL-2.0+
