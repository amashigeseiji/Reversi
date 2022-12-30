<?php
namespace Tenjuu99\Reversi\Model;

class Game
{
    private Board $board;
    private Player $currentPlayer;

    private function __construct(Board $board, Player $currentPlayer)
    {
        $this->board = $board;
        $this->currentPlayer = $currentPlayer;
    }

    public static function initialize(Player $player) : self
    {
        $board = new Board();
        $board->put('4-4', Player::WHITE);
        $board->put('4-5', Player::BLACK);
        $board->put('5-4', Player::BLACK);
        $board->put('5-5', Player::WHITE);
        $game = new self($board, $player);

        return $game;
    }

    public function move(string $index) : bool
    {
        $move = $this->moves()[$index];
        if ($move) {
            $move->execute();
            return true;
        }
        return false;
    }

    /**
     * @return Moves
     */
    public function moves() : Moves
    {
        return new Moves($this->board, $this->currentPlayer);
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
