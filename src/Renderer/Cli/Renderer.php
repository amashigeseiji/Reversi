<?php
namespace Tenjuu99\Reversi\Renderer\Cli;

use Tenjuu99\Reversi\Model\Player;

class Renderer
{
    public bool $simple = false;

    private Board $cliBoard;
    private Command $command;

    public function __construct(Board $cliBoard, Command $command)
    {
        $this->cliBoard = $cliBoard;
        $this->command = $command;
    }

    public function clear()
    {
        echo chr(27).chr(91).'H'.chr(27).chr(91).'J';
    }

    public function command(string $inputMessage = '') : void
    {
        $lines = $this->boardHeight() + 3;
        // $lines += count($this->command->getMessages());
        $this->clearFrom($lines);
        readline_completion_function([$this->command, 'commandCompletion']);
        system("tput cup {$lines} {$this->cliBoard->marginLeft}");
        $input = readline($inputMessage);
        readline_add_history($input);
        $this->command->invoke($input);
        if ($input === 'reset' || $input === 'simple' || $input === 'resize') {
            $this->clearFrom(0);
            $this->command->clearMessages();
            $this->oldMessage = [];
        }
        $this->render();
    }

    public function simple()
    {
        $simple = !$this->simple;
        $this->simple = $simple;
        $this->cliBoard->simple($simple);
    }

    public function __destruct()
    {
        system("tput cnorm");
    }

    public function render()
    {
        system("tput civis && tput cup 0 0");
        echo $this->cliBoard;

        $lines = $this->boardHeight();
        $black = 'BLACK: ' . count($this->command->board()->black);
        $white = 'WHITE: ' . count($this->command->board()->white);
        system("tput cup {$lines} {$this->cliBoard->marginLeft}");
        echo $black . PHP_EOL;
        $lines++;
        system("tput cup {$lines} {$this->cliBoard->marginLeft}");
        echo $white . PHP_EOL;

        system('tput cup 1 50');
        echo 'MESSAGE:' . PHP_EOL;
        $i = 2;
        if ($this->command->messageCount() > ($this->boardHeight() - 4)) {
            $this->command->shiftMessage();
        }
        $messages = $this->command->getMessages();
        foreach ($messages as $k => $message) {
            $line = $i + $k;
            system("tput cup {$line} 50 && tput el");
            echo $message . PHP_EOL;
        }
        system("tput cnorm");
    }

    private function boardHeight() : int
    {
        return count(explode("\n", $this->cliBoard));
    }

    private function clearFrom(int $from = 0)
    {
        system("tput sc && tput cup {$from} 0 && tput cd && tput rc");
    }
}
