<?php
namespace Tenjuu99\Reversi\AI;

use Tenjuu99\Reversi\Model\Game;
use Tenjuu99\Reversi\Model\Move;

class Random implements ThinkInterface
{
    public function choice(Game $game) : string
    {
        $moves = $game->moves();
        if ($moves->hasMoves()) {
            $indices = $moves->indices();
            $key = array_rand($indices);
            return $indices[$key];
        } else {
            return 'pass';
        }
    }
}
