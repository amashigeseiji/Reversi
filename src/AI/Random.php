<?php
namespace Tenjuu99\Reversi\AI;

use Tenjuu99\Reversi\Model\Game;
use Tenjuu99\Reversi\Model\Move;

class Random implements ThinkInterface
{
    public function choice(Game $game) : ?Move
    {
        $moves = iterator_to_array($game->moves());
        if ($moves) {
            $key = array_rand($moves);
            return $moves[$key];
        } else {
            return null;
        }
    }
}
