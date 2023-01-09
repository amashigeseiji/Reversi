<?php
namespace Tenjuu99\Reversi\Model;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use Traversable;

/**
 * イミュータブル
 */
class Board implements ArrayAccess, IteratorAggregate
{
    private readonly array $cells;

    public readonly int $xMax;
    public readonly int $yMax;
    public readonly array $white;
    public readonly array $black;
    public readonly array $empties;

    public function __construct(int $xMax = 8, int $yMax = 8, array $white = [], array $black = [])
    {
        $this->xMax = $xMax;
        $this->yMax = $yMax;
        sort($white);
        sort($black);
        $this->white = $white;
        $this->black = $black;
        $cells = [];
        $columns = range(1, $xMax);
        $rows = range(1, $yMax);
        foreach ($columns as $x) {
            foreach ($rows as $y) {
                $cell = new Cell($x, $y, $this);
                $cells[$cell->index] = $cell;
            }
        }
        // cell は本来イミュータブルオブジェクトにできるが、
        // 計算コストがちょっと増えてしまうため変更可になっている。ここでしか state のセットはしていない。
        foreach ($white as $index) {
            $cells[$index]->state = CellState::WHITE;
        }
        foreach ($black as $index) {
            $cells[$index]->state = CellState::BLACK;
        }
        $this->empties = array_filter($cells, fn($cell) => $cell->state === CellState::EMPTY);
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
            CellState::WHITE->value => $this->white,
            CellState::BLACK->value => $this->black,
        ];
    }

    public static function fromArray(array $array) : self
    {
        $board = new self($array['xMax'], $array['yMax'], $array['white'], $array['black']);
        return $board;
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
