<?php
namespace Tenjuu99\Reversi\Model;

class Move
{
    public readonly Cell $cell;
    public readonly string $index;
    public readonly Player $player;

    public function __construct(Cell $cell, Player $player)
    {
        $this->cell = $cell;
        $this->index = $cell->index;
        $this->player = $player;
    }
}
