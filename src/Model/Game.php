<?php
namespace Tenjuu99\Reversi\Model;

use Tenjuu99\Reversi\AI\Ai;

class Game
{
    private Board $board;
    private Player $currentPlayer;
    private Player $user = Player::WHITE;
    private array $moves;
    private Histories $history;
    /** 何手目か */
    private int $moveCount = 0;
    /**
     * boardをハッシュ化した値
     * 初期化、一手うったタイミングで計算しなおす
     */
    private string $boardHash;
    private Ai $ai;

    private function __construct(Board $board, Player $player, ?int $moveCount = null)
    {
        $this->board = $board;
        $this->currentPlayer = $player;
        $this->user = $player;
        $this->boardHash = $board->hash();
        $this->history = new Histories;
        $this->ai = new Ai();
        if ($moveCount) {
            $this->moveCount = $moveCount;
        }
    }

    public static function initialize(Player $player, int $boardSizeX = 8, int $boardSizeY = 8) : self
    {
        $halfX = round($boardSizeX / 2);
        $halfY = round($boardSizeY / 2);
        $board = new Board($boardSizeX, $boardSizeY);
        $board->put($halfX . '-' . $halfY, CellState::WHITE);
        $board->put($halfX . '-' . ($halfY + 1), CellState::BLACK);
        $board->put(($halfX + 1) . '-' . $halfY, CellState::BLACK);
        $board->put(($halfX + 1) . '-' . ($halfY + 1), CellState::WHITE);
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
        // ハッシュ値の再計算
        $this->boardHash = $this->board->hash();
        return true;
    }

    /**
     * ゲーム木の子ノードを生成する
     * @param string $index 手もしくはパス
     * @return Game|null
     */
    public function node(string $index) : ?Game
    {
        $moves = $this->moves();
        if ($index !== 'pass' && !isset($moves[$index])) {
            return null;
        }
        $moveCount = $this->moveCount + 1;
        $board = $index === 'pass'
            ? Board::fromArray($this->board->toArray())
            : $moves[$index]->newState($this->board, $this->currentPlayer);
        $player = $this->currentPlayer->enemy();
        return new Game($board, $player, $moveCount);
    }

    /**
     * @return array<string, Move>
     */
    public function moves() : array
    {
        return $this->getMoves($this->board, $this->currentPlayer);
    }

    /**
     * @return array<string, Move>
     */
    private function getMoves(Board $board, Player $player) : array
    {
        $hash = $this->boardHash . $player->name;
        if (isset($this->moves[$hash])) {
            return $this->moves[$hash];
        }
        $moves = Moves::generate($board, $player);
        if ($moves) {
            return $this->moves[$hash] = $moves;
        }
        return [];
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
        $white = count($this->board->whites());
        $black = count($this->board->blacks());
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
        if (count($this->board->empties()) === 0) {
            return true;
        }
        $moves = $this->getMoves($this->board, $this->currentPlayer);
        if (count($moves) > 0) {
            return false;
        }
        $enemyMoves = $this->getMoves($this->board, $this->currentPlayer->enemy());
        if (count($enemyMoves) > 0) {
            return false;
        }
        return true;
    }

    public function compute(string $strategy = 'random', int $searchLevel = 2) : string
    {
        $move = $this->ai->choice($this, $strategy, $searchLevel);
        if ($move) {
            $this->move($move->index);
            return $move->index;
        } else {
            $this->next();
            return 'pass';
        }
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
        $this->boardHash = $history->hash;
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

    public function userPlayer() : Player
    {
        return $this->user;
    }

    public function computerPlayer(): Player
    {
        return $this->user->enemy();
    }
}
