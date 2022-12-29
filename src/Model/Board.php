<?php
namespace Tenjuu99\Reversi\Model;

use ArrayAccess;
use IteratorAggregate;
use Traversable;

class Board implements ArrayAccess, IteratorAggregate
{
    private array $board;

    private function __construct(array $board)
    {
        $this->board = $board;
    }

    public static function initialize()
    {
        $board = [];
        for ($x = 1; $x <= 8; $x++) {
          for ($y = 1; $y <= 8; $y++) {
              $index = $x . Game::SEPARATOR . $y;
              $board[$index] = null;
          }
        }
        return new self($board);
    }

    public function offsetExists($offset): bool
    {
        var_dump($this->board);
        return isset($this->board[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        if (!$this->offsetExists($offset)) {
            throw new \Exception('Invalid offset ' . $offset . ' is given.');
        }
        return $this->board[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        if (!$this->offsetExists($offset)) {
            throw new \Exception('Invalid offset ' . $offset . ' is given.');
        }
        $this->board[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        throw new \Exception('Do not use offsetUnset.');
    }

    public function getIterator() : Traversable
    {
        return new ArrayIterator($this->board);
    }
}
