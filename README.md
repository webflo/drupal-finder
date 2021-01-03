# Drupal Finder

[![Travis](https://img.shields.io/travis/webflo/drupal-finder.svg)](https://travis-ci.org/webflo/drupal-finder) [![Packagist](https://img.shields.io/packagist/v/webflo/drupal-finder.svg)](https://packagist.org/packages/webflo/drupal-finder)

Drupal Finder provides a class to locate a Drupal installation in a given path.

## Usage

```PHP
$drupalFinder = new \DrupalFinder\DrupalFinder();
if ($drupalFinder->locateRoot(getcwd())) {
    $drupalRoot = $drupalFinder->getDrupalRoot();
    $composerRoot = $drupalFinder->getComposerRoot();
    ...
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

## Examples

- [Drupal Console Launcher](https://github.com/hechoendrupal/drupal-console-launcher)
- [Drush Launcher](https://github.com/drush-ops/drush-launcher)

## License

GPL-2.0+
