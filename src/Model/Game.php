<?php
namespace Tenjuu99\Reversi\Model;

class Game
{
    private Board $board;
    private Player $currentPlayer;
    /** @var Moves[] */
    private array $moves;
    /**
     * boardをハッシュ化した値
     * 初期化、一手うったタイミングで計算しなおす
     */
    private string $boardHash;

    private function __construct(Board $board, Player $currentPlayer)
    {
        $this->board = $board;
        $this->currentPlayer = $currentPlayer;
        $this->boardHash = $board->hash();
    }

    public static function initialize(Player $player) : self
    {
        $board = new Board();
        $board['4-4']->put(CellState::WHITE);
        $board['4-5']->put(CellState::BLACK);
        $board['5-4']->put(CellState::BLACK);
        $board['5-5']->put(CellState::WHITE);
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
        $this->next();
        // ハッシュ値の再計算
        $this->boardHash = $this->board->hash();
        return true;
    }

    /**
     * @return Moves
     */
    public function moves() : Moves
    {
        return $this->getMoves($this->board, $this->currentPlayer);
    }

    private function getMoves(Board $board, Player $player) : Moves
    {
        $hash = $this->boardHash . $player->name;
        if (isset($this->moves[$hash])) {
            return $this->moves[$hash];
        }
        $moves = new Moves($board, $player);
        return $this->moves[$hash] = $moves;
    }

    public function cells() : Board
    {
        return $this->board;
    }

    public function getPlayer() : Player
    {
        return $this->currentPlayer;
    }

    public function next()
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
        $moves = $this->getMoves($this->board, $this->currentPlayer);
        if ($moves->count() > 0) {
            return false;
        }
        $enemyMoves = $this->getMoves($this->board, $this->currentPlayer->enemy());
        if ($enemyMoves->count() > 0) {
            return false;
        }
        return true;
    }
}
