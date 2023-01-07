<?php
namespace Tenjuu99\Reversi\AI;

use Tenjuu99\Reversi\Model\Game;
use Tenjuu99\Reversi\Model\Move;
use Tenjuu99\Reversi\Model\Player;

class MiniMax implements ThinkInterface, GameTreeInterface
{
    private $searchLevel = 2;
    private Player $player;

    public function choice(Game $game) : string
    {
        $this->player = $game->getCurrentPlayer();
        $choice = $this->miniMax($game, $this->searchLevel, true);
        return $choice;
    }

    public function searchLevel(int $searchLevel) : void
    {
        $this->searchLevel = $searchLevel;
    }

    /**
     * @param Game $game
     * @param int $depth 探索する深さ
     * @param bool $flag 評価側を true, 敵側を false
     */
    public function miniMax(Game $game, int $depth, bool $flag)
    {
        if ($depth === 0 || $game->isGameEnd()) {
            // score を返す
            return Evaluator::score($game, $this->player);
        }
        $value = $flag ? PHP_INT_MIN : PHP_INT_MAX;
        $bestIndex = null;

        $moves = $game->moves();
        if (!$moves) {
            $moves['pass'] = 'pass';
        }
        foreach ($moves as $index => $move) {
            $gameNode = $game->node($index);
            $childValue = $this->miniMax($gameNode, $depth - 1, !$flag);
            $condition = $flag ? $value <= $childValue : $value >= $childValue;
            if ($condition) {
                $value = $childValue;
                $bestIndex = $index;
            }
        }

        return $depth === $this->searchLevel ? $bestIndex : $value;
    }
}
