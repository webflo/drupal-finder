# Drupal Finder

Drupal Finder provides a class to locate a Drupal installation in a given path.

## Usage

```
$drupalFinder = new \DrupalFinder\DrupalFinder();
$drupalRoot = $drupalFinder->locateRoot(getcwd());
```

## Example

[Drush Shim](https://github.com/webflo/drush-shim)

## License

GPL-2.0+
