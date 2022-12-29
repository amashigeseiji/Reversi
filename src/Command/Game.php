<?php
namespace Tenjuu99\Reversi\Command;

use ReflectionClass;
use ReflectionMethod;
use Tenjuu99\Reversi\Model\Board;
use Tenjuu99\Reversi\Model\Game as ModelGame;
use Tenjuu99\Reversi\Model\Move;
use Tenjuu99\Reversi\Model\Player;

class Game
{
    private ModelGame $game;
    /** @var ReflectionMethod[] */
    private $commands = [];

    public function __construct()
    {
        $this->game = ModelGame::initialize(Player::WHITE);
        $reflection = new ReflectionClass($this);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        $commands = explode(' ', $this->help());
        $this->commands = array_filter($methods, fn($method) => in_array($method->name, $commands));
    }

    public function put(string $index)
    {
        $move = new Move($index, $this->game->getPlayer());
        $this->game->put($move);
        $this->game->changePlayer();
    }

    public function pass()
    {
        $this->game->changePlayer();
    }

    public function reset()
    {
        $this->game = ModelGame::initialize(Player::WHITE);
    }

    public function moves() : string
    {
        $this->game->moves();
        return '1-2 2-3';
        // return implode(' ', $this->game->moves());
    }

    public function board(): Board
    {
        return $this->game->getBoardState();
    }

    public function player(): string
    {
        return $this->game->getPlayer()->name;
    }

    public function help(): string
    {
        return 'put pass reset moves help';
    }

    public function invoke(string $input)
    {
        $commandInput = explode(' ', $input);
        $command = array_shift($commandInput);
        foreach ($this->commands as $method) {
            if ($command === $method->name) {
                return $method->invokeArgs($this, $commandInput);
            }
        }
    }
}
