<?php
namespace Tenjuu99\Reversi\Model;

use Traversable;

class Game
{
    private Board $board;
    private Player $currentPlayer;
    private array $moves;
    private Histories $history;
    /** 何手目か */
    private int $moveCount = 0;

    private function __construct(Board $board, Player $player, ?int $moveCount = null)
    {
        $this->board = $board;
        $this->currentPlayer = $player;
        $this->history = new Histories;
        if ($moveCount) {
            $this->moveCount = $moveCount;
        }
    }

    public static function initialize(Player $player, int $boardSizeX = 8, int $boardSizeY = 8) : self
    {
        $halfX = round($boardSizeX / 2);
        $halfY = round($boardSizeY / 2);
        $white = [$halfX . '-' . $halfY, ($halfX + 1) . '-' . ($halfY + 1)];
        $black = [$halfX . '-' . ($halfY + 1), ($halfX + 1) . '-' . $halfY];
        $board = new Board($boardSizeX, $boardSizeY, $white, $black);
        $game = new self($board, $player);

        return $game;
    }

    public function move(string $index) : bool
    {
        $moves = $this->moves();
        if (!isset($moves[$index])) {
            return false;
        }
        $this->history->push($this);
        $this->moveCount++;
        $this->board = $moves[$index]->newState($this->board, $this->currentPlayer);
        // 盤面サイズがでかい場合にメモリが足りなくなるのでクリアする
        $this->moves = [];
        $this->next();
        return true;
    }

    /**
     * ゲーム木の子ノードを生成する
     * @param string $index 手もしくはパス
     * @return Game
     */
    private function node(string $index) : Game
    {
        $moves = $this->moves();
        $moveCount = $this->moveCount + 1;
        $board = $index === 'pass'
            ? $this->board
            : $moves[$index]->newState($this->board, $this->currentPlayer);
        $player = $this->currentPlayer->enemy();
        return new Game($board, $player, $moveCount);
    }

    public function expandNode(): Traversable
    {
        $moves = $this->moves();
        if (!$moves->hasMoves()) {
            yield 'pass' => $this->node('pass');
        } else {
            foreach ($moves as $index => $move) {
                yield $index => $this->node($index);
            }
        }
    }

    /**
     * @return Moves
     */
    public function moves() : Moves
    {
        return $this->getMoves($this->board, $this->currentPlayer);
    }

    /**
     * @return Moves
     */
    private function getMoves(Board $board, Player $player) : Moves
    {
        if (isset($this->moves[$player->name])) {
            return $this->moves[$player->name];
        }
        $moves = new Moves($board, $player);
        return $this->moves[$player->name] = $moves;
    }

    public function board() : Board
    {
        return $this->board;
    }

    public function getCurrentPlayer() : Player
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

    public function isGameEnd() : bool
    {
        if (
            count($this->board->empties) === 0
            || count($this->board->white) === 0
            || count($this->board->black) === 0
        ) {
            return true;
        }
        $moves = $this->getMoves($this->board, $this->currentPlayer);
        if ($moves->hasMoves()) {
            return false;
        }
        $enemyMoves = $this->getMoves($this->board, $this->currentPlayer->enemy());
        if ($enemyMoves->hasMoves()) {
            return false;
        }
        return true;
    }

    public function historyBack(string $hash)
    {
        if ($history = $this->history->get($hash)) {
            $this->fromHistory($history);
        }
    }

    public function toHistory() : History
    {
        return new History($this->board, $this->currentPlayer, $this->moveCount);
    }

    public function fromHistory(History $history)
    {
        $this->board = Board::fromArray($history->board);
        $this->currentPlayer = $history->player === Player::WHITE->name ? Player::WHITE : Player::BLACK;
        $this->moveCount = $history->moveCount;
        $this->moves = [];
    }

    public function history() : array
    {
        $histories = [];
        foreach ($this->history as $hash => $history) {
            $histories[$history->moveCount] = $hash;
        }
        return $histories;
    }

    public function moveCount() : int
    {
        return $this->moveCount;
    }
}
