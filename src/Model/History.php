<?php
namespace Tenjuu99\Reversi\Model;

class History
{
    public readonly string $hash;
    public readonly array $board;
    public readonly string $player;
    public readonly int $moveCount;

    public function __construct(string $hash, Board $board, Player $currentPlayer, int $moveCount)
    {
        $this->hash = $hash;
        $this->board = $board->toArray();
        $this->player = $currentPlayer->name;
        $this->moveCount = $moveCount;
    }
}
