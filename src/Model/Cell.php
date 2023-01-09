<?php
namespace Tenjuu99\Reversi\Model;

class Cell
{
    public readonly int $x;
    public readonly int $y;
    public readonly string $index;
    public CellState $state = CellState::EMPTY;
    const SEPARATOR = '-';

    private Board $board;

    public function __construct(int $x, int $y, Board $board)
    {
        $this->x = $x;
        $this->y = $y;
        $this->index = $x . self::SEPARATOR . $y;
        $this->board = $board;
    }

    public function right() : ?Cell
    {
        if ($this->x === $this->board->xMax) {
            return null;
        }
        $index = $this->x +1 . self::SEPARATOR . $this->y;
        return $this->board[$index];
    }

    public function left() : ?Cell
    {
        if ($this->x === 1) {
            return null;
        }
        $index = $this->x -1 . self::SEPARATOR . $this->y;
        return $this->board[$index];
    }

    public function upper() : ?Cell
    {
        if ($this->y === 1) {
            return null;
        }
        $index = $this->x . self::SEPARATOR . $this->y -1;
        return $this->board[$index];
    }

    public function lower() : ?Cell
    {
        if ($this->y === $this->board->yMax) {
            return null;
        }
        $index = $this->x . self::SEPARATOR . $this->y +1;
        return $this->board[$index];
    }

    public function upperRight() : ?Cell
    {
        if ($this->y === 1 || $this->x === $this->board->xMax) {
            return null;
        }
        $index = $this->x +1 . self::SEPARATOR . $this->y -1;
        return $this->board[$index];
    }

    public function lowerRight() : ?Cell
    {
        if ($this->y === $this->board->yMax || $this->x === $this->board->xMax) {
            return null;
        }
        $index = $this->x +1 . self::SEPARATOR . $this->y +1;
        return $this->board[$index];
    }

    public function upperLeft() : ?Cell
    {
        if ($this->y === 1 || $this->x === 1) {
            return null;
        }
        $index = $this->x -1 . self::SEPARATOR . $this->y -1;
        return $this->board[$index];
    }

    public function lowerLeft() : ?Cell
    {
        if ($this->y === $this->board->yMax || $this->x === 1) {
            return null;
        }
        $index = $this->x -1 . self::SEPARATOR . $this->y +1;
        return $this->board[$index];
    }
}
