<?php

/**
 * @file
 * Drupal site-specific configuration file for 'local' environment.
 */

 /**
  * Access control for update.php script.
  *
  * If you are updating your Drupal installation using the update.php script but
  * are not logged in using either an account with the "Administer software
  * updates" permission or the site maintenance account (the account that was
  * created during installation), you will need to modify the access check
  * statement below. Change the FALSE to a TRUE to disable the access check.
  * After finishing the upgrade, be sure to open this file again and change the
  * TRUE back to a FALSE!
  */
 $settings['update_free_access'] = TRUE;
