<?php
namespace Tenjuu99\Reversi\AI;

use Tenjuu99\Reversi\Model\Game;
use Tenjuu99\Reversi\Model\Move;
use Tenjuu99\Reversi\Model\Moves;
use Tenjuu99\Reversi\Model\Player;
use Traversable;

class AlphaBeta extends AbstractGameTree implements ThinkInterface
{
    protected int $nodeCount = 0;
    private Player $player;

    private Game $rootNode;
    protected array $score = ['calc', 'cornerPoint', 'moveCount'];
    private array $corner;

    /**
     * choice
     *
     * @param Game $game
     * @return string
     */
    public function choice(Game $game) : string
    {
        $this->nodeCount = 0;
        $this->rootNode = $game;
        $this->corner = $game->board()->corner();
        $this->player = $game->getCurrentPlayer();
        $nokori = count($game->board()->empties);
        if ($nokori < $this->endgameThreshold) {
            $this->searchLevel = $nokori;
            $this->score = ['winOrLose'];
        }
        $choice = $this->alphaBeta($game, $this->searchLevel, true, PHP_INT_MIN, PHP_INT_MAX);
        return $choice;
    }

    /**
     * alphaBeta
     *
     * @param Game $game
     * @param int $depth
     * @param bool $flag
     * @param int $alpha
     * @param int $beta
     */
    public function alphaBeta(Game $game, int $depth, bool $flag, int $alpha, int $beta)
    {
        $this->nodeCount++;
        if ($depth === 0 || $game->isGameEnd) {
            // score を返す
            return Evaluator::score($game, $this->player, $this->score);
        }
        $value = $flag ? PHP_INT_MIN : PHP_INT_MAX;
        $bestIndex = null;

        foreach ($this->expandNode($game) as $index => $node) {
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

    /**
     * expandNode
     *
     * @param Game $game
     * @return Traversable<string, Game>
     */
    private function expandNode(Game $game): Traversable
    {
        $moves = $game->moves();
        if (!$moves->hasMoves()) {
            yield 'pass' => $game->node('pass');
        } else {
            $nokori = count($this->rootNode->board()->empties);
            $isEndgame = ($nokori - $this->endgameThreshold) < 0;
            // 終盤の相手プレイヤーの手は手が少いほうから調査する
            if ($isEndgame && $game->getCurrentPlayer() !== $this->player) {
                $nodes = [];
                foreach ($moves->getAll() as $index => $move) {
                    $nodes[$index] = $game->node($index);
                }
                uasort($nodes, function (Game $a, Game $b) {
                    $movesA = count($a->moves()->getAll());
                    $movesB = count($b->moves()->getAll());
                    return $movesA - $movesB;
                });
                foreach ($nodes as $index => $node) {
                    yield $index => $node;
                }
            } else {
                $moves = $this->simpleSort($moves);
                foreach ($moves as $index => $move) {
                    yield $index => $game->node($index);
                }
            }
        }
    }

    /**
     * 枝刈りのための効率化
     * ノードを評価が高いであろう順にならべなおす
     *
     * @return array<string, Move>
     */
    private function simpleSort(Moves $moves): array
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
