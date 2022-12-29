<?php
namespace Tenjuu99\Reversi\Model;

class Stone
{
    public readonly string $index;
    public readonly Player $player;

    public function __construct(string $index, Player $player)
    {
        $this->index = $index;
        $this->player = $player;
    }
}
