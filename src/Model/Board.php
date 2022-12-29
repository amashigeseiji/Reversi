<?php
namespace Tenjuu99\Reversi\Model;

use ArrayAccess;
use IteratorAggregate;
use Traversable;

class Board implements ArrayAccess, IteratorAggregate
{
    private array $cells;
    private array $state;

    public function __construct()
    {
        $cells = [];
        $state = [];
        for ($x = 1; $x <= 8; $x++) {
          for ($y = 1; $y <= 8; $y++) {
              $cell = new Cell($x, $y);
              $cells[$cell->index] = $cell;
              $state[$cell->index] = CellState::EMPTY;
          }
        }
        $this->cells = $cells;
        $this->state = $state;
    }

    public function put(string $index, Player $player)
    {
        if (!$this->offsetExists($index)) {
            throw new \Exception('Invalid index ' . $index);
        }
        $this->state[$index] = $player === Player::WHITE
            ? CellState::WHITE
            : CellState::BLACK;
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
        return new CellWithState($this->cells[$offset], $this->state[$offset]);
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
        foreach ($this->cells as $index => $cell) {
            yield $index => new CellWithState($cell, $this->state[$index]);
        }
    }

    public function filterState(CellState $state) : Traversable
    {
        $cells = array_filter($this->state, fn($cellState) => $cellState === $state);
        foreach ($cells as $index => $cell) {
            yield $index => new CellWithState($this->cells[$index], $cell);
        }
    }

    public function filterByIndices(array $indices) : Traversable
    {
        foreach ($indices as $index) {
            if ($this->offsetExists($index)) {
                yield $index => new CellWithState($this->cells[$index], $this->state[$index]);
            }
        }
    }

    public function getNextEmptyCells(Cell $cell)
    {
        return array_filter($cell->getNextCells(), function ($index) {
            return $this->state[$index] === CellState::EMPTY;
        });
    }
}
