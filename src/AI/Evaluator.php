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
        $nokori = count($game->board()->empties);
        $score = self::calc($game, $player);
        if ($nokori > 10) {
            $score = self::cornerPoint($score, $game->board(), $player);
        }
        return $score;
    }

    public static function calc(Game $game, Player $player): int
    {
        $white = count($game->board()->white);
        $black = count($game->board()->black);
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

    public static function cornerPoint(int $score, Board $board, Player $player) : int
    {
        $corner = $board->corner();
        $players = array_intersect($corner, $board->getPlayersCells($player));
        $enemies = array_intersect($corner, $board->getPlayersCells($player->enemy()));
        if (count($players) > 0) {
            $score = $score + count($players) * 20;
        }
        if (count($enemies) > 0) {
            $score = $score - count($enemies) * 20;
        }
        return $score;
    }
}
