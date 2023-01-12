<?php
namespace Tenjuu99\Reversi\AI;

use Traversable;
use Tenjuu99\Reversi\Model\Game;
class AbstractGameTree implements GameTreeInterface
{
    protected int $nodeCount = 0;
    protected int $searchLevel = 4;

    public function searchLevel(int $searchLevel) : void
    {
        if ($searchLevel > 0) {
            $this->searchLevel = $searchLevel;
        }
    }

    public function nodeCount(): int
    {
        return $this->nodeCount;
    }

    protected function expandNode(Game $game, ?callable $sort = null): Traversable
    {
        $moves = $game->moves();
        if (!$moves->hasMoves()) {
            $this->nodeCount++;
            yield 'pass' => $this->node($game, 'pass');
        } else {
            $moves = $sort ? $sort($moves) : $moves->getAll();
            foreach ($moves as $index => $move) {
                $this->nodeCount++;
                yield $index => $this->node($game, $index);
            }
        }
    }

    /**
     * ゲーム木の子ノードを生成する
     * @param string $index 手もしくはパス
     * @return Game
     */
    public function node(Game $game, string $index) : Game
    {
        $moves = $game->moves();
        $moveCount = $game->moveCount() + 1;
        $player = $game->getCurrentPlayer();
        $board = $index === 'pass'
            ? $game->board()
            : $moves[$index]->newState($game->board(), $player);
        $nextPlayer = $player->enemy();
        return new Game($board, $nextPlayer, $moveCount);
    }
}
