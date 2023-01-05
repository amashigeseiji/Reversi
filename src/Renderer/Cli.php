<?php
namespace Tenjuu99\Reversi\Renderer;

use Tenjuu99\Reversi\Command\Game;
use Tenjuu99\Reversi\Model\CellState;
use Tenjuu99\Reversi\Model\GameState;
use Tenjuu99\Reversi\Model\Player;

class Cli
{
    private Game $game;
    public bool $simple = false;
    public bool $exit = false;

    private CliColor $bgColor = CliColor::BG_LightGreen;

    public function __construct(int $boardSizeX = 8, int $boardSizeY = 8)
    {
        $this->game = new Game($this, Player::WHITE, $boardSizeX, $boardSizeY);
    }

    public function play()
    {
        $yokohaba = trim(shell_exec('tput cols'));
        if ((int)$yokohaba - ($this->game->boardSizeX * 4) - 3 <= 0) {
            $this->simple = true;
        }
        $this->render();
        while (true) {
            if ($this->exit) {
                break;
            }
            switch($this->game->state()) {
            case GameState::WIN_WHITE:
                $this->renderMessage('white win!' . PHP_EOL);
                $this->command('Input: ');
                break;
            case GameState::WIN_BLACK:
                $this->renderMessage('black win!' . PHP_EOL);
                $this->command('Input: ');
                break;
            case GameState::DRAW:
                $this->renderMessage('draw!' . PHP_EOL);
                $this->command('Input: ');
                break;
            }
            if ($this->game->isMyTurn() || !$this->game->opponentComputer) {
                $this->renderMessage('moves: ' . $this->game->moves() . PHP_EOL);
                $return = $this->command($this->game->currentPlayer() . ": ");
                if ($return) {
                    $this->renderMessage($return . PHP_EOL);
                } else {
                    $this->render();
                }
            } else {
                $message = $this->game->currentPlayer() . ": thinking... ";
                $this->renderMessage($message);
                $this->sleep($this->game->sleep);
                $command = $this->game->compute();
                $this->render();
                $this->renderMessage($message . $command . PHP_EOL);
            }
        }
    }

    private function command(string $inputMessage = '')
    {
        $input = readline($this->renderMessage($inputMessage, true));
        return $this->game->invoke($input);
    }

    public function benchmark(int $count = 10)
    {
        $done = [];
        for ($i = 0; $i < $count; $i++) {
            $start = microtime(true);
            while ($this->game->state() === GameState::ONGOING) {
                $this->game->compute();
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
            echo PHP_EOL;
            $done[] = microtime(true) - $start;
            echo 'time: ' . (string)(microtime(true) - $start) . PHP_EOL;
            $this->game->reset();
        }
        echo 'average: ' . array_sum($done) / count($done) . PHP_EOL;
    }

    public function render()
    {
        if ($this->simple) {
            return $this->renderSimple();
        }
        $format = $this->sprintfFormat();
        $board = $this->game->board();
        $lines = [];
        foreach ($board as $index => $cell) {
            $lines[$cell->y -1][$cell->x -1] = $cell;
        }
        system('clear');
        echo sprintf($format, ' ') . ' ';
        for ($i = 1; $i <= $this->game->boardSizeX; $i++) {
            echo sprintf('% 2d ', $i) . ' ';
        }
        echo PHP_EOL;

        foreach ($lines as $line) {
            echo sprintf($format, ' ') . $this->beforeLineRender();
            echo  sprintf($format, $line[0]->y . ' ');
            foreach ($line as $cell) {
                echo $this->color('|', CliColor::Black, $this->bgColor) . $this->cellRenderer($cell->state);
            }
            echo $this->color('|', CliColor::Black, $this->bgColor) . PHP_EOL;
        }
        echo sprintf($format, ' ') . $this->beforeLineRender();
    }

    public function renderSimple()
    {
        $board = $this->game->board();
        $lines = [];
        foreach ($board as $index => $cell) {
            $lines[$cell->y -1][$cell->x -1] = $cell;
        }
        system('clear');
        echo '  ';
        for ($i = 1; $i <= $this->game->boardSizeX; $i++) {
            if ($i % 10 === 0) {
                echo $i;
            } else {
                echo sprintf('% 2d', substr($i, -1));
            }
        }
        echo PHP_EOL;

        foreach ($lines as $line) {
            echo sprintf('% 2d', $line[0]->y . ' ');
            foreach ($line as $cell) {
                echo $this->color('|', CliColor::Black, $this->bgColor) . $this->cellRenderer($cell->state);
            }
            echo $this->color('|', CliColor::Black, $this->bgColor) . PHP_EOL;
        }
    }

    private function beforeLineRender()
    {
        $unit = '+---';
        $beforeLine = '';
        for ($i = 1; $i <= $this->game->boardSizeX; $i++) {
            $beforeLine .= $unit;
        }
        $beforeLine .= '+' . PHP_EOL;
        return $this->color($beforeLine, CliColor::Black, $this->bgColor);
    }

    private function cellRenderer(CellState $state)
    {
        $empty = $this->simple ? ' ' : '   ';
        $white = $this->simple ? 'w' : ' ● ';
        $black = $this->simple ? 'b' : ' ● ';
        return match($state) {
            CellState::EMPTY => $this->color($empty, CliColor::LightGray, $this->bgColor),
            CellState::WHITE => $this->color($white, CliColor::White, $this->bgColor),
            CellState::BLACK => $this->color($black, CliColor::Black, $this->bgColor)
        };
    }

    private function renderMessage(string $message, bool $return = false)
    {
        $message = $this->simple ? $message : sprintf($this->sprintfFormat(), ' ') . $message;
        if ($return) {
            return $message;
        } else {
            echo $message;
        }
    }

    private function sprintfFormat()
    {
        $space = 3;
        return '% ' . $space . 's';
    }

    private function color($message, CliColor $color = CliColor::DEFAULT, CliColor $bgColor = CliColor::BG_DEFAULT)
    {
        return sprintf("\033[%d;%dm%s\033[m", $color->value, $bgColor->value, $message);
    }

    private function sleep(int|float $sleep)
    {
        if (gettype($sleep) === 'integer') {
            sleep($sleep);
        } elseif (gettype($sleep) === 'double') {
            usleep($sleep * 1000000);
        }
    }
}
