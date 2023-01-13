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
                'right' => self::name($x + 1, $y),
                'left' => self::name($x - 1, $y),
                'upper' => self::name($x, $y - 1),
                'lower' => self::name($x, $y + 1),
                'upperRight' => self::name($x + 1, $y - 1),
                'upperLeft' => self::name($x - 1, $y - 1),
                'lowerRight' => self::name($x + 1, $y + 1),
                'lowerLeft' => self::name($x - 1, $y + 1),
            ];
            if ($x === 1) {
                unset($orientations['left'], $orientations['upperLeft'], $orientations['lowerLeft']);
            } elseif ($x === $board->xMax) {
                unset($orientations['right'], $orientations['upperRight'], $orientations['lowerRight']);
            }
            if ($y === 1) {
                unset($orientations['upper']);
                if (isset($orientations['upperRight'])) {
                    unset($orientations['upperRight']);
                }
                if (isset($orientations['upperLeft'])) {
                    unset($orientations['upperLeft']);
                }
            } elseif ($y === $board->yMax) {
                unset($orientations['lower']);
                if (isset($orientations['lowerRight'])) {
                    unset($orientations['lowerRight']);
                }
                if (isset($orientations['lowerLeft'])) {
                    unset($orientations['lowerLeft']);
                }
            }
            self::$allOrientations[$this->index] = $orientations;
        }
        $this->orientations = $orientations;
    }

    public function right() : ?Cell
    {
        if (!isset($this->orientations['right'])) {
            return null;
        }
        return $this->board[$this->orientations['right']];
    }

    public function left() : ?Cell
    {
        if (!isset($this->orientations['left'])) {
            return null;
        }
        return $this->board[$this->orientations['left']];
    }

    public function upper() : ?Cell
    {
        if (!isset($this->orientations['upper'])) {
            return null;
        }
        return $this->board[$this->orientations['upper']];
    }

    public function lower() : ?Cell
    {
        if (!isset($this->orientations['lower'])) {
            return null;
        }
        return $this->board[$this->orientations['lower']];
    }

    public function upperRight() : ?Cell
    {
        if (!isset($this->orientations['upperRight'])) {
            return null;
        }
        return $this->board[$this->orientations['upperRight']];
    }

    public function lowerRight() : ?Cell
    {
        if (!isset($this->orientations['lowerRight'])) {
            return null;
        }
        return $this->board[$this->orientations['lowerRight']];
    }

    public function upperLeft() : ?Cell
    {
        if (!isset($this->orientations['upperLeft'])) {
            return null;
        }
        return $this->board[$this->orientations['upperLeft']];
    }

    public function lowerLeft() : ?Cell
    {
        if (!isset($this->orientations['lowerLeft'])) {
            return null;
        }
        return $this->board[$this->orientations['lowerLeft']];
    }

    private static function name(int $x, int $y) : string
    {
        return $x . self::SEPARATOR . $y;
    }
}
