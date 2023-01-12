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
    public readonly array $orientations;
    // resize時に破棄する必要がある
    // Game::initialize 時にこのキャッシュを破棄している
    public static array $allOrientations = [];

    public function __construct(int $x, int $y, Board $board)
    {
        $this->x = $x;
        $this->y = $y;
        $this->index = $x . self::SEPARATOR . $y;
        $this->board = $board;
        if (isset(self::$allOrientations[$this->index])) {
            $orientations = self::$allOrientations[$this->index];
        } else {
            $orientations = [
                'right',
                'left',
                'upper',
                'lower',
                'upperRight',
                'upperLeft',
                'lowerRight',
                'lowerLeft',
            ];
            if ($x === 1) {
                unset($orientations[1], $orientations[5], $orientations[7]);
            } elseif ($x === $board->xMax) {
                unset($orientations[0], $orientations[4], $orientations[6]);
            }
            if ($y === 1) {
                unset($orientations[2]);
                if (isset($orientations[4])) {
                    unset($orientations[4]);
                }
                if (isset($orientations[5])) {
                    unset($orientations[5]);
                }
            } elseif ($y === $board->yMax) {
                unset($orientations[3]);
                if (isset($orientations[6])) {
                    unset($orientations[6]);
                }
                if (isset($orientations[7])) {
                    unset($orientations[7]);
                }
            }
            self::$allOrientations[$this->index] = $orientations;
        }
        $this->orientations = $orientations;
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
