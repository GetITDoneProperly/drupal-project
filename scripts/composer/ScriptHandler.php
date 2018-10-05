<?php

/**
 * @file
 * Contains \DrupalProject\composer\ScriptHandler.
 */

namespace DrupalProject\composer;

use Composer\Script\Event;
use Composer\Semver\Comparator;
use DrupalFinder\DrupalFinder;
use Symfony\Component\Filesystem\Filesystem;
use Webmozart\PathUtil\Path;

class ScriptHandler {


    /**
     * Generate a random string.
     *
     * @param int $length
     *   Length of generated string.
     */
    public static function generateRandomString($length = 10) {
      $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
      $charactersLength = strlen($characters);
      $randomString = '';
      for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
      }
      return $randomString;
    }

    /**
     * Create required files.
     */
    public static function createRequiredFiles(Event $event) {
      $fs = new Filesystem();
      $drupalFinder = new DrupalFinder();
      $drupalFinder->locateRoot(getcwd());
      $drupalRoot = $drupalFinder->getDrupalRoot();

      $dirs = [
        'modules',
        'profiles',
        'themes',
      ];

      // Required for unit testing.
      foreach ($dirs as $dir) {
        if (!$fs->exists($drupalRoot . '/' . $dir . '/custom')) {
          $fs->mkdir($drupalRoot . '/' . $dir . '/custom');
          $fs->touch($drupalRoot . '/' . $dir . '/custom/.gitkeep');
        }
      }
    }

    /**
     * Create site files.
     */
    public static function createSiteFiles(Event $event) {
      $fs = new Filesystem();
      $drupalFinder = new DrupalFinder();
      $drupalFinder->locateRoot(getcwd());
      $drupalRoot = $drupalFinder->getDrupalRoot();

      $directories = glob('config/drupal/*', GLOB_ONLYDIR);

      foreach ($directories as $directory) {

        $name = basename($directory);

        // Create the site directory.
        if (!$fs->exists($drupalRoot . '/sites/' . $name)) {
          $fs->mkdir($drupalRoot . '/sites/' . $name, 0750);
          $event->getIO()->write("Create a sites/" . $name . " directory");
        }

        // Prepare the settings file for installation.
        $fs->copy($drupalRoot . '/../config/drupal/settings.php', $drupalRoot . '/sites/' . $name . '/settings.php');
        $fs->chmod($drupalRoot . '/sites/' . $name . '/settings.php', 0640);
        $event->getIO()->write("Create a sites/" . $name . "/settings.php file");

        // Create the sync directory.
        if (!$fs->exists($drupalRoot . '/../config/drupal/' . $name . '/sync')) {
          $fs->mkdir($drupalRoot . '/../config/drupal/' . $name . '/sync', 0750);
          $event->getIO()->write("Create a /config/drupal/" . $name . "/sync directory");
        }

        // Create the public files directory.
        if (!$fs->exists($drupalRoot . '/../data/drupal/' . $name . '/public')) {
          $oldmask = umask(0);
          $fs->mkdir($drupalRoot . '/../data/drupal/' . $name . '/public', 0750);
          umask($oldmask);
          $event->getIO()->write("Create a /data/drupal/" . $name . "/public directory");
        }
        // Create the private files directory.
        if (!$fs->exists($drupalRoot . '/../data/drupal/' . $name . '/private')) {
          $oldmask = umask(0);
          $fs->mkdir($drupalRoot . '/../data/drupal/' . $name . '/private', 0750);
          umask($oldmask);
          $event->getIO()->write("Create a /data/drupal/" . $name . "/private directory");
        }
        // Create the files symlink.
        if (!$fs->exists($drupalRoot . '/sites/' . $name . '/files')) {
          $fs->symlink('../../../data/drupal/' . $name . '/public', $drupalRoot . '/sites/' . $name . '/files');
          $event->getIO()->write("Create a sites/" . $name . "/files symlink");
        }

        // Generate salt file.
        if (!file_exists($drupalRoot . '/../config/drupal/' . $name . '/salt.txt')) {
          $fp = fopen($drupalRoot . '/../config/drupal/' . $name . '/salt.txt', 'w');
          fwrite($fp, self::generateRandomString(64));
          fclose($fp);
        }

      }
    }

  /**
   * Checks if the installed version of Composer is compatible.
   *
   * Composer 1.0.0 and higher consider a `composer install` without having a
   * lock file present as equal to `composer update`. We do not ship with a lock
   * file to avoid merge conflicts downstream, meaning that if a project is
   * installed with an older version of Composer the scaffolding of Drupal will
   * not be triggered. We check this here instead of in drupal-scaffold to be
   * able to give immediate feedback to the end user, rather than failing the
   * installation after going through the lengthy process of compiling and
   * downloading the Composer dependencies.
   *
   * @see https://github.com/composer/composer/pull/5035
   */
  public static function checkComposerVersion(Event $event) {
    $composer = $event->getComposer();
    $io = $event->getIO();

    $version = $composer::VERSION;

    // The dev-channel of composer uses the git revision as version number,
    // try to the branch alias instead.
    if (preg_match('/^[0-9a-f]{40}$/i', $version)) {
      $version = $composer::BRANCH_ALIAS_VERSION;
    }

    // If Composer is installed through git we have no easy way to determine if
    // it is new enough, just display a warning.
    if ($version === '@package_version@' || $version === '@package_branch_alias_version@') {
      $io->writeError('<warning>You are running a development version of Composer. If you experience problems, please update Composer to the latest stable version.</warning>');
    }
    elseif (Comparator::lessThan($version, '1.0.0')) {
      $io->writeError('<error>Drupal-project requires Composer version 1.0.0 or higher. Please update your Composer before continuing</error>.');
      exit(1);
    }
  }

}
