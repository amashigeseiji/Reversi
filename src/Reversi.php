<?php
namespace Tenjuu99\Reversi;

use Tenjuu99\Reversi\AI\Ai;
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

    public function __construct()
    {
        $this->ai = new Ai();
        $this->game = Game::initialize(Player::BLACK);
        $this->history = new Histories;
        $this->strategy = [
            Player::WHITE->name => $this->defaultStrategy,
            Player::BLACK->name => $this->defaultStrategy,
        ];
    }

    public function newGame(int $xSize = 8, int $ySize = 8)
    {
        $this->game = Game::initialize(Player::BLACK, $xSize, $ySize);
    }

    public function getBoard() : Board
    {
        return $this->game->board();
    }

    public function move(string $index)
    {
        $moves = $this->game->moves();
        $flip = isset($moves[$index]) ? $moves[$index]->flipCells : [];
        $this->game->move($index);
        $this->history->push($this->game->toHistory());
    }

    public function pass()
    {
        $this->game->next();
        $this->history->push($this->game->toHistory());
    }

    public function compute() : array
    {
        $strategy = $this->getStrategy($this->game->getCurrentPlayer());
        $move = $this->ai->choice($this->game, $strategy['strategy'], $strategy['searchLevel']);
        $flip = [];
        if ($move === 'pass') {
            $this->pass();
        } else {
            $moves = $this->game->moves();
            $flip = $moves[$move]->flipCells;
            $this->game->move($move);
        }
        $this->history->push($this->game->toHistory());
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
            $this->game = Game::fromHistory($histories->get($hash));
        }
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
            'state' => $this->game->state()->value,
            'end' => $this->game->isGameEnd() ? 1 : 0,
            'currentPlayer' => $this->game->getCurrentPlayer()->name,
            // 'userColor' => $this->game,
            'history' => $histories,
            'moveCount' => $this->game->moveCount(),
            'strategy' => $this->getStrategy(),
            // 'choice' => $choice,
            // 'flippedCells' => $flip,
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

    public function getMoves() : Moves
    {
        return $this->game->moves();
    }

    public function gameState() : GameState
    {
        return $this->game->state();
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
