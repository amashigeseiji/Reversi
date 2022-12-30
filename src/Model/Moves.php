<?php
namespace Tenjuu99\Reversi\Model;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

class Moves implements ArrayAccess, IteratorAggregate, Countable
{
    /** @var Move[] */
    private array $moves = [];

    public function __construct(Board $board, Player $player)
    {
        $empties = $board->filterState(CellState::EMPTY);
        foreach ($empties as $emptyCell) {
            $move = new Move($emptyCell, $player);
            if (count($move->flipCells) > 0) {
                $this->moves[$move->index] = $move;
            }
        }
    }

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->moves);
    }

    public function offsetGet($offset): Move
    {
        if (!$this->offsetExists($offset)) {
            throw new \Exception('Invalid offset ' . $offset . ' is given.');
        }
        return $this->moves[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        throw new \Exception('Do not use offsetSet.');
    }

    public function offsetUnset($offset): void
    {
        throw new \Exception('Do not use offsetUnset.');
    }

    /**
     * @return Move[]
     */
    public function getIterator() : Traversable
    {
        return new ArrayIterator($this->moves);
    }

    public function count() : int
    {
        return count($this->moves);
    }

    public function __toString()
    {
        return implode(' ', array_keys($this->moves));
    }
}
