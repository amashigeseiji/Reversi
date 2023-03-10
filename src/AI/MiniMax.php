<?php
namespace Tenjuu99\Reversi\AI;

use Tenjuu99\Reversi\Model\Game;
use Tenjuu99\Reversi\Model\Player;

class MiniMax extends AbstractGameTree implements ThinkInterface
{
    protected int $nodeCount = 0;
    private Player $player;
    protected array $score = ['score', 'cornerPoint', 'moveCount'];

    public function choice(Game $game) : string
    {
        $this->nodeCount = 0;
        $this->player = $game->getCurrentPlayer();
        $choice = $this->miniMax($game, $this->searchLevel, true);
        return $choice;
    }

    /**
     * @param Game $game
     * @param int $depth 探索する深さ
     * @param bool $flag 評価側を true, 敵側を false
     */
    public function miniMax(Game $game, int $depth, bool $flag)
    {
        $this->nodeCount++;
        if ($depth === 0 || $game->isGameEnd) {
            // score を返す
            return Evaluator::score($game, $this->player, $this->score);
        }
        $value = $flag ? PHP_INT_MIN : PHP_INT_MAX;
        $bestIndex = null;

        $moves = $game->moves()->getAll();
        if (count($moves) === 0) {
            $moves['pass'] = 'pass';
        }
        foreach ($moves as $index => $move) {
            $childValue = $this->miniMax($game->node($index), $depth - 1, !$flag);
            if ($flag) {
                if ($value <= $childValue) {
                    $value = $childValue;
                    $bestIndex = $index;
                }
            } else {
                if ($value >= $childValue) {
                    $value = $childValue;
                    $bestIndex = $index;
                }
            }
        }

        return $depth === $this->searchLevel ? $bestIndex : $value;
    }
}
