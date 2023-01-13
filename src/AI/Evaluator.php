<?php
namespace Tenjuu99\Reversi\AI;

use Tenjuu99\Reversi\Model\Game;
use Tenjuu99\Reversi\Model\Board;
use Tenjuu99\Reversi\Model\GameState;
use Tenjuu99\Reversi\Model\Player;

class Evaluator
{
    public static function score(Game $game, Player $player, array $scoreMethod = ['calc'])
    {
        $nokori = count($game->board()->empties);
        $score = 0;
        foreach ($scoreMethod as $method) {
            $score = self::$method($score, $game, $player);
        }
        return $score;
    }

    public static function calc(int $score, Game $game, Player $player): int
    {
        $white = count($game->board()->white);
        $black = count($game->board()->black);
        switch ($game->state) {
        case GameState::WIN_WHITE:
            return $player === Player::WHITE ? 50000 : -50000;
        case GameState::WIN_BLACK:
            return $player === Player::WHITE ? -50000 : 50000;
        case GameState::DRAW:
            return 0;
        default:
            return $player === Player::WHITE ? $white - $black : $black - $white;
        }
    }

    public static function cornerPoint(int $score, Game $game, Player $player) : int
    {
        $board = $game->board();
        $corner = $board->corner();
        $players = array_intersect($corner, $board->getPlayersCells($player));
        $enemies = array_intersect($corner, $board->getPlayersCells($player->enemy()));
        if (count($players) > 0) {
            $score = $score + count($players) * 200;
        }
        if (count($enemies) > 0) {
            $score = $score - count($enemies) * 200;
        }
        return $score;
    }

    public static function moveCount(int $score, Game $game, Player $player): int
    {
        $moves = $game->moves();
        $isPlayer = $player === $game->getCurrentPlayer();
        $moveCount = count($moves->getAll());
        if ($moveCount === 0) {
            $score += $isPlayer ? 100 : -100;
        }
        $score += $isPlayer ? $moveCount * 2 : -($moveCount * 2);
        return $score;
    }
}
