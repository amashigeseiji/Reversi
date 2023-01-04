<?php
namespace Tenjuu99\Reversi\Command;

use ReflectionClass;
use ReflectionMethod;
use Tenjuu99\Reversi\Model\Board;
use Tenjuu99\Reversi\Model\Game as ModelGame;
use Tenjuu99\Reversi\Model\GameState;
use Tenjuu99\Reversi\Model\Move;
use Tenjuu99\Reversi\Model\Player;

class Game
{
    private ModelGame $game;
    private Player $userPlayer;
    /** @var ReflectionMethod[] */
    private $commands = [];

    private int $boardSizeX;
    private int $boardSizeY;
    private string $strategy;

    public function __construct(Player $player, int $boardSizeX = 8, int $boardSizeY = 8, string $strategy = 'random')
    {
        $this->userPlayer = $player;
        $this->game = ModelGame::initialize($player, $boardSizeX, $boardSizeY, $strategy);
        $reflection = new ReflectionClass($this);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        $commands = explode(' ', $this->help());
        $this->commands = array_filter($methods, fn($method) => in_array($method->name, $commands));
        $this->boardSizeX = $boardSizeX;
        $this->boardSizeY = $boardSizeY;
        $this->strategy = $strategy;
    }

    public function move(string $index)
    {
        $this->game->move($index);
    }

    public function pass()
    {
        $this->game->next();
    }

    public function reset()
    {
        $this->game = ModelGame::initialize($this->userPlayer, $this->boardSizeX, $this->boardSizeY, $this->strategy);
    }

    public function moves() : string
    {
        return implode(' ', array_keys($this->game->moves()));
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
        return 'move pass reset moves help';
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

    public function compute() : string
    {
        return $this->game->compute();
    }

    public function isGameEnd() : bool
    {
        return $this->game->state() !== GameState::ONGOING;
    }

    public function state() : GameState
    {
        return $this->game->state();
    }
}
