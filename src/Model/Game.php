<?php
namespace Tenjuu99\Reversi\Model;

class Game
{
    private Board $board;
    private Player $currentPlayer;

    public const SEPARATOR = '-';

    private function __construct(Board $board, Player $currentPlayer)
    {
        $this->board = $board;
        $this->currentPlayer = $currentPlayer;
    }

    public static function initialize(Player $player) : self
    {
        $board = Board::initialize();
        $board->put('4-4', Player::WHITE->name);
        $board->put('4-5', Player::BLACK->name);
        $board->put('5-4', Player::BLACK->name);
        $board->put('5-5', Player::WHITE->name);

        return new self($board, $player);
    }

    public function put(Move $move)
    {
        $this->board->put($move->index, $move->player);
    }

    public function moves() : array
    {
        // 現在のセルから敵の石があるセルを抽出
        $enemyCells = array_filter($this->board, fn($cell) => $cell && $cell !== $this->currentPlayer->name);
        // ひとつづつ隣りあうセルをとりだす
        foreach (array_keys($enemyCells) as $index) {
            $nextCells[$index] = $this->board->getNextEmptyCells($index);
        }
        return $nextCells;
    }

    public function getBoardState() : Board
    {
        return $this->board;
    }

    public function getPlayer() : Player
    {
        return $this->currentPlayer;
    }

    public function changePlayer()
    {
        $this->currentPlayer = $this->currentPlayer === Player::WHITE
            ? Player::BLACK
            : Player::WHITE;
    }
}
