<?php
namespace Tenjuu99\Reversi\Model;

use Tenjuu99\Reversi\AI\Ai;

class Game
{
    private Board $board;
    private Player $currentPlayer;
    /** @var Moves[] */
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
        $board = new Board($boardSizeX, $boardSizeY);
        $halfX = round($boardSizeX / 2);
        $halfY = round($boardSizeY / 2);
        $board[$halfX . '-' . $halfY]->put(CellState::WHITE);
        $board[$halfX . '-' . $halfY + 1]->put(CellState::BLACK);
        $board[$halfX + 1 . '-' . $halfY]->put(CellState::BLACK);
        $board[$halfX + 1 . '-' . $halfY + 1]->put(CellState::WHITE);
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
        $moves[$index]->execute();
        // 盤面サイズがでかい場合にメモリが足りなくなるのでクリアする
        unset($this->moves[$this->boardHash . $this->getPlayer()->name]);
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
        if ($moves->count() > 0) {
            return false;
        }
        $enemyMoves = $this->getMoves($this->board, $this->currentPlayer->enemy());
        if ($enemyMoves->count() > 0) {
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
            // $this->history = [];
        }
    }

    public function history() : array
    {
        return array_keys($this->history);
    }
}
