<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Tenjuu99\Reversi\Renderer\Cli;

$numberOfTrial = 100;
$boardSizeX = 8;
$boardSizeY = 8;
$strategy = [];

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
    if (strpos($arg, 'bstrategy=') === 0) {
        [$strategyName, $searchLevel] = explode(',', str_replace('bstrategy=', '', $arg));
        $strategy[] = [
            'strategy' => $strategyName,
            'searchLevel' => $searchLevel ?: 2,
            'player' => 'black',
        ];
    }
    if (strpos($arg, '--bs=') === 0) {
        [$strategyName, $searchLevel] = explode(',', str_replace('--bs=', '', $arg));
        $strategy[] = [
            'strategy' => $strategyName,
            'searchLevel' => $searchLevel ?: 2,
            'player' => 'black',
        ];
    }
    if (strpos($arg, 'wstrategy=') === 0) {
        [$strategyName, $searchLevel] = explode(',', str_replace('wstrategy=', '', $arg));
        $strategy[] = [
            'strategy' => $strategyName,
            'searchLevel' => $searchLevel ?: 2,
            'player' => 'white',
        ];
    }
    if (strpos($arg, '--ws=') === 0) {
        [$strategyName, $searchLevel] = explode(',', str_replace('--ws=', '', $arg));
        $strategy[] = [
            'strategy' => $strategyName,
            'searchLevel' => $searchLevel ?: 2,
            'player' => 'white',
        ];
    }
}

$cli = new Cli($boardSizeX, $boardSizeY);
$cli->benchmark($numberOfTrial, $strategy);
