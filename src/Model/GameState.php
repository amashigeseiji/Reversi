<?php
namespace Tenjuu99\Reversi\Model;

enum GameState : string
{
    case WIN_WHITE = 'white';
    case WIN_BLACK = 'black';
    case DRAW = 'draw';
    case ONGOING = 'on going';
}
