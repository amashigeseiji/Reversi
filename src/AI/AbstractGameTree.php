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
            yield 'pass' => $game->node('pass');
        } else {
            $moves = $sort ? $sort($moves) : $moves->getAll();
            foreach ($moves as $index => $move) {
                $this->nodeCount++;
                yield $index => $game->node($index);
            }
        }
    }
}
