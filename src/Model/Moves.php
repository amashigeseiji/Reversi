<?php
namespace Tenjuu99\Reversi\Model;

class Moves
{
    /**
     * @return array<string, Move>
     */
    public static function generate(Board $board, Player $player) : array
    {
        $moves = [];
        $owns = $board->getPlayersCells($player);
        $empties = $board->empties();
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
        $orientations = [
            'right', 'left', 'upper', 'lower',
            'upperRight', 'upperLeft', 'lowerRight', 'lowerLeft',
        ];
        $moves = [];
        foreach ($orientations as $orientation) {
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
        while($current = $current->{$orientation}()) {
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
        $orientations = [
            'right', 'left', 'upper', 'lower',
            'upperRight', 'upperLeft', 'lowerRight', 'lowerLeft',
        ];
        $cells = [];
        foreach ($orientations as $orientation) {
            $chain = $cell->chain($orientation);
            if ($chain && $chain[0]->state === $player->enemy()->toCellState()) {
                $cells = array_merge($cells, array_map(fn(Cell $cell) => $cell->index, $chain));
            }
        }
        return $cells;
    }
}
