<?php
namespace Tenjuu99\Reversi\Renderer\Cli;

use Tenjuu99\Reversi\Model\CellState;

class Board
{
    private Command $game;
    private bool $simple = false;
    private Color $bgColor = Color::BG_LightGreen;
    public int $marginLeft = 3;

    public function __construct(Command $game)
    {
        $this->game = $game;
        $yokohaba = trim(shell_exec('tput cols'));
        if ((int)$yokohaba - ($this->game->boardSizeX * 4) - 3 <= 0) {
            $this->simple = true;
        }
        $takasa = trim(shell_exec('tput lines'));
        if ((int)$takasa - ($this->game->boardSizeY * 2) - 3 <= 0) {
            $this->simple = true;
        }
    }

    public function simple(bool $simple = true)
    {
        $this->simple = $simple;
    }

    public function __toString()
    {
        if ($this->simple) {
            return $this->renderSimple();
        }
        $lines = $this->lines();

        ob_start();
        echo $this->marginLeft(' ') . ' ';
        for ($i = 1; $i <= $this->game->boardSizeX; $i++) {
            echo sprintf('% 2d ', $i) . ' ';
        }
        echo PHP_EOL;

        foreach ($lines as $line) {
            echo $this->marginLeft(' ') . $this->beforeLineRender();
            echo  $this->marginLeft($line[0]->y);
            foreach ($line as $cell) {
                echo $this->color('|', Color::Black, $this->bgColor) . $this->cellRenderer($cell->state);
            }
            echo $this->color('|', Color::Black, $this->bgColor) . PHP_EOL;
        }
        echo $this->marginLeft(' ') . $this->beforeLineRender();
        return ob_get_clean();
    }

    private function renderSimple() : string
    {
        ob_start();
        echo '  ';
        for ($i = 1; $i <= $this->game->boardSizeX; $i++) {
            if ($i % 10 === 0) {
                echo $i;
            } else {
                echo sprintf('% 2d', substr($i, -1));
            }
        }
        echo PHP_EOL;

        foreach ($this->lines() as $line) {
            echo sprintf('% 2d', $line[0]->y);
            foreach ($line as $cell) {
                echo $this->color('|', Color::Black, $this->bgColor) . $this->cellRenderer($cell->state);
            }
            echo $this->color('|', Color::Black, $this->bgColor) . PHP_EOL;
        }
        return ob_get_clean();
    }

    private function lines() : array
    {
        $board = $this->game->board();
        $lines = [];
        foreach ($board as $index => $cell) {
            $lines[$cell->y -1][$cell->x -1] = $cell;
        }
        return $lines;
    }

    private function marginLeft(string $text)
    {
        return sprintf('% ' . $this->marginLeft . 's', $text);
    }

    private function beforeLineRender()
    {
        $unit = '+---';
        $beforeLine = '';
        for ($i = 1; $i <= $this->game->boardSizeX; $i++) {
            $beforeLine .= $unit;
        }
        $beforeLine .= '+' . PHP_EOL;
        return $this->color($beforeLine, Color::Black, $this->bgColor);
    }

    private function cellRenderer(CellState $state)
    {
        $empty = $this->simple ? ' ' : '   ';
        $white = $this->simple ? 'w' : ' ● ';
        $black = $this->simple ? 'b' : ' ● ';
        return match($state) {
            CellState::EMPTY => $this->color($empty, Color::LightGray, $this->bgColor),
            CellState::WHITE => $this->color($white, Color::White, $this->bgColor),
            CellState::BLACK => $this->color($black, Color::Black, $this->bgColor)
        };
    }

    private function color($message, Color $color = Color::DEFAULT, Color $bgColor = Color::BG_DEFAULT)
    {
        return sprintf("\033[%d;%dm%s\033[m", $color->value, $bgColor->value, $message);
    }
}
