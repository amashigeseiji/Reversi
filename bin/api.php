<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Tenjuu99\Reversi\Renderer\Api;

if (!defined('DEBUG')) {
  define('DEBUG', getenv('DEBUG'));
}
$cli = new Api();
$cli->handle($_SERVER);
