<?php
namespace Tenjuu99\Reversi\Model;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

class Histories implements IteratorAggregate
{
    private array $history = [];
    private int $historyMax = 10;

    public function add(string $hash, Board $board, Player $player, int $moveCount)
    {
        $this->history[$hash] = new History($hash, $board, $player, $moveCount);
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
