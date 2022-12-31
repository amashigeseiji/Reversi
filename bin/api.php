<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Tenjuu99\Reversi\Renderer\Api;

$cli = new Api();
$cli->handle($_SERVER);
