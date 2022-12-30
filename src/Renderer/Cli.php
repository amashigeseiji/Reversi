<?php
namespace Tenjuu99\Reversi\Renderer;

use Tenjuu99\Reversi\Command\Game;
use Tenjuu99\Reversi\Model\Cell;
use Tenjuu99\Reversi\Model\CellState;
use Tenjuu99\Reversi\Model\Player;

class Cli
{
    private Game $game;

    public function __construct()
    {
        $this->game = new Game(Player::WHITE);
    }

    public function play()
    {
        while (true) {
          $this->render();
          if ($this->game->isMyTurn()) {
              echo $this->game->currentPlayer() . ": ";
              $input = trim(fgets(STDIN));
              $return = $this->game->invoke($input);
              if ($return) {
                  echo $return . PHP_EOL;
              }
          } else {
              echo $this->game->currentPlayer() . ": thinking..." . PHP_EOL;
              $this->game->compute();
          }
        }
    }

    public function render()
    {
        $cells = $this->game->cells();
        $lines = [];
        foreach ($cells as $index => $cell) {
            $lines[$cell->y -1][$cell->x -1] = $cell;
        }
        echo 'y\x 1 2 3 4 5 6 7 8 ' . PHP_EOL;
        foreach ($lines as $line) {
            echo '  ' . $line[0]->y;
            foreach ($line as $cell) {
                echo '|' . $this->cellRenderer($cell->state);
            }
            echo '|' . PHP_EOL;
        }
    }

    private function cellRenderer(CellState $state)
    {
        return match($state) {
            CellState::EMPTY => " ",
            CellState::WHITE => 'w',
            CellState::BLACK => 'b'
        };
    }
}
