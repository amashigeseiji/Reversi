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
        $board = new Board();
        $game = new self($board, $player);
        $game->put(new Stone('4-4', Player::WHITE));
        $game->put(new Stone('4-5', Player::BLACK));
        $game->put(new Stone('5-4', Player::BLACK));
        $game->put(new Stone('5-5', Player::WHITE));

        return $game;
    }

    public function put(Stone $move)
    {
        $this->board->put($move->index, $move->player);
    }

    public function move(Move $move)
    {
        $this->board->put($move->index, $move->player);
    }

    public function moves() : array
    {
        // 現在のセルから敵の石があるセルを抽出
        $enemyCells = $this->board->filterState($this->currentPlayer->toCellState());
        var_dump(iterator_to_array($enemyCells));
        // ひとつづつ隣りあうセルをとりだす
        $nextCells = [];
        foreach ($enemyCells as $index => $cell) {
            $nextCells = array_merge($nextCells, $this->board->getNextEmptyCells($cell));
        }
        $moves = [];
        // foreach ($nextCells as $cell) {
        //     if ($this->canMove($cell, $this->currentPlayer)) {
        //     }
        // }
        // $cells = array_map(fn($cell) => $cell->index, $nextCells);
        sort($nextCells);
        // sort($nextCells);
        var_dump($nextCells);
        return $nextCells;
    }

    private function canMove(CellWithState $index, Player $player) : bool
    {
        return false;
    }

    public function cells() : Board
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
