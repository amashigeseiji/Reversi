<?php
namespace Tenjuu99\Reversi\Model;

class Cell
{
    public readonly int $x;
    public readonly int $y;
    public readonly string $index;
    const SEPARATOR = '-';

    public function __construct(int $x, int $y)
    {
        $this->x = $x;
        $this->y = $y;
        $this->index = $x . self::SEPARATOR . $y;
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
}
