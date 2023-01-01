<?php
namespace Tenjuu99\Reversi\AI;

use Tenjuu99\Reversi\Model\Game;
use Tenjuu99\Reversi\Model\Move;

Interface ThinkInterface
{
    public function choice(Game $game) : ?Move;
}
