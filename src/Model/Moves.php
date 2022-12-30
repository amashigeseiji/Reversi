<?php
namespace Tenjuu99\Reversi\Model;

use ArrayObject;

class Moves extends ArrayObject
{
    public function __construct(Board $board, Player $player)
    {
        $empties = $board->filterState(CellState::EMPTY);
        foreach ($empties as $emptyCell) {
            $move = new Move($emptyCell, $player);
            if (count($move->flipCells) > 0) {
                $this->offsetSet($emptyCell->index, $move);
            }
        }
    }

    public function __toString()
    {
        return implode(' ', array_keys(iterator_to_array($this)));
    }
}
