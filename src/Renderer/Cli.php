<?php
namespace Tenjuu99\Reversi\Renderer;

use Tenjuu99\Reversi\Command\Game;
use Tenjuu99\Reversi\Model\Player;

class Cli
{
    private Game $game;

    public function __construct()
    {
        $this->game = new Game();
    }

    public function play()
    {
        while (true) {
          $this->render();
          echo $this->game->player() . ": ";
          $input = trim(fgets(STDIN));
          $return = $this->game->invoke($input);
          if ($return) {
              echo $return . PHP_EOL;
          }
        }
    }

    public function render()
    {
        $board = $this->game->board();
        $lines = [];
        foreach ($board as $index => $cell) {
            [$x, $y] = explode('-', $index);
            $lines[$y-1][$x-1] = [$cell, $x, $y, $index];
        }
        echo 'y\x 1 2 3 4 5 6 7 8 ' . PHP_EOL;
        foreach ($lines as $line) {
            echo '  ' . $line[0][2];
            foreach ($line as $cell) {
                echo '|' . $this->cellRenderer($cell);
            }
            echo '|' . PHP_EOL;
        }
    }

    private function cellRenderer($cell)
    {
        return match($cell[0]) {
            null => " ",
            Player::WHITE->name => 'w',
            Player::BLACK->name => 'b'
        };
    }
}
