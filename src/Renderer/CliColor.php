<?php
namespace Tenjuu99\Reversi\Renderer;

enum CliColor: int
{
    case DEFAULT = 39;
    case Black = 30;
    case Red = 31;
    case Green = 32;
    case Yellow = 33;
    case Blue = 34;
    case Magenta = 35;
    case Cyan = 36;
    case LightGray = 37;

    case BG_DEFAULT = 49;
    case BG_Black = 40;
    case BG_Red = 41;
    case BG_Green = 42;
    case BG_Yellow = 43;
    case BG_Blue = 44;
    case BG_Magenta = 45;
    case BG_Cyan = 46;
    case BG_LightGray = 47;
}
