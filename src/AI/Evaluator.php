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
        $score = 0;
        foreach ($scoreMethod as $method) {
            if ($method === 'calc') {
                $score += self::calc($game, $player);
            } elseif ($method === 'cornerPoint') {
                $score += self::cornerPoint($game, $player);
            } elseif ($method === 'moveCount') {
                $score += self::moveCount($game, $player);
            } elseif ($method === 'winOrLose') {
                $score += self::winOrLose($game, $player);
            }
        }
        return $score;
    }

    public static function winOrLose(Game $game, Player $player): int
    {
        switch ($game->state) {
        case GameState::WIN_WHITE:
            return $player === Player::WHITE ? 50000 : -50000;
        case GameState::WIN_BLACK:
            return $player === Player::WHITE ? -50000 : 50000;
        case GameState::DRAW:
            return 0;
        default:
            return 0;
        }
    }

    public static function calc(Game $game, Player $player): int
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

    public static function cornerPoint(Game $game, Player $player) : int
    {
        $score = 0;
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

    public static function moveCount(Game $game, Player $player): int
    {
        $score = 0;
        $moves = $game->moves();
        $isPlayer = $player === $game->getCurrentPlayer();
        $moveCount = count($moves->getAll());
        switch ($moveCount) {
        case 0:
            $score += !$isPlayer ? 50 : -50;
            break;
        case 1:
            $score += !$isPlayer ? 30 : -30;
            break;
        case 2:
            $score += !$isPlayer ? 10 : -10;
            break;
        }
        return $score;
    }
}
