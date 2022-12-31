<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Tenjuu99\Reversi\Renderer\Cli;

$numberOfTrial = 100;
$boardSizeX = 8;
$boardSizeY = 8;
foreach ($argv as $arg) {
    if (strpos($arg, 'x=') === 0) {
        $boardSizeX = ltrim($arg, 'x=');
    }
    if (strpos($arg, 'y=') === 0) {
        $boardSizeY = ltrim($arg, 'y=');
    }
    if (is_numeric($arg)) {
        $numberOfTrial = $arg;
    }
}
$cli = new Cli($boardSizeX, $boardSizeY);
$cli->benchmark($numberOfTrial);
