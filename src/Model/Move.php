<?php
namespace Tenjuu99\Reversi\Model;

class Move
{
    public readonly string $index;
    public readonly array $flipCells;

    public function __construct(string $cell, array $flipCells)
    {
        $this->index = $cell;
        $this->flipCells = $flipCells;
    }

    public function merge(Move $move): Move
    {
        if ($this->index !== $move->index) {
            throw new \Exception('Invalid move index.');
        }
        return new Move($this->index, [...$this->flipCells, ...$move->flipCells]);
    }

    public function newState(Board $board, Player $player) : Board
    {
        $board = $board->toArray();
        $owner = $player->toCellState();
        $enemy = $player->enemy()->toCellState();

        $owns = array_merge($this->flipCells, [$this->index], $board[$owner->value]);
        $enemies = array_filter($board[$enemy->value], fn(string $cellIndex) => !in_array($cellIndex, $this->flipCells));

        $newBoard = [
            'xMax' => $board['xMax'],
            'yMax' => $board['yMax'],
            $owner->value => $owns,
            $enemy->value => $enemies,
        ];
        return Board::fromArray($newBoard);
    }
}
