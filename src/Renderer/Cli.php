<?php
namespace Tenjuu99\Reversi\Renderer;

use Tenjuu99\Reversi\Command\Game;
use Tenjuu99\Reversi\Model\Cell;
use Tenjuu99\Reversi\Model\CellState;
use Tenjuu99\Reversi\Model\GameState;
use Tenjuu99\Reversi\Model\Player;

class Cli
{
    private Game $game;
    private int $boardSizeX;
    private int $boardSizeY;

    public function __construct(int $boardSizeX = 8, int $boardSizeY = 8)
    {
        $this->game = new Game(Player::WHITE, $boardSizeX, $boardSizeY);
        $this->boardSizeX = $boardSizeX;
        $this->boardSizeY = $boardSizeY;
    }

    public function play()
    {
        $state = $this->game->state();
        while ($state === GameState::ONGOING) {
          $state = $this->game->state();
          $this->render();
          if ($this->game->isMyTurn()) {
              echo 'moves: ' . $this->game->moves() . PHP_EOL;
              echo $this->game->currentPlayer() . ": ";
              $input = trim(fgets(STDIN));
              $return = $this->game->invoke($input);
              if ($return) {
                  echo $return . PHP_EOL;
              }
          } else {
              echo $this->game->currentPlayer() . ": thinking..." . PHP_EOL;
              $command = $this->game->compute();
              echo $command . PHP_EOL;
          }
        }
        switch($state) {
        case GameState::WIN_WHITE:
            echo 'white win!';
            break;
        case GameState::WIN_BLACK:
            echo 'black win!';
            break;
        case GameState::DRAW:
            echo 'draw!';
            break;
        }
    }

    public function benchmark(int $count = 10)
    {
        $done = [];
        for ($i = 0; $i < $count; $i++) {
            $start = microtime(true);
            $state = $this->game->state();
            while ($state === GameState::ONGOING) {
                $state = $this->game->state();
                $this->game->compute();
            }
            switch($state) {
            case GameState::WIN_WHITE:
                echo 'white win!';
                break;
            case GameState::WIN_BLACK:
                echo 'black win!';
                break;
            case GameState::DRAW:
                echo 'draw!';
                break;
            }
            echo PHP_EOL;
            $done[] = microtime(true) - $start;
            echo 'time: ' . microtime(true) - $start . PHP_EOL;
            $this->game->reset();
        }
        echo 'average: ' . array_sum($done) / count($done) . PHP_EOL;
    }

    public function render()
    {
        $board = $this->game->board();
        $lines = [];
        foreach ($board as $index => $cell) {
            $lines[$cell->y -1][$cell->x -1] = $cell;
        }
        echo '   ';
        for ($i = 1; $i <= $this->boardSizeX; $i++) {
            echo sprintf('%02d ', $i) . ' ';
        }
        echo PHP_EOL;

        foreach ($lines as $line) {
            echo '  ' . $this->beforeLineRender();
            echo  sprintf('%02d', $line[0]->y);
            foreach ($line as $cell) {
                echo '|' . $this->cellRenderer($cell->state);
            }
            echo '|' . PHP_EOL;
        }
        echo '  ' . $this->beforeLineRender();
    }

    private function beforeLineRender()
    {
        $unit = '+---';
        $beforeLine = '';
        for ($i = 1; $i <= $this->boardSizeX; $i++) {
            $beforeLine .= $unit;
        }
        $beforeLine .= '+' . PHP_EOL;
        return $beforeLine;
    }

    private function cellRenderer(CellState $state)
    {
        return match($state) {
            CellState::EMPTY => "   ",
            CellState::WHITE => ' w ',
            CellState::BLACK => ' b '
        };
    }
}
