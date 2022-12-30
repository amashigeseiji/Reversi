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

    public function getNextCells() : array
    {
        $indices = [
            [$this->x + 1, $this->y],
            [$this->x - 1, $this->y],
            [$this->x, $this->y + 1],
            [$this->x, $this->y - 1],
            [$this->x + 1, $this->y + 1],
            [$this->x - 1, $this->y - 1],
            [$this->x + 1, $this->y - 1],
            [$this->x - 1, $this->y + 1],
        ];
        $indices = array_filter($indices, function ($index) {
          return $index[0] > 0 && $index[0] <= 8 && $index[1] > 0 && $index[1] <= 8;
        });
        return array_map(fn($index) => implode(self::SEPARATOR, $index), $indices);
    }

    public static function fromIndex(string $index): self
    {
        [$x, $y] = explode(self::SEPARATOR, $index);
        return new self($x, $y);
    }

    public function put(CellState $state)
    {
        $this->state = $state;
    }

    /**
     * @return Cell[]
     */
    public function nextCells(): array
    {
        return [
            'right' => $this->right(),
            'left' => $this->left(),
            'upper' => $this->upper(),
            'lower' => $this->lower(),
            'upperRight' => $this->upperRight(),
            'upperLeft' => $this->upperLeft(),
            'lowerRight' => $this->lowerRight(),
            'lowerLeft' => $this->lowerLeft(),
        ];
    }

    public function right() : ?Cell
    {
        if ($this->x === 8) {
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
        if ($this->y === 8) {
            return null;
        }
        $index = $this->x . self::SEPARATOR . $this->y +1;
        return $this->board[$index];
    }

    public function upperRight() : ?Cell
    {
        if ($this->y === 1 || $this->x === 8) {
            return null;
        }
        $index = $this->x +1 . self::SEPARATOR . $this->y -1;
        return $this->board[$index];
    }

    public function lowerRight() : ?Cell
    {
        if ($this->y === 8 || $this->x === 8) {
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
        if ($this->y === 8 || $this->x === 1) {
            return null;
        }
        $index = $this->x -1 . self::SEPARATOR . $this->y +1;
        return $this->board[$index];
    }

    /**
     * @return Cell[]
     */
    public function chain(string $orientation) : array
    {
        $cells = [];
        $current = $this;
        while(true) {
            $current = $current->{$orientation}();
            if (!$current) {
                break;
            }
            $cells[] = $current;
        }
        return $cells;
    }

    public function flip()
    {
        if ($this->state === CellState::EMPTY) {
            throw new \Exception('Invlid call of method');
        }
        $this->state = $this->state->flip();
    }
}
