<?php
namespace Tenjuu99\Reversi\AI;

use Tenjuu99\Reversi\Model\Game;
use Tenjuu99\Reversi\Model\History;
use Tenjuu99\Reversi\Model\Move;

class MiniMax implements ThinkInterface
{
    private $searchLevel = 4;
    private History $history;

    public function choice(Game $game) : ?Move
    {
        $this->history = $game->toHistory();
        $choice = $this->miniMax($game, $this->searchLevel, $game->computerPlayer() === $game->getCurrentPlayer());
        $game->fromHistory($this->history);
        return $choice === 'pass' ? null : $choice;
    }

    /**
     * @param Game $game
     * @param int $depth 探索する深さ
     * @param bool $flagComputerTurn コンピュータが攻めのときは true
     */
    public function miniMax(Game $game, int $depth, bool $flagComputerTurn)
    {
        if ($depth === 0) {
            // score を返す
            return Evaluator::score($game, $game->computerPlayer());
        }
        $value = $flagComputerTurn ? PHP_INT_MIN : PHP_INT_MAX;
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
            $childValue = $this->miniMax($game, $depth - 1, !$flagComputerTurn);
            $condition = $flagComputerTurn ? $value < $childValue : $value > $childValue;
            if ($condition) {
                $value = $childValue;
                $bestIndex = $move;
            }
            $game->fromHistory($history);
        }

        return $depth === $this->searchLevel ? $bestIndex : $value;
    }
}
