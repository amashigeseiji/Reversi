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

    public function move(string $index) : bool
    {
        $moves = $this->moves();
        foreach ($moves as $move) {
            if ($move->index !== $index) {
                continue;
            }
            $this->board->put($move->index, $move->player);
            $orientations = [
                'right', 'left', 'upper', 'lower',
                'upperRight', 'upperLeft', 'lowerRight', 'lowerLeft',
            ];
            foreach ($orientations as $orientation) {
                $chain = $move->cell->chain($orientation);
                if (count($chain) <= 1) {
                    continue;
                }
                if ($chain[0]->state !== $this->currentPlayer->enemy()->toCellState()) {
                    continue;
                }
                $flips = [];
                $tmpFlips = [];
                foreach ($chain as $cell) {
                    if ($cell->state === CellState::EMPTY) {
                        $tmpFlips = [];
                        break;
                    } elseif ($cell->state === $this->currentPlayer->enemy()->toCellState()) { // 敵陣
                        $tmpFlips[] = $cell;
                    } else { // 自陣
                        $flips = array_merge($flips, $tmpFlips);
                        $tmpFlips = [];
                        break;
                    }
                }
                foreach ($flips as $flipCell) {
                    $flipCell->flip();
                }
            }
            return true;
        }
        return false;
    }

    /**
     * todo move を生成するタイミングで flip セルも計算する
     * move クラスに flip セルをもたせる
     * @return Move[]
     */
    public function moves() : array
    {
        $moves = [];
        $empties = $this->board->filterState(CellState::EMPTY);
        $orientations = [
            'right', 'left', 'upper', 'lower',
            'upperRight', 'upperLeft', 'lowerRight', 'lowerLeft',
        ];
        foreach ($empties as $emptyCell) {
            foreach ($orientations as $orientation) {
                $chain = $emptyCell->chain($orientation);
                if (count($chain) <= 1) {
                    continue;
                }
                $next = array_shift($chain);
                if ($next->state !== $this->currentPlayer->enemy()->toCellState()) {
                    continue;
                }
                foreach ($chain as $cell) {
                    switch ($cell->state) {
                    case CellState::EMPTY:
                        break 2;
                    case $this->currentPlayer->enemy()->toCellState():
                        break;
                    case $this->currentPlayer->toCellState():
                        $moves[] = new Move($emptyCell, $this->currentPlayer);
                        break 3;
                    }
                }
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
