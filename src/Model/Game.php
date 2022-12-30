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
        if (!isset($moves[$index])) {
            return false;
        }
        $moves[$index]->execute();
        return true;
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

    public function state() : GameState
    {
        if (!$this->isGameEnd()) {
            return GameState::ONGOING;
        }
        $white = count($this->board->filterState(CellState::WHITE));
        $black = count($this->board->filterState(CellState::BLACK));
        if ($white > $black) {
            return GameState::WIN_WHITE;
        } elseif ($white < $black) {
            return GameState::WIN_BLACK;
        } else {
            return GameState::DRAW;
        }
    }

    private function isGameEnd() : bool
    {
        // todo cache
        $moves = new Moves($this->board, $this->currentPlayer);
        if ($moves->count() > 0) {
            return false;
        }
        $enemyMoves = new Moves($this->board, $this->currentPlayer->enemy());
        if ($enemyMoves->count() > 0) {
            return false;
        }
        return true;
    }
}
