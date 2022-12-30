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
    private Player $player;
    /** @var ReflectionMethod[] */
    private $commands = [];

    public function __construct(Player $player)
    {
        $this->player = $player;
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
        $this->game = ModelGame::initialize($this->player);
    }

    public function moves() : string
    {
        $moves = $this->game->moves();
        return implode(' ', array_map(fn(Move $move) => $move->index, $moves));
    }

    public function cells(): Board
    {
        return $this->game->cells();
    }

    public function isMyTurn() : bool
    {
        return $this->player === $this->game->getPlayer();
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

    public function thinkAndMove()
    {
        $moves = $this->game->moves();
        if ($moves) {
            $this->put($moves[0]->index);
        } else {
            $this->pass();
        }
    }
}
