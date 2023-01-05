<?php
namespace Tenjuu99\Reversi\Command;

use ReflectionClass;
use ReflectionMethod;
use Tenjuu99\Reversi\Model\Board;
use Tenjuu99\Reversi\Model\Game as ModelGame;
use Tenjuu99\Reversi\Model\GameState;
use Tenjuu99\Reversi\Model\History;
use Tenjuu99\Reversi\Model\Move;
use Tenjuu99\Reversi\Model\Player;
use Tenjuu99\Reversi\Renderer\Cli;

class Game
{
    private ModelGame $game;
    private Player $userPlayer;
    /** @var ReflectionMethod[] */
    private $commands = [];

    public int $boardSizeX;
    public int $boardSizeY;

    public bool $opponentComputer = true;
    public bool $auto = false;
    public float|int $sleep = 0.5;

    private Cli $cli;

    public function __construct(Cli $cli, Player $player, int $boardSizeX = 8, int $boardSizeY = 8)
    {
        $this->cli = $cli;
        $this->userPlayer = $player;
        $this->game = ModelGame::initialize($player, $boardSizeX, $boardSizeY);
        $reflection = new ReflectionClass($this);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        $commands = explode(' ', $this->help());
        $this->commands = array_filter($methods, fn($method) => in_array($method->name, $commands));
        $this->boardSizeX = $boardSizeX;
        $this->boardSizeY = $boardSizeY;
    }

    public function move(string $index)
    {
        $this->game->move($index);
    }

    public function pass()
    {
        $this->game->next();
    }

    public function reset() : bool
    {
        $this->auto = false;
        $this->game = ModelGame::initialize($this->userPlayer, $this->boardSizeX, $this->boardSizeY);
        return true;
    }

    public function moves() : string
    {
        return implode(' ', array_keys($this->game->moves()));
    }

    public function board(): Board
    {
        return $this->game->board();
    }

    public function isMyTurn() : bool
    {
        if ($this->auto) {
            return false;
        }
        return $this->userPlayer === $this->game->getCurrentPlayer();
    }

    public function currentPlayer(): string
    {
        return $this->game->getCurrentPlayer()->name;
    }

    public function help(): string
    {
        return 'move pass reset moves help history back computer auto simple exit resize';
    }

    public function invoke(string $input)
    {
        $commandInput = explode(' ', $input);
        $command = array_shift($commandInput);
        if (preg_match('/^\d-\d$/', $command)) {
            $commandInput = [$command];
            $command = 'move';
        }
        foreach ($this->commands as $method) {
            if ($command === $method->name) {
                return $method->invokeArgs($this, $commandInput);
            }
        }
        return "invalid command: {$input}. Commands: ["  . $this->help() . "]";
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

    public function history(): string
    {
        return implode(' ', array_values($this->game->history()));
    }

    public function back(string $hash)
    {
        $this->game->historyBack($hash);
    }

    public function computer(string $onoff)
    {
        $this->opponentComputer = $onoff === 'on' ? true : false;
    }

    public function auto(float|int $sleep = 1)
    {
        $this->opponentComputer = true;
        $this->auto = true;
        $this->sleep = $sleep;
        return true;
    }

    public function simple(): bool
    {
        $this->cli->simple = !$this->cli->simple;
        return true;
    }

    public function exit() : string
    {
        $this->cli->exit = true;
        return 'bye!';
    }

    public function resize(int $x, int $y) : bool
    {
        $this->boardSizeX = $x;
        $this->boardSizeY = $y;
        $this->reset();
        return true;
    }
}
