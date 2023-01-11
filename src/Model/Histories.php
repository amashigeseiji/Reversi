<?php
namespace Tenjuu99\Reversi\Model;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

class Histories implements IteratorAggregate
{
    /** @var History[] */
    private array $history = [];
    private int $historyMax = 100;

    public function push(History $history)
    {
        $this->history[$history->hash] = $history;
        if (count($this->history) > $this->historyMax) {
            array_shift($this->history);
        }
        if (isset($_SESSION['history'])) {
            $_SESSION['history'] = $this;
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

    public function last(): ?History
    {
        $history = end($this->history);
        reset($this->history);
        return $history ?: null;
    }

    public function getIterator() : Traversable
    {
        return new ArrayIterator($this->history);
    }

    public function clear()
    {
        $this->history = [];
    }
}
