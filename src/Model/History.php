<?php
namespace Tenjuu99\Reversi\Model;

class History
{
    public readonly string $hash;
    public readonly array $board;
    public readonly string $player;
    public readonly int $moveCount;

    public function __construct(Board $board, Player $currentPlayer, int $moveCount)
    {
        $object = [
            'board' => $board->toArray(),
            'player' => $currentPlayer->name,
            'moveCount' => $moveCount,
        ];
        $this->hash = md5(json_encode($object));
        $this->board = $board->toArray();
        $this->player = $currentPlayer->name;
        $this->moveCount = $moveCount;
    }
}
