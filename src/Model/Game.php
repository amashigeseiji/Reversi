<?php
namespace Tenjuu99\Reversi\Model;

class Game
{
    private Board $board;
    private Player $currentPlayer;

    public const SEPARATOR = '-';

    private function __construct(Board $board, Player $currentPlayer)
    {
        $this->board = $board;
        $this->currentPlayer = $currentPlayer;
    }

    public static function initialize(Player $player) : self
    {
        $board = Board::initialize();
        $board['4-4'] = Player::WHITE->name;
        $board['4-5'] = Player::BLACK->name;
        $board['5-4'] = Player::BLACK->name;
        $board['5-5'] = Player::WHITE->name;

        return new self($board, $player);
    }

    public function put(Move $move)
    {
        $this->board[$move->index] = $move->player;
    }

    public function moves() : array
    {
        // 現在のセルから敵の石があるセルを抽出
        $enemyCells = array_filter($this->board, fn($cell) => $cell && $cell !== $this->currentPlayer->name);
        // ひとつづつ隣りあうセルをとりだす
        foreach (array_keys($enemyCells) as $index) {
            $nextCells[$index] = self::getNextEmptyCells($index);
        }
        return $nextCells;
    }

    public function getBoardState() : Board
    {
        return $this->board;
    }

    public function getPlayer() : Player
    {
        return $this->currentPlayer;
    }

    public function changePlayer()
    {
        if ($this->currentPlayer === Player::WHITE) {
            $this->currentPlayer = Player::BLACK;
        } else {
            $this->currentPlayer = Player::WHITE;
        }
    }

    private static function getNextCells(string $index) : array
    {
        [$x, $y] = explode(self::SEPARATOR, $index);
        $indices = [
            [$x + 1, $y],
            [$x - 1, $y],
            [$x, $y + 1],
            [$x, $y - 1],
            [$x + 1, $y + 1],
            [$x - 1, $y - 1],
            [$x + 1, $y - 1],
            [$x - 1, $y + 1],
        ];
        $indices = array_filter($indices, function ($index) {
          return $index[0] > 0 && $index[0] <= 8 && $index[1] > 0 && $index[1] <= 8;
        });
        return array_map(fn($index) => implode(self::SEPARATOR, $index), $indices);
    }

    private function getNextEmptyCells(string $index) : array
    {
        return array_filter(self::getNextCells($index), function ($i) {
            return $this->board[$i];
        });
    }
}
