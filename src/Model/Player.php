<?php
namespace Tenjuu99\Reversi\Model;

enum Player
{
    case BLACK;
    case WHITE;

    public function toCellState() : CellState
    {
        return match($this) {
            self::BLACK => CellState::BLACK,
            self::WHITE => CellState::WHITE,
        };
    }

    public function enemy() : self
    {
        return match($this) {
            self::BLACK => self::WHITE,
            self::WHITE => self::BLACK,
        };
    }
}
