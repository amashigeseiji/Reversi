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
        return new Move($this->index, array_merge($this->flipCells, $move->flipCells));
    }

    public function newState(Board $board, Player $player) : Board
    {
        $board = $board->toArray();
        $owner = $player->toCellState();
        $enemy = $player->enemy()->toCellState();

        $owns = array_merge($this->flipCells, [$this->index], $board[$owner->value]);
        $enemies = array_combine($board[$enemy->value], $board[$enemy->value]);
        foreach ($this->flipCells as $index) {
            unset($enemies[$index]);
        }
        $enemies = array_values($enemies);

        $newBoard = [
            'xMax' => $board['xMax'],
            'yMax' => $board['yMax'],
            $owner->value => $owns,
            $enemy->value => $enemies,
        ];
        return Board::fromArray($newBoard);
    }
}
