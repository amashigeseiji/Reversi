<?php
namespace Tenjuu99\Reversi\Model;

use ArrayAccess;

class Moves implements ArrayAccess
{
    private readonly array $moves;

    /**
     * @param Board $board
     * @param Player $player
     */
    public function __construct(Board $board, Player $player)
    {
        $this->moves = self::generate($board, $player);
    }

    /**
     * @return array<string, Move>
     */
    private static function generate(Board $board, Player $player) : array
    {
        $moves = [];
        $owns = $board->getPlayersCells($player);
        $empties = $board->empties;
        if (count($owns) < count($empties)) {
            foreach ($owns as $index) {
                $cell = $board[$index];
                $movesFromOwnCells = self::movesFromOwnCells($cell, $player);
                // index が重複している場合2ライン以上にわたってflip可能セルがあるのでマージする
                foreach ($movesFromOwnCells as $move) {
                    if (isset($moves[$move->index])) {
                        $moves[$move->index] = $move->merge($moves[$move->index]);
                    } else {
                        $moves[$move->index] = $move;
                    }
                }
            }
        } else {
            $moves = [];
            foreach ($empties as $emptyCell) {
                if ($move = self::moveFromEmptyCell($emptyCell, $player)) {
                    $moves[$move->index] = $move;
                }
            }
        }
        ksort($moves);
        return $moves;
    }

    /**
     * 自陣の石を同じ方角に向かってしらべる
     * 敵陣の石があればpush
     * 自陣の石が出てきたらbreak
     * 空白セルの場合、敵陣の石がひとつ以上あれば、その空白には打つことができる
     */
    private static function movesFromOwnCells(Cell $cell, Player $player)
    {
        $moves = [];
        foreach ($cell->orientations as $orientation => $index) {
            [$move, $flip] = self::chainFromOwnCell($cell, $player, $orientation);
            if (!$move) {
                continue;
            }
            $moves[] = new Move($move->index, $flip);
        }
        return $moves;
    }

    private static function chainFromOwnCell(Cell $cell, Player $player, string $orientation)
    {
        $enemyState = $player->enemy()->toCellState();
        $playerState = $player->toCellState();
        $current = $cell;
        $flipCells = [];
        $move = null;
        $enemies = [];
        while($current = $current->nextCell($orientation)) {
            if ($current->state === $enemyState) {
                $enemies[] = $current->index;
                continue;
            } elseif ($current->state === $playerState) {
                $enemies = [];
                break;
            } elseif ($current->state === CellState::EMPTY) {
                if (count($enemies) > 0) {
                    $move = $current;
                }
                break;
            }
        }
        return [
            $move,
            $enemies
        ];
    }

    /**
     * 空白セルを起点に裏返すことができるセルをしらべ、手を生成する
     */
    private static function moveFromEmptyCell(Cell $cell, Player $player) : ?Move
    {
        $flippable = self::flippableFromEmptyCell($cell, $player);
        if ($flippable) {
            return new Move($cell->index, $flippable);
        }
        return null;
    }

    private static function flippableFromEmptyCell(Cell $cell, Player $player) : array
    {
        $cells = [];
        foreach ($cell->orientations as $orientation => $index) {
            $chain = self::chainFromEmptyCell($cell, $orientation, $player);
            if ($chain) {
                $cells = array_merge($cells, array_map(fn(Cell $cell) => $cell->index, $chain));
            }
        }
        return $cells;
    }

    /**
     * 指定された方向に、ひっくりかえすことができる
     * セルのチェーン状のつらなりを生成する。
     * (空)白白(黒)、(空)黒黒黒(白)、(空)黒(白)などの連なりのパターン
     * にあてはまる場合に配列を生成する。
     *
     * @return Cell[]
     */
    public static function chainFromEmptyCell(Cell $cell, string $orientation, Player $player) : array
    {
        $cells = [];
        $current = $cell;
        $enemyState = $player->enemy()->toCellState();
        while($current = $current->nextCell($orientation)) {
            // 隣が空白セルの場合は終了
            if ($current->state === CellState::EMPTY) {
                break;
            } elseif ($current->state === $enemyState) {
                $cells[] = $current;
                continue;
            } else {
                return $cells;
            }
        }
        return [];
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->moves[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->moves[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \Exception();
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new \Exception();
    }

    public function hasMoves() : bool
    {
        return count($this->moves) > 0;
    }

    public function indices(): array
    {
        return array_keys($this->moves);
    }

    /**
     * @return Move[]
     */
    public function getAll(): array
    {
        return $this->moves;
    }
}
