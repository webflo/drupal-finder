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

### Environment variables fallback

If normal detection fails (e.g. you do not have a composer.json file), then the
detection mechanism switches over to looking for explicitly set environment
variables, which point to the pertinent directory:

- `DRUPAL_FINDER_DRUPAL_ROOT`
- `DRUPAL_FINDER_COMPOSER_ROOT`
- `DRUPAL_FINDER_VENDOR_DIR`

For example:

- `DRUPAL_FINDER_DRUPAL_ROOT=/var/www/web`
- `DRUPAL_FINDER_COMPOSER_ROOT=/var/www`
- `DRUPAL_FINDER_VENDOR_DIR=/var/www/vendor`

## Examples

- [Drupal Console Launcher](https://github.com/hechoendrupal/drupal-console-launcher)
- [Drush Launcher](https://github.com/drush-ops/drush-launcher)

## License

GPL-2.0+
