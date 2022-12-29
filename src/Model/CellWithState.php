<?php
namespace Tenjuu99\Reversi\Model;

class CellWithState extends Cell
{
    public readonly int $x;
    public readonly int $y;
    public readonly string $index;
    public readonly Cell $cell;
    public readonly CellState $state;

    public function __construct(Cell $cell, CellState $state)
    {
        $this->x = $cell->x;
        $this->y = $cell->y;
        $this->index = $cell->index;
        $this->cell = $cell;
        $this->state = $state;
    }
}
