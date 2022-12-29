<?php
namespace Tenjuu99\Reversi\Model;

enum CellState : int
{
    case EMPTY = 0;
    case WHITE = 1;
    case BLACK = 2;
}
