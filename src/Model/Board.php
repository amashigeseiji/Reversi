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
    private array $white = [];
    private array $black = [];

    public function __construct(int $xMax = 8, int $yMax = 8, array $white = [], array $black = [])
    {
        $this->xMax = $xMax;
        $this->yMax = $yMax;
        $this->white = $white;
        $this->black = $black;
        $cells = [];
        for ($x = 1; $x <= $xMax; $x++) {
          for ($y = 1; $y <= $yMax; $y++) {
              $cell = new Cell($x, $y, $this);
              $cells[$cell->index] = $cell;
          }
        }
        foreach ($white as $index) {
            $cells[$index]->state = CellState::WHITE;
        }
        foreach ($black as $index) {
            $cells[$index]->state = CellState::BLACK;
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
        return array_filter($this->cells, fn($cell) => $cell->state === CellState::EMPTY);
    }

    public function whites(): array
    {
        return $this->white;
    }

    public function blacks(): array
    {
        return $this->black;
    }

    public function hash(): string
    {
        $string = json_encode($this->toArray());
        return md5($string);
    }

    public function json() : string
    {
        return json_encode($this->toArray());
    }

    public function toArray(): array
    {
        return [
            'xMax' => $this->xMax,
            'yMax' => $this->yMax,
            CellState::WHITE->value => array_values($this->white),
            CellState::BLACK->value => array_values($this->black),
        ];
    }

    public static function fromArray(array $array) : self
    {
        $board = new self($array['xMax'], $array['yMax'], $array['white'], $array['black']);
        return $board;
    }

    public function put(string $index, CellState $cellState)
    {
        if (!isset($this->cells[$index])) {
            throw new \Exception();
        }
        $this->cells[$index]->state = $cellState;
        if ($cellState === CellState::WHITE) {
            $this->white[] = $index;
        } elseif ($cellState === CellState::BLACK) {
            $this->black[] = $index;
        }
    }

    public function getPlayersCells(Player $player) : array
    {
        return $player === Player::WHITE  ? $this->white : $this->black;
    }

    public function corner() : array
    {
        return [
            '1-1',
            "1-{$this->yMax}",
            "{$this->xMax}-1",
            "{$this->xMax}-{$this->yMax}",
        ];
    }
}
