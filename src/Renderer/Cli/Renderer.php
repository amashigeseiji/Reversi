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

    public function message(string $message)
    {
        $lines = $this->boardHeight() + 3;
        system("tput cup {$lines} 0");
        system("tput el");
        echo $message;
    }

    public function command(string $inputMessage = '') : void
    {
        readline_completion_function([$this->command, 'commandCompletion']);
        $lines = $this->boardHeight() + 2;
        $messageCount = count($this->command->getMessages());
        $lines += $messageCount;
        $input = readline($inputMessage);
        readline_add_history($input);
        $this->command->invoke($input);
        if ($input === 'reset' || $input === 'simple') {
            $this->clearFrom(0);
            $this->command->clearMessages();
        }
        $this->render();
        $messages = $this->command->getMessages();
        foreach ($messages as $message) {
            system('tput el');
            echo $message . PHP_EOL;
        }
        $this->command->clearMessages();
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
        $this->clearFrom($lines);
        $black = 'BLACK: ' . count($this->command->board()->black);
        $white = 'WHITE: ' . count($this->command->board()->white);
        echo $black . PHP_EOL;
        system('tput el');
        echo $white . PHP_EOL;
        if (!$this->command->auto) {
            $messages = $this->command->getMessages();
            foreach ($messages as $message) {
                system('tput el');
                echo $message . PHP_EOL;
            }
        }
        system("tput cnorm");
    }

    private function boardHeight() : int
    {
        return count(explode("\n", $this->cliBoard));
    }

    private function clearFrom(int $from = 0)
    {
        system("tput cup {$from} 0 && tput ed || tput cd");
    }
}
