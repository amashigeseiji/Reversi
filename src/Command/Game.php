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
    private Player $userPlayer;
    /** @var ReflectionMethod[] */
    private $commands = [];

    public function __construct(Player $player)
    {
        $this->userPlayer = $player;
        $this->game = ModelGame::initialize($player);
        $reflection = new ReflectionClass($this);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        $commands = explode(' ', $this->help());
        $this->commands = array_filter($methods, fn($method) => in_array($method->name, $commands));
    }

    public function put(string $index)
    {
        if ($this->game->move($index)) {
            $this->game->changePlayer();
        }
    }

    public function pass()
    {
        $this->game->changePlayer();
    }

    public function reset()
    {
        $this->game = ModelGame::initialize($this->userPlayer);
    }

    public function moves() : string
    {
        return $this->game->moves();
    }

    public function cells(): Board
    {
        return $this->game->cells();
    }

    public function isMyTurn() : bool
    {
        return $this->userPlayer === $this->game->getPlayer();
    }

    public function currentPlayer(): string
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

    public function compute()
    {
        $moves = $this->game->moves();
        if ($moves) {
            $key = array_rand(iterator_to_array($moves));
            $this->put($moves[$key]->index);
        } else {
            $this->pass();
        }
    }
}
