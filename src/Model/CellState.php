<?php
namespace Tenjuu99\Reversi\Model;

enum CellState : string
{
    case EMPTY = '';
    case WHITE = 'white';
    case BLACK = 'black';

    public function flip()
    {
        return match($this) {
            self::WHITE => self::BLACK,
            self::BLACK => self::WHITE,
        };
    }
}
