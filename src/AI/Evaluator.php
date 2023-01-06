<?php
namespace Tenjuu99\Reversi\AI;

use Tenjuu99\Reversi\Model\Game;
use Tenjuu99\Reversi\Model\Board;
use Tenjuu99\Reversi\Model\GameState;
use Tenjuu99\Reversi\Model\Player;

class Evaluator
{
    public static function score(Game $game, Player $player)
    {
        return self::calc($game, $player);
    }

    public static function calc(Game $game, Player $player): int
    {
        $white = count($game->board()->whites());
        $black = count($game->board()->blacks());
        $gameEnd = $game->isGameEnd();
        switch ($game->state()) {
        case GameState::WIN_WHITE:
            return $player === Player::WHITE ? 500000 : -500000;
        case GameState::WIN_BLACK:
            return $player === Player::WHITE ? -500000 : 500000;
        case GameState::DRAW:
            return 0;
        default:
            return $player === Player::WHITE ? $white - $black : $black - $white;
        }
    }
}
