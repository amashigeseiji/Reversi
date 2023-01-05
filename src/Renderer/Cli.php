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
        $this->render();
        while ($this->game->state() === GameState::ONGOING) {
          if ($this->game->isMyTurn()) {
              $this->renderMessage('moves: ' . $this->game->moves() . PHP_EOL);
              $this->renderMessage($this->game->currentPlayer() . ": ");
              $input = trim(fgets(STDIN));
              $return = $this->game->invoke($input);
              if ($return) {
                  $this->renderMessage($return . PHP_EOL);
              } else {
                  $this->render();
              }
          } else {
              $message = $this->game->currentPlayer() . ": thinking... ";
              $this->renderMessage($message);
              sleep(1);
              $command = $this->game->compute();
              $this->render();
              $this->renderMessage($message . $command. PHP_EOL);
          }
        }
        switch($this->game->state()) {
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
        $format = $this->sprintfFormat();
        system('clear');
        $board = $this->game->board();
        $lines = [];
        foreach ($board as $index => $cell) {
            $lines[$cell->y -1][$cell->x -1] = $cell;
        }
        echo sprintf($format, ' ') . ' ';
        for ($i = 1; $i <= $this->boardSizeX; $i++) {
            echo sprintf('% 2d ', $i) . ' ';
        }
        echo PHP_EOL;

        foreach ($lines as $line) {
            echo sprintf($format, ' ') . $this->beforeLineRender();
            echo  sprintf($format, $line[0]->y);
            foreach ($line as $cell) {
                echo $this->color('|', CliColor::Black, CliColor::BG_LightGray) . $this->cellRenderer($cell->state);
            }
            echo $this->color('|', CliColor::Black, CliColor::BG_LightGray) . PHP_EOL;
        }
        echo  sprintf($format, ' ') . $this->beforeLineRender();
    }

    private function beforeLineRender()
    {
        $unit = '+---';
        $beforeLine = '';
        for ($i = 1; $i <= $this->boardSizeX; $i++) {
            $beforeLine .= $unit;
        }
        $beforeLine .= '+' . PHP_EOL;
        return $this->color($beforeLine, CliColor::Black, CliColor::BG_LightGray);
    }

    private function cellRenderer(CellState $state)
    {
        return match($state) {
            CellState::EMPTY => $this->color("   ", CliColor::LightGray, CliColor::BG_LightGray),
            CellState::WHITE => $this->color(' ○ ', CliColor::Red, CliColor::BG_LightGray),
            CellState::BLACK => $this->color(' ● ', CliColor::Black, CliColor::BG_LightGray)
        };
    }

    private function renderMessage(string $message)
    {
        echo sprintf($this->sprintfFormat(), ' ') . $message;
    }

    private function sprintfFormat()
    {
        $yokohaba = trim(shell_exec('tput cols'));
        $space = ((int)$yokohaba / 4);
        return '% ' . $space . 's';
    }

    private function color($message, CliColor $color = CliColor::DEFAULT, CliColor $bgColor = CliColor::BG_DEFAULT)
    {
        return sprintf("\033[%d;%dm%s\033[m", $color->value, $bgColor->value, $message);
    }
}
