<?php
namespace Tenjuu99\Reversi\Renderer\Cli;

use Tenjuu99\Reversi\Command\Game;

class Renderer
{
    public bool $simple = false;
    private $filename;

    private Board $cliBoard;
    private int $marginLeft = 3;

    public function __construct(Board $cliBoard)
    {
        $this->cliBoard = $cliBoard;
        $this->filename = sys_get_temp_dir() . '/reversi_buffer';
    }

    public function clear()
    {
        echo chr(27).chr(91).'H'.chr(27).chr(91).'J';
    }

    public function board()
    {
        $string = (string)$this->cliBoard;
        file_put_contents($this->filename, $string);
        system("tput cup 0 0 && tput civis && cat {$this->filename} && tput cnorm");
    }

    public function message(string $message, bool $return = false)
    {
        $message = $this->simple ? $message : $this->marginLeft(' ') . $message;
        if ($return) {
            return $message;
        } else {
            file_put_contents($this->filename, $message, FILE_APPEND);
            system("tput cup 0 0 && tput civis && cat {$this->filename} && tput cnorm");
        }
    }

    public function command(string $inputMessage = '') : string
    {
        ob_start();
        $input = readline($this->message($inputMessage, true));
        readline_add_history($input);
        $content = ob_get_clean();
        file_put_contents($this->filename, $content, FILE_APPEND);
        system("tput cup 0 0 && tput civis && cat {$this->filename} && tput cnorm");
        $this->clear();
        return $input;
    }

    private function marginLeft(string $text)
    {
        return sprintf('% ' . $this->marginLeft . 's', $text);
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
}
