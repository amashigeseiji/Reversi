<?php
namespace Tenjuu99\Reversi\Renderer;

use Tenjuu99\Reversi\Model\GameState;
use Tenjuu99\Reversi\Model\Player;
use Tenjuu99\Reversi\Renderer\Cli\{Board, Renderer, Color, Command};

class Cli
{
    private Command $game;
    public bool $exit = false;

    private Renderer $renderer;

    public function __construct(int $boardSizeX = 8, int $boardSizeY = 8)
    {
        $this->game = new Command($this, Player::BLACK, $boardSizeX, $boardSizeY);
        $this->renderer = new Renderer(new Board($this->game));
    }

    public function play()
    {
        $this->renderer->clear();
        $this->renderer->board();
        while (true) {
            if ($this->exit) {
                break;
            }
            if ($this->game->isMyTurn() || !$this->game->opponentComputer) {
                $this->renderer->message('moves: ' . $this->game->moves() . PHP_EOL);
                $return = $this->command($this->game->currentPlayer() . ": ");
                if ($return) {
                    $this->renderer->message($return . PHP_EOL);
                    unset($return);
                } else {
                    $this->renderer->board();
                }
            } else {
                $message = $this->game->currentPlayer() . ": thinking... ";
                $this->renderer->message($message);
                $this->sleep($this->game->sleep);
                $command = $this->game->compute();
                $this->renderer->board();
                $this->renderer->message($message . $command . PHP_EOL);
            }
            switch($this->game->state()) {
            case GameState::WIN_WHITE:
                $this->renderer->message('White win!' . PHP_EOL);
                $return = $this->command('Input: ');
                $this->renderer->board();
                if (isset($return)) {
                    $this->renderer->message($return);
                }
                break;
            case GameState::WIN_BLACK:
                $this->renderer->message('Black win!' . PHP_EOL);
                $return = $this->command('Input: ');
                $this->renderer->board();
                if (isset($return)) {
                    $this->renderer->message($return);
                }
                break;
            case GameState::DRAW:
                $this->renderer->message('Draw!' . PHP_EOL);
                $return = $this->command('Input: ');
                $this->renderer->board();
                if (isset($return)) {
                    $this->renderer->message($return);
                }
                break;
            }
        }
        if ($this->exit) {
            exit;
        }
    }

    public function benchmark(int $count, array $strategies)
    {
        if ($strategies) {
            foreach ($strategies as $setting) {
                $this->game->strategy($setting['strategy'], $setting['searchLevel'], $setting['player']);
            }
        }
        $done = [];
        $white = 0;
        $black = 0;
        $passCount = [
            Player::WHITE->name => 0,
            Player::BLACK->name => 0,
        ];
        for ($i = 0; $i < $count; $i++) {
            $start = microtime(true);
            while ($this->game->state() === GameState::ONGOING) {
                $player = $this->game->currentPlayer();
                $move = $this->game->compute();
                if ($move === 'pass') {
                    $passCount[$player]++;
                }
            }
            switch($this->game->state()) {
            case GameState::WIN_WHITE:
                echo 'white win!';
                $white++;
                break;
            case GameState::WIN_BLACK:
                echo 'black win!';
                $black++;
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
        echo 'WHITE win: ' . $white . PHP_EOL;
        echo 'BLACK win: ' . $black . PHP_EOL;
        echo 'WHITE passed:' . $passCount[Player::WHITE->name] . PHP_EOL;
        echo 'BLACK passed:' . $passCount[Player::BLACK->name] . PHP_EOL;
        echo 'average: ' . array_sum($done) / count($done) . PHP_EOL;
    }

    public function simple()
    {
        $this->renderer->simple();
    }

    private function command(string $inputMessage = '')
    {
        $input = $this->renderer->command($inputMessage);
        return $this->game->invoke($input);
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
