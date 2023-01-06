<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Tenjuu99\Reversi\Renderer\Cli;
$boardSizeX = 8;
$boardSizeY = 8;
foreach ($argv as $arg) {
    if (strpos($arg, 'x=') === 0) {
        $boardSizeX = ltrim($arg, 'x=');
    }
    if (strpos($arg, 'y=') === 0) {
        $boardSizeY = ltrim($arg, 'y=');
    }
}
$cli = new Cli($boardSizeX, $boardSizeY);
$cli->play();
