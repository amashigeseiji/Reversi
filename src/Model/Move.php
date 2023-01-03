<?php
namespace Tenjuu99\Reversi\Model;

class Move
{
    public readonly Cell $cell;
    public readonly string $index;
    public readonly Player $player;
    public readonly array $flipCells;
    private array $board;

    public function __construct(Cell $cell, Player $player, array $board)
    {
        if ($cell->state !== CellState::EMPTY) {
            throw new \Exception('Cell ' . $cell->index . ' is not empty.');
        }
        $this->cell = $cell;
        $this->index = $cell->index;
        $this->board = $board;
        $this->flipCells = $this->flippable($cell, $player);
    }

    public function newState(Player $player) : Board
    {
        $owner = $player->toCellState();
        $enemy = $player->enemy()->toCellState();

        $owns = array_merge($this->flipCells, [$this->cell->index], $this->board[$owner->value]);
        $enemies = array_filter($this->board[$enemy->value], fn(string $cellIndex) => !in_array($cellIndex, $this->flipCells));

        $newBoard = [
            'xMax' => $this->board['xMax'],
            'yMax' => $this->board['yMax'],
            $owner->value => $owns,
            $enemy->value => $enemies,
        ];
        return Board::fromArray($newBoard);
    }

    private function flippable(Cell $cell, Player $player) : array
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
