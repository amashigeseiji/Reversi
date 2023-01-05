<?php
namespace Tenjuu99\Reversi\Model;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

class Histories implements IteratorAggregate
{
    /** @var History[] */
    private array $history = [];
    private int $historyMax = 10;

    public function push(Game $game)
    {
        $history = $game->toHistory();
        $this->history[$history->hash] = $history;
        if (count($this->history) > $this->historyMax) {
            array_shift($this->history);
        }
    }

    public function has(string $hash) : bool
    {
        return isset($this->history[$hash]);
    }

    public function get(string $hash) : ?History
    {
        return $this->history[$hash] ?? null;
    }

    public function getIterator() : Traversable
    {
        return new ArrayIterator($this->history);
    }
}
