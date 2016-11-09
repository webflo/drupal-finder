<?php

/**
 * @file
 * Contains \DrupalFinder\DrupalFinder.
 */

namespace DrupalFinder;

class DrupalFinder {

  /**
   * @var string
   */
  private $drupalRoot;

  /**
   * @var string
   */
  private $projectRoot;

  public function locateRoot($start_path) {
    $drupal_root = FALSE;

    foreach (array(TRUE, FALSE) as $follow_symlinks) {
      $path = $start_path;
      if ($follow_symlinks && is_link($path)) {
        $path = realpath($path);
      }
      // Check the start path.
      if ($checked_path = $this->isValidRoot($path)) {
        $drupal_root = $checked_path;
        break;
      }
      else {
        // Move up dir by dir and check each.
        while ($path = $this->shiftPathUp($path)) {
          if ($follow_symlinks && is_link($path)) {
            $path = realpath($path);
          }
          if ($checked_path = $this->isValidRoot($path)) {
            $drupal_root = $checked_path;
            break 2;
          }
        }
      }
    }

    return $drupal_root;
  }

  /**
   * Returns parent directory.
   *
   * @param string
   *   Path to start from.
   *
   * @return string
   *   Parent path of given path.
   */
  public function shiftPathUp($path) {
    if (empty($path)) {
      return FALSE;
    }
    $path = explode(DIRECTORY_SEPARATOR, $path);
    // Move one directory up.
    array_pop($path);
    return implode(DIRECTORY_SEPARATOR, $path);
  }

  /**
   * @param $path
   *
   * @return boolean
   */
  public function isValidRoot($path) {
    if (!empty($path) && is_dir($path) && file_exists($path . '/autoload.php')) {
      // Additional check for the presence of core/composer.json to
      // grant it is not a Drupal 7 site with a base folder named "core".
      $candidate = 'core/includes/common.inc';
      if (file_exists($path . '/' . $candidate) && file_exists($path . '/core/core.services.yml')) {
        if (file_exists($path . '/core/misc/drupal.js') || file_exists($path . '/core/assets/js/drupal.js')) {
          $this->projectRoot = $path;
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
              $this->projectRoot = $path;
              $this->drupalRoot = $path . '/' . substr($install_path, 0, -5);
              return TRUE;
            }
          }
        }
      }
    }
    return FALSE;
  }

}
