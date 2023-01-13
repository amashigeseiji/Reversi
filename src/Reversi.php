<?php
namespace Tenjuu99\Reversi;

use Tenjuu99\Reversi\AI\Ai;
use Tenjuu99\Reversi\Error\InvalidMoveException;
use Tenjuu99\Reversi\Model\Board;
use Tenjuu99\Reversi\Model\Game;
use Tenjuu99\Reversi\Model\GameState;
use Tenjuu99\Reversi\Model\Histories;
use Tenjuu99\Reversi\Model\Moves;
use Tenjuu99\Reversi\Model\Player;

class Reversi
{
    private Ai $ai;
    private Histories $history;
    private Game $game;

    private array $strategy;
    private array $defaultStrategy = ['strategy' => 'alphabeta', 'searchLevel' => 5];
    private bool $suspend = false;

    public function __construct(int $boardSizeX = 8, int $boardSizeY = 8, array $strategies = [])
    {
        $this->ai = new Ai();
        $this->game = Game::initialize(Player::BLACK, $boardSizeX, $boardSizeY);
        $this->history = new Histories;
        $this->strategy = $strategies ?: [
            Player::WHITE->name => $this->defaultStrategy,
            Player::BLACK->name => $this->defaultStrategy,
        ];
        $this->history->push($this->game->toHistory());
    }

    public function newGame(int $xSize = 8, int $ySize = 8)
    {
        $this->history = new Histories;
        $this->game = Game::initialize(Player::BLACK, $xSize, $ySize);
    }

    public function getBoard() : Board
    {
        return $this->game->board();
    }

    public function move(string $index)
    {
        if ($this->suspend) {
            return;
        }
        $moves = $this->game->moves();
        if (!isset($moves[$index])) {
            throw new InvalidMoveException("Invalid move: {$index}");
        }
        $flip = isset($moves[$index]) ? $moves[$index]->flipCells : [];
        $this->game = $this->game->node($index);
        $this->history->push($this->game->toHistory());
        return [$index, $flip];
    }

    public function pass()
    {
        if ($this->suspend) {
            return;
        }
        $this->game = $this->game->node('pass');
        $this->history->push($this->game->toHistory());
    }

    public function compute() : array
    {
        if ($this->suspend) {
            return ['suspend'];
        }
        $strategy = $this->getStrategy($this->game->getCurrentPlayer());
        $move = $this->ai->choice($this->game, $strategy['strategy'], $strategy['searchLevel']);
        $flip = [];
        if ($move === 'pass') {
            $this->pass();
        } else {
            [$move, $flip] = $this->move($move);
        }
        return [$move, $flip];
    }

    public function getStrategy(?Player $player = null) : array
    {
        if ($player) {
            return $this->strategy[$player->name];
        }
        return $this->strategy;
    }

    public function setStrategy(string $strategy, Player $player, ?int $searchLevel = null)
    {
        $strategies = $this->ai->strategies();
        if (!in_array($strategy, $strategies)) {
            return;
        }
        $this->strategy[$player->name]['strategy'] = $strategy;
        if (!is_null($searchLevel) && $searchLevel > 0) {
            $this->strategy[$player->name]['searchLevel'] = $searchLevel;
        }
    }

    public function historyBack(string $hash)
    {
        $histories = $this->history;
        if ($histories->has($hash)) {
            $this->suspend = $hash !== $this->history->last()->hash;
            $this->game = Game::fromHistory($histories->get($hash));
        }
    }

    public function resume()
    {
        $this->suspend = false;
        $this->game = Game::fromHistory($this->history->last());
    }

    public function toArray() : array
    {
        $moves = $this->game->moves();
        $board = $this->game->board()->toArrayForJson();
        $histories = [];
        foreach ($this->history as $hash => $history) {
            $histories[$history->moveCount] = $hash;
        }
        $data = [
            'board' => $board,
            'moves' => $moves->hasMoves() ? $moves->getAll() : ['pass' => 'pass'],
            'state' => $this->game->state->value,
            'end' => $this->game->isGameEnd ? 1 : 0,
            'currentPlayer' => $this->game->getCurrentPlayer()->name,
            'history' => $histories,
            'moveCount' => $this->game->moveCount(),
            'strategy' => $this->getStrategy(),
            'nodeCount' => $this->ai->nodeCount,
        ];
        if (DEBUG) {
            $data['memoryUsage'] = number_format((memory_get_usage() / 1000)) . 'KB';
        }
        return $data;
    }

    public function strategyList() : array
    {
        return $this->ai->strategies();
    }

    public function clearHistory()
    {
        $this->history->clear();
    }

    public function hasHistory(string $hash)
    {
        return $this->history->has($hash);
    }

    public function getMoves() : Moves
    {
        return $this->game->moves();
    }

    public function gameState() : GameState
    {
        return $this->game->state;
    }

    public function getHistoriesHashList(): array
    {
        return array_keys(iterator_to_array($this->history));
    }

    public function getCurrentPlayer(): Player
    {
        return $this->game->getCurrentPlayer();
    }
}
