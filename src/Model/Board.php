<?php
namespace Tenjuu99\Reversi\Model;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use Traversable;

class Board implements ArrayAccess, IteratorAggregate
{
    private array $cells;

    public int $xMax = 8;
    public int $yMax = 8;

    public function __construct(int $xMax = 8, int $yMax = 8)
    {
        $this->xMax = $xMax;
        $this->yMax = $yMax;
        $cells = [];
        for ($x = 1; $x <= $xMax; $x++) {
          for ($y = 1; $y <= $yMax; $y++) {
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

    public function empties(): array
    {
        return $this->filterState(CellState::EMPTY);
    }

    public function whites(): array
    {
        return $this->filterState(CellState::WHITE);
    }

    public function blacks(): array
    {
        return $this->filterState(CellState::BLACK);
    }

    private function filterState(CellState $state) : array
    {
        return array_filter($this->cells, fn($cell) => $cell->state === $state);
    }

    public function hash(): string
    {
        $string = json_encode($this->cells);
        return md5($string);
    }

    public function json() : string
    {
        return json_encode($this->cells);
    }
}
