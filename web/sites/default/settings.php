<?php

/**
 * @file
 * Drupal site-specific configuration file.
 */

$site_name = basename($site_path);
$config_dir = $app_root . '/../config/drupal/' . $site_name . '/';

if (isset($_ENV["DRUPAL_ENVIRONMENT"])) {
  $env = $_ENV["DRUPAL_ENVIRONMENT"];
}

/**
 * Require default configuration files.
 */
require_once $config_dir . 'settings.database.default.php';
require_once $config_dir . 'settings.default.php';

/**
 * Require environment-specific configuration files.
 */
if (isset($env)) {
  if (file_exists($config_dir . 'settings.database.' . $env . '.php')) {
    require_once $config_dir . 'settings.database.' . $env . '.php';
  }
  if (file_exists($config_dir . 'settings.' . $env . '.php')) {
    require_once $config_dir . 'settings.' . $env . '.php';
  }
}

/**
 * Load services definition files.
 */
if (file_exists($config_dir . 'services.default.yml')) {
  $settings['container_yamls'][] = $config_dir . 'services.default.yml';
}
if (file_exists($config_dir . 'services.' . $env . 'yml')) {
  $settings['container_yamls'][] = $config_dir . 'services.' . $env . 'yml';
}

/**
 * Salt for one-time login links, cancel links, form tokens, etc.
 */
if (isset($_ENV["DRUPAL_SALT"])) {
  $settings['hash_salt'] = $_ENV["DRUPAL_SALT"];
} elseif (file_exists($config_dir . 'salt.txt')) {
  $settings['hash_salt'] = file_get_contents($config_dir . 'salt.txt');
} else {
  $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $charactersLength = strlen($characters);
  $randomString = '';
  for ($i = 0; $i < $length; $i++) {
    $randomString .= $characters[rand(0, $charactersLength - 1)];
  }
  $salt = fopen($config_dir . 'salt.txt', 'w');
  fwrite($salt, $randomString);
  fclose($salt);
  $settings['hash_salt'] = file_get_contents($config_dir . 'salt.txt');
}


/**
 * Load local development override configuration, if available.
 *
 * Use settings.local.php to override variables on secondary (staging,
 * development, etc) installations of this site. Typically used to disable
 * caching, JavaScript/CSS compression, re-routing of outgoing emails, and
 * other things that should not happen on development and testing sites.
 *
 * Keep this code block at the end of this file to take full effect.
 */
if (file_exists($app_root . '/' . $site_path . '/settings.local.php')) {
 include $app_root . '/' . $site_path . '/settings.local.php';
}
