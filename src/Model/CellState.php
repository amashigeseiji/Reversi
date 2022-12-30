<?php
namespace Tenjuu99\Reversi\Model;

enum CellState : int
{
    case EMPTY = 0;
    case WHITE = 1;
    case BLACK = 2;

    public function flip()
    {
        return match($this) {
            self::WHITE => self::BLACK,
            self::BLACK => self::WHITE,
        };
    }
}
