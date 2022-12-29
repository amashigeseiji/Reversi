<?php
namespace Tenjuu99\Reversi\Model;

use ArrayAccess;
use ArrayIterator;
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

    public function put($index, Player $player)
    {
        if (!$this->offsetExists($index)) {
            throw new \Exception('Invalid cell ' . $index);
        }
        $this->offsetSet($index, $player->name);
    }

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->board);
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

    public static function getNextCells(string $index) : array
    {
        [$x, $y] = explode(Game::SEPARATOR, $index);
        $indices = [
            [$x + 1, $y],
            [$x - 1, $y],
            [$x, $y + 1],
            [$x, $y - 1],
            [$x + 1, $y + 1],
            [$x - 1, $y - 1],
            [$x + 1, $y - 1],
            [$x - 1, $y + 1],
        ];
        $indices = array_filter($indices, function ($index) {
          return $index[0] > 0 && $index[0] <= 8 && $index[1] > 0 && $index[1] <= 8;
        });
        return array_map(fn($index) => implode(Game::SEPARATOR, $index), $indices);
    }

    public function getNextEmptyCells(string $index) : array
    {
        return array_filter(self::getNextCells($index), function ($i) {
            return $this->board[$i];
        });
    }
}
