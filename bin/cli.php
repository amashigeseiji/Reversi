<?php
require_once __DIR__ . '/../vendor/autoload.php';

$term = false;
pcntl_async_signals(true);
$signalHandler = function ($signo, $siginfo) use (&$term) {
    $term = true;
    exit(0);
};
pcntl_signal(SIGTERM, $signalHandler);
pcntl_signal(SIGINT, $signalHandler);

use Tenjuu99\Reversi\Renderer\Cli;
$boardSizeX = 8;
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
