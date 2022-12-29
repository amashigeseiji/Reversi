<?php
namespace Tenjuu99\Reversi\Model;

class Move
{
    public readonly string $index;
    public readonly string $player;

    public function __construct(string $index, Player $player)
    {
        $this->index = $index;
        $this->player = $player->name;
    }
}
