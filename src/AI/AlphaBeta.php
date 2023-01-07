<?php
namespace Tenjuu99\Reversi\AI;

use Tenjuu99\Reversi\Model\Game;
use Tenjuu99\Reversi\Model\Player;

class AlphaBeta implements ThinkInterface, GameTreeInterface
{
    private $searchLevel = 2;
    private Player $player;

    public function choice(Game $game) : string
    {
        $this->player = $game->getCurrentPlayer();
        $choice = $this->alphaBeta($game, $this->searchLevel, true, PHP_INT_MIN, PHP_INT_MAX);
        return $choice;
    }

    public function searchLevel(int $searchLevel) : void
    {
        if ($searchLevel > 0) {
            $this->searchLevel = $searchLevel;
        }
    }

    /**
     * @param Game $game
     * @param int $depth 探索する深さ
     * @param bool $flag 評価側を true, 敵側を false
     */
    public function alphaBeta(Game $game, int $depth, bool $flag, int $alpha, int $beta)
    {
        if ($depth === 0 || $game->isGameEnd()) {
            // score を返す
            return Evaluator::score($game, $this->player);
        }
        $value = 0;
        $bestIndex = null;

        foreach ($game->expandNode() as $index => $node) {
            if (!$bestIndex) {
                $bestIndex = $index;
            }
            $childValue = $this->alphaBeta($node, $depth - 1, !$flag, $alpha, $beta);

            if ($flag) { // AIのノードの場合
                // 子ノードのなかでおおきい値を取得する
                if ($childValue >= $value) {
                    $value = $childValue;
                    $alpha = $value;
                    $bestIndex = $index;
                }
                // ベータカット
                if ($value >= $beta) {
                    if ($depth === $this->searchLevel) {
                        break;
                    } else {
                        return $value;
                    }
                }
            } else { // 敵のノードの場合
                // 子ノードのなかで小さい値を取得する
                if ($childValue < $value) {
                    $value = $childValue;
                    $beta = $value;
                    $bestIndex = $index;
                }
                if ($value <= $alpha) {
                    return $value;
                }
            }
        }

        if ($depth === $this->searchLevel) {
            return $bestIndex;
        }
        return $flag ? $alpha : $beta;
    }
}
