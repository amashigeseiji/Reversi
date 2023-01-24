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
        $this->renderer = new Renderer(new Board($this->game), $this->game);
    }

    public function play()
    {
        $this->renderer->clear();
        while (true) {
            if ($this->exit) {
                break;
            }
            $this->renderer->render();
            if ($this->game->isMyTurn() || !$this->game->opponentComputer) {
                $this->renderer->command(">> ");
            } else {
                if ($this->game->state() === GameState::ONGOING) {
                    $this->sleep($this->game->sleep);
                    $this->game->compute();
                } else {
                    $this->renderer->command(">> ");
                }
            }
            if ($this->game->state() !== GameState::ONGOING) {
                $this->game->clearMessages();
                switch($this->game->state()) {
                case GameState::WIN_WHITE:
                    $this->game->pushMessage('White win!' . PHP_EOL);
                    break;
                case GameState::WIN_BLACK:
                    $this->game->pushMessage('Black win!' . PHP_EOL);
                    break;
                case GameState::DRAW:
                    $this->game->pushMessage('Draw!' . PHP_EOL);
                    break;
                }
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
                $this->game->strategy($setting['strategy'], $setting['searchLevel'], $setting['endgameThreshold'], $setting['player']);
            }
        }
        $done = [];
        $white = 0;
        $black = 0;
        $passCount = [
            Player::WHITE->name => 0,
            Player::BLACK->name => 0,
        ];
        $corner = [
            Player::WHITE->name => 0,
            Player::BLACK->name => 0,
        ];
        $cornerCell = array_flip($this->game->board()->corner());
        for ($i = 0; $i < $count; $i++) {
            $start = microtime(true);
            while ($this->game->state() === GameState::ONGOING) {
                $player = $this->game->currentPlayer();
                $move = $this->game->compute();
                if ($move === 'pass') {
                    $passCount[$player]++;
                } elseif (isset($cornerCell[$move])) {
                    $corner[$player]++;
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
        echo 'WHITE passed:' . $passCount[Player::WHITE->name] . ', corner: ' . $corner[Player::WHITE->name] . PHP_EOL;
        echo 'BLACK passed:' . $passCount[Player::BLACK->name] . ', corner: ' . $corner[Player::BLACK->name] . PHP_EOL;
        echo 'average: ' . array_sum($done) / count($done) . PHP_EOL;
    }

    public function simple()
    {
        $this->renderer->simple();
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
