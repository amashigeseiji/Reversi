<?php
namespace Tenjuu99\Reversi\Model;

class Game
{
    private readonly Board $board;
    private readonly Player $currentPlayer;
    private readonly Moves $moves;
    /** 何手目か */
    private readonly int $moveCount;
    public readonly GameState $state;
    public readonly bool $isGameEnd;

    private function __construct(Board $board, Player $player, int $moveCount = 0)
    {
        $this->board = $board;
        $this->currentPlayer = $player;
        $this->moveCount = $moveCount;
        $this->moves = new Moves($board, $player);
        $this->isGameEnd = $this->isGameEnd();
        $this->state = $this->state();
    }

    public static function initialize(Player $player, int $boardSizeX = 8, int $boardSizeY = 8) : self
    {
        Cell::$allOrientations = [];
        $halfX = round($boardSizeX / 2);
        $halfY = round($boardSizeY / 2);
        $white = [$halfX . '-' . $halfY, ($halfX + 1) . '-' . ($halfY + 1)];
        $black = [$halfX . '-' . ($halfY + 1), ($halfX + 1) . '-' . $halfY];
        $board = new Board($boardSizeX, $boardSizeY, $white, $black);
        $game = new self($board, $player);

        return $game;
    }

    /**
     * ゲーム木の子ノードを生成する
     * @param string $index 手もしくはパス
     * @return Game
     */
    public function node(string $index) : Game
    {
        $moves = $this->moves();
        $moveCount = $this->moveCount + 1;
        $board = $index === 'pass'
            ? $this->board
            : $moves[$index]->newState($this->board, $this->currentPlayer);
        $player = $this->currentPlayer->enemy();
        return new Game($board, $player, $moveCount);
    }

    /**
     * @return Moves
     */
    public function moves() : Moves
    {
        return $this->moves;
    }

    public function board() : Board
    {
        return $this->board;
    }

    public function getCurrentPlayer() : Player
    {
        return $this->currentPlayer;
    }

    private function state() : GameState
    {
        if (!$this->isGameEnd()) {
            return GameState::ONGOING;
        }
        $white = count($this->board->white);
        $black = count($this->board->black);
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
        if (
            count($this->board->empties) === 0
            || count($this->board->white) === 0
            || count($this->board->black) === 0
        ) {
            return true;
        }
        $moves = $this->moves;
        if ($moves->hasMoves()) {
            return false;
        }
        $enemyMoves = new Moves($this->board, $this->currentPlayer->enemy());
        if ($enemyMoves->hasMoves()) {
            return false;
        }
        return true;
    }

    public function toHistory() : History
    {
        return new History($this->board, $this->currentPlayer, $this->moveCount);
    }

    public static function fromHistory(History $history) : Game
    {
        $board = Board::fromArray($history->board);
        $currentPlayer = $history->player === Player::WHITE->name ? Player::WHITE : Player::BLACK;
        $moveCount = $history->moveCount;
        return new self($board, $currentPlayer, $moveCount);
    }

    public function moveCount() : int
    {
        return $this->moveCount;
    }
}
