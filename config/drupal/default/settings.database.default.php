<?php

/**
 * @file
 * Drupal site-specific database configuration for all environments.
 */

$databases['default']['default'] = array(
  'database' => isset($_ENV["DB_NAME"]) ? $_ENV["DB_NAME"] : 'drupal',
  'username' => isset($_ENV["DB_USER"]) ? $_ENV["DB_USER"] : 'drupal',
  'password' => isset($_ENV["DB_PASSWORD"]) ? $_ENV["DB_PASSWORD"] : 'password',
  'prefix' => isset($_ENV["DB_PREFIX"]) ? $_ENV["DB_PREFIX"] : '',
  'host' => isset($_ENV["DB_HOST"]) ? $_ENV["DB_HOST"] : 'mariadb',
  'port' => isset($_ENV["DB_PORT"]) ? $_ENV["DB_PORT"] : '3306',
  'driver' => isset($_ENV["DB_DRIVER"]) ? $_ENV["DB_DRIVER"] : 'mysql',
);
