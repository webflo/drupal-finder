# Drupal Finder

[![Build Status](https://travis-ci.org/webflo/drupal-finder.svg?branch=master)](https://travis-ci.org/webflo/drupal-finder)

Drupal Finder provides a class to locate a Drupal installation in a given path.

## Usage

```
$drupalFinder = new \DrupalFinder\DrupalFinder();
if ($drupalFinder->locateRoot(getcwd())) {
    $drupalRoot = $drupalFinder->getDrupalRoot();
    $composerRoot = $drupalFinder->getComposerRoot();
    ...
}
```

## Examples

- [Drupal Console Launcher](https://github.com/hechoendrupal/drupal-console-launcher/blob/master/bin/drupal.php)
- [Drush Shim](https://github.com/webflo/drush-shim) (with webflo/drupal-finder:^0.0.1)

## License

GPL-2.0+
