<?php

/**
 * @file
 * Contains \DrupalFinder\DrupalFinder.
 */

namespace DrupalFinder;
use SplFileInfo;

class DrupalFinder {

  /**
   * Drupal web public directory.
   *
   * @var string
   */
  private $drupalRoot;

  /**
   * Drupal package composer directory.
   *
   * @var string
   */
  private $composerRoot;

  public function locateRoot($start_path) {
    $start_path = new SplFileInfo($start_path);
    $this->drupalRoot = FALSE;
    $this->composerRoot = FALSE;

    foreach (array(TRUE, FALSE) as $follow_symlinks) {
      for ($path = $start_path;
           $path->getFilename() != '.';
           $path = $path->getPathInfo()) {
        if ($follow_symlinks && $path->isLink()) {
          $path = $path->getRealPath();
        }
        // Check the start path.
        if ($checked_path = $this->isValidRoot($path->getPathname())) {
          return $checked_path;
        }
      };
    }

    return FALSE;
  }

  /**
   * @param $path
   *
   * @return string|FALSE
   */
  protected function isValidRoot($path) {
    if (!empty($path) && is_dir($path) && file_exists($path . '/autoload.php') && file_exists($path . '/composer.json')) {
      // Additional check for the presence of core/composer.json to
      // grant it is not a Drupal 7 site with a base folder named "core".
      $candidate = 'core/includes/common.inc';
      if (file_exists($path . '/' . $candidate) && file_exists($path . '/core/core.services.yml')) {
        if (file_exists($path . '/core/misc/drupal.js') || file_exists($path . '/core/assets/js/drupal.js')) {
          $this->composerRoot = $path;
          $this->drupalRoot = $path;
        }
      }
    }
    if (!empty($path) && is_dir($path) && file_exists($path . '/composer.json')) {
      $json = json_decode(file_get_contents($path . '/composer.json'), TRUE);
      if (is_array($json) && isset($json['require']['drupal/core'])) {
        if (isset($json['extra']['installer-paths']) && is_array($json['extra']['installer-paths'])) {
          foreach ($json['extra']['installer-paths'] as $install_path => $items) {
            if (in_array('type:drupal-core', $items)) {
              $this->composerRoot = $path;
              $this->drupalRoot = $path . '/' . substr($install_path, 0, -5);
            }
          }
        }
      }
    }
    return ($this->drupalRoot && $this->composerRoot);
  }

  /**
   * @return string
   */
  public function getDrupalRoot() {
    return $this->drupalRoot;
  }

  /**
   * @return string
   */
  public function getComposerRoot() {
    return $this->composerRoot;
  }

}
