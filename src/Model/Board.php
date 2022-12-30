<?php
namespace Tenjuu99\Reversi\Model;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use Traversable;

class Board implements ArrayAccess, IteratorAggregate
{
    private array $cells;

    public function __construct()
    {
        $cells = [];
        $state = [];
        for ($x = 1; $x <= 8; $x++) {
          for ($y = 1; $y <= 8; $y++) {
              $cell = new Cell($x, $y, $this);
              $cells[$cell->index] = $cell;
          }
        }
        $this->cells = $cells;
    }

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->cells);
    }

    public function offsetGet($offset): mixed
    {
        if (!$this->offsetExists($offset)) {
            throw new \Exception('Invalid offset ' . $offset . ' is given.');
        }
        return $this->cells[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        throw new \Exception('Do not use offsetSet.');
    }

    public function offsetUnset($offset): void
    {
        throw new \Exception('Do not use offsetUnset.');
    }

    public function getIterator() : Traversable
    {
        return new ArrayIterator($this->cells);
    }

    public function filterState(CellState $state) : array
    {
        return array_filter($this->cells, fn($cell) => $cell->state === $state);
    }

    public function hash(): string
    {
        $array = $this->toArray();
        $string = json_encode($array);
        return md5($string);
    }

    private function toArray() : array
    {
        $cells = [];
        foreach ($this->cells as $index => $cell) {
            $cells[$index] = $cell->state->name;
        }
        ksort($cells);
        return $cells;
    }
}
