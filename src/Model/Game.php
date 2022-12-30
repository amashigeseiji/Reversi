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
        $moves = $this->moves();
        foreach ($moves as $move) {
            if ($move->index === $index) {
                $move->execute();
                return true;
            }
        }
        return false;
    }

    /**
     * @return Move[]
     */
    public function moves() : array
    {
        $moves = [];
        $empties = $this->board->filterState(CellState::EMPTY);
        foreach ($empties as $emptyCell) {
            $move = new Move($emptyCell, $this->currentPlayer);
            if (count($move->flipCells) > 0) {
                $moves[] = $move;
            }
        }
        return $moves;
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
