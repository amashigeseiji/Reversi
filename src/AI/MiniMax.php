<?php
namespace Tenjuu99\Reversi\AI;

use Tenjuu99\Reversi\Model\Game;
use Tenjuu99\Reversi\Model\History;
use Tenjuu99\Reversi\Model\Move;

class MiniMax implements ThinkInterface
{
    private $searchLevel = 3;
    private History $history;

    public function choice(Game $game) : ?Move
    {
        $this->history = $game->toHistory();
        $choice = $this->miniMax($game, $this->searchLevel, true);
        $game->fromHistory($this->history);
        return $choice === 'pass' ? null : $choice;
    }

    /**
     * @param Game $game
     * @param int $depth 探索する深さ
     * @param bool $flag 評価側を true, 敵側を false
     */
    public function miniMax(Game $game, int $depth, bool $flag)
    {
        if ($depth === 0) {
            // score を返す
            return Evaluator::score($game, $game->computerPlayer());
        }
        $value = $flag ? PHP_INT_MIN : PHP_INT_MAX;
        $bestIndex = null;

        $moves = $game->moves();
        if (!$moves) {
            $moves[] = 'pass';
        }
        foreach ($moves as $index => $move) {
            $history = $game->toHistory();
            if ($move === 'pass') {
                $game->next();
            } else {
                $game->move($index);
            }
            $childValue = $this->miniMax($game, $depth - 1, !$flag);
            $condition = $flag ? $value < $childValue : $value > $childValue;
            if ($condition) {
                $value = $childValue;
                $bestIndex = $move;
            }
            $game->fromHistory($history);
        }

        return $depth === $this->searchLevel ? $bestIndex : $value;
    }
}
