<?php
namespace Tenjuu99\Reversi\AI;

use Tenjuu99\Reversi\Model\Game;
use Tenjuu99\Reversi\Model\Move;
use Tenjuu99\Reversi\Model\Moves;
use Tenjuu99\Reversi\Model\Player;

class AlphaBeta extends AbstractGameTree implements ThinkInterface
{
    private Player $player;

    protected array $score = ['calc', 'cornerPoint', 'moveCount'];
    private array $corner;
    private ?string $sortMethod = 'simpleSort';

    public function choice(Game $game) : string
    {
        $this->nodeCount = 0;
        $this->corner = $game->board()->corner();
        $this->player = $game->getCurrentPlayer();
        $nokori = count($game->board()->empties);
        if ($nokori < $this->endgameThreshold) {
            $this->searchLevel = $nokori;
            $this->score = ['winOrLose'];
            $this->sortMethod = null;
        }
        $choice = $this->alphaBeta($game, $this->searchLevel, true, PHP_INT_MIN, PHP_INT_MAX);
        return $choice;
    }

    /**
     * @param Game $game
     * @param int $depth 探索する深さ
     * @param bool $flag 評価側を true, 敵側を false
     */
    public function alphaBeta(Game $game, int $depth, bool $flag, int $alpha, int $beta)
    {
        if ($depth === 0 || $game->isGameEnd) {
            // score を返す
            return Evaluator::score($game, $this->player, $this->score);
        }
        $value = $flag ? PHP_INT_MIN : PHP_INT_MAX;
        $bestIndex = null;

        if (!$this->sortMethod) {
            if (!$flag) {
                $nodes = iterator_to_array($this->expandNode($game));
                uasort($nodes, function (Game $a, Game $b) {
                    $movesA = count($a->moves()->getAll());
                    $movesB = count($b->moves()->getAll());
                    return $movesA - $movesB;
                });
            } else {
                $nodes = $this->expandNode($game, [$this, 'simpleSort']);
            }
        } else {
            $nodes = $this->expandNode($game, [$this, $this->sortMethod]);
        }
        foreach ($nodes as $index => $node) {
            $childValue = $this->alphaBeta($node, $depth - 1, !$flag, $alpha, $beta);

            if ($flag) { // AIのノードの場合
                // 子ノードのなかでおおきい値を取得する
                if ($childValue > $value) {
                    $value = $childValue;
                    $alpha = $value;
                    $bestIndex = $index;
                }
                // ベータカット
                if ($value >= $beta) {
                    break;
                }
            } else { // 敵のノードの場合
                // 子ノードのなかで小さい値を取得する
                if ($childValue < $value) {
                    $value = $childValue;
                    $beta = $value;
                    $bestIndex = $index;
                }
                if ($value <= $alpha) {
                    break;
                }
            }
        }

        if ($depth === $this->searchLevel) {
            return $bestIndex;
        }
        return $flag ? $alpha : $beta;
    }

    // 枝刈りのための効率化
    // ノードを評価が高いであろう順にならべなおす
    public function simpleSort(Moves $moves)
    {
        $moves = $moves->getAll();
        $corner = array_flip($this->corner);
        $cornerMoves = [];
        $else = [];
        foreach ($moves as $index => $move) {
            if (isset($corner[$index])) {
                $cornerMoves[$index] = $move;
            } else {
                $else[$index] = $move;
            }
        }
        uasort($else, function (Move $a, Move $b) {
            $countA = count($a->flipCells);
            $countB = count($b->flipCells);
            if ($countA === $countB) {
                return 0;
            }
            return $countA < $countB ? 1 : -1;
        });
        return [...$cornerMoves, ...$else];
    }
}
