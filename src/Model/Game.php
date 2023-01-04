<?php
namespace Tenjuu99\Reversi\Model;

use Tenjuu99\Reversi\AI\Ai;

class Game
{
    private Board $board;
    private Player $currentPlayer;
    private array $moves;
    private array $history = [];
    /**
     * boardをハッシュ化した値
     * 初期化、一手うったタイミングで計算しなおす
     */
    private string $boardHash;
    private Ai $ai;

    private function __construct(Board $board, Player $currentPlayer, string $strategy = 'random')
    {
        $this->board = $board;
        $this->currentPlayer = $currentPlayer;
        $this->boardHash = $board->hash();
        $this->ai = new Ai($strategy);
    }

    public static function initialize(Player $player, int $boardSizeX = 8, int $boardSizeY = 8, string $strategy = 'random') : self
    {
        $halfX = round($boardSizeX / 2);
        $halfY = round($boardSizeY / 2);
        $board = new Board($boardSizeX, $boardSizeY);
        $board->put($halfX . '-' . $halfY, CellState::WHITE);
        $board->put($halfX . '-' . $halfY + 1, CellState::BLACK);
        $board->put($halfX + 1 . '-' . $halfY, CellState::BLACK);
        $board->put($halfX + 1 . '-' . $halfY + 1, CellState::WHITE);
        $game = new self($board, $player, $strategy);

        return $game;
    }

    public function move(string $index) : bool
    {
        $moves = $this->moves();
        if (!isset($moves[$index])) {
            return false;
        }
        $this->history[$this->boardHash] = [
            'board' => $this->board->toArray(),
            'player' => $this->currentPlayer->name,
        ];
        if (count($this->history) > 10) {
            array_shift($this->history);
        }
        $this->board = $moves[$index]->newState($this->board, $this->currentPlayer);
        // 盤面サイズがでかい場合にメモリが足りなくなるのでクリアする
        $this->moves = [];
        $this->next();
        // ハッシュ値の再計算
        $this->boardHash = $this->board->hash();
        return true;
    }

    /**
     * @return Move[]
     */
    public function moves() : array
    {
        return $this->getMoves($this->board, $this->currentPlayer);
    }

    /**
     * @return Move[]
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

    private function isGameEnd() : bool
    {
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

    public function compute() : string
    {
        $move = $this->ai->choice($this);
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
        if (isset($this->history[$hash])) {
            $this->board = Board::fromArray($this->history[$hash]['board']);
            $this->boardHash = $hash;
            $this->currentPlayer = $this->history[$hash]['player'] === Player::WHITE ? Player::WHITE : Player::BLACK;
            $this->moves = [];
        }
    }

    public function history() : array
    {
        return array_keys($this->history);
    }
}
