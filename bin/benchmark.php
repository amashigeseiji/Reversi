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
    $getStrategyArg = function ($arg) {
        if (preg_match('/((?P<color1>b|w)strategy|--(?P<color2>b|w)s)=(?P<arg>.*)/', $arg, $match)) {
            $color = $match['color1'] ?: $match['color2'];
            if (!$match['arg']) {
                echo 'strategy の引数が足りません';
                exit;
            }
            $strategy = explode(',', $match['arg']);
            return [
                'strategy' => $strategy[0],
                'searchLevel' => $strategy[1] ?? 2,
                'player' => $color === 'b' ? 'black' : 'white',
                'endgameThreshold' => $strategy[2] ?? 0,
            ];
        }
        return null;
    };
    if ($tmp = $getStrategyArg($arg)) {
        $strategy[] = $tmp;
    }
}

$cli = new Cli($boardSizeX, $boardSizeY);
$cli->benchmark($numberOfTrial, $strategy);
