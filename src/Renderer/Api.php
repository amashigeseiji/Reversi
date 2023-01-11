<?php
namespace Tenjuu99\Reversi\Renderer;

use ReflectionClass;
use ReflectionMethod;
use Tenjuu99\Reversi\AI\Ai;
use Tenjuu99\Reversi\Model\Game;
use Tenjuu99\Reversi\Model\Histories;
use Tenjuu99\Reversi\Model\Player;

class Api
{
    /**
     * @var array<string, ReflectionMethod[]>
     */
    private array $handler;

    private Ai $ai;

    private $retry = 0;

    public function __construct()
    {
        session_start();
        $this->ai = new Ai();
        $this->setHandler();
    }

    private function game() : Game
    {
        if (isset($_SESSION['game'])) {
            return $_SESSION['game'];
        }
        $histories = $this->history();
        if ($last = $histories->last()) {
            $game = Game::fromHistory($last);
            return $game;
        }
        $game = Game::initialize(Player::BLACK);
        $this->history()->push($game->toHistory());
        return $_SESSION['game'] = $game;
    }

    public function history() : Histories
    {
        if (isset($_SESSION['history'])) {
            return $_SESSION['history'];
        }
        return $_SESSION['history'] = new Histories();
    }

    private function setHandler()
    {
        $reflection = new ReflectionClass($this);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        $handler = [
            'get' => [],
            'post' => [],
        ];
        foreach ($methods as $method) {
            $doc = $method->getDocComment();
            if ($doc) {
                $comment = trim(str_replace(['/', '*'], '', $doc));
                $lines = explode("\n", $comment);
                foreach($lines as $line) {
                    $line = trim(strtolower($line));
                    if (strpos($line, '@post') === 0) {
                        $handler['post'][strtolower($method->name)] = $method;
                        continue;
                    }
                    if (strpos($line, '@get') === 0) {
                        $handler['get'][strtolower($method->name)] = $method;
                        continue;
                    }
                }
            }
        }
        $this->handler = $handler;
    }

    public function handle(array $request)
    {
        $method = strtolower($request['REQUEST_METHOD']);
        $uri = explode('?', trim(strtolower($request['REQUEST_URI']), '/'));
        $uri = explode('/', $uri[0]);
        $uriFirst = array_shift($uri);
        if ($uriFirst === "") {
            $uriFirst = 'index';
        }
        try {
            if (isset($this->handler[$method][$uriFirst])) {
                $invoker = $this->handler[$method][$uriFirst];
                $args = $method === 'get' ? $_GET : $_POST;
                $this->render($invoker, $args);
            } else {
                header("HTTP/1.0 404 Not Found");
                echo 'Not found';
            }
        } catch (\Throwable $e) {
            $this->retry++;
            $this->reset();
            if ($this->retry <= 3) {
                $this->handle($request);
            }
        }
    }

    private function render(ReflectionMethod $invoker, array $args = [])
    {
        ob_start();
        if ($invoker->getNumberOfParameters() > 0) {
            $invoker->invokeArgs($this, $args);
        } else {
            $invoker->invoke($this);
        }
        ob_end_flush();
    }

    /**
     * @Get
     */
    public function index()
    {
        $template = __DIR__ . '/../../template/index.html';
        $gameJson = $this->gameJson();
        require($template);
    }

    /**
     * @Post
     */
    public function move(string $index)
    {
        $moves = $this->game()->moves();
        $flip = isset($moves[$index]) ? $moves[$index]->flipCells : [];
        $this->game()->move($index);
        $this->history()->push($this->game()->toHistory());
        header('Content-Type: application/json');
        echo $this->gameJson($index, $flip);
    }

    /**
     * @Post
     */
    public function reset(int $boardSizeX = 8, int $boardSizeY = 8)
    {
        if (isset($_SESSION['history'])) {
            $_SESSION['history'] = new Histories;
        }
        if (isset($_SESSION['game'])) {
            $_SESSION['game'] = Game::initialize(Player::BLACK, $boardSizeX, $boardSizeY);
        }
    }

    /**
     * @Post
     */
    public function pass()
    {
        $this->game()->next();
        $this->history()->push($this->game()->toHistory());
        header('Content-Type: application/json');
        echo $this->gameJson();
    }

    /**
     * @Get
     */
    public function board()
    {
        header('Content-Type: application/json');
        echo $this->gameJson();
    }

    /**
     * @Post
     */
    public function compute()
    {
        $strategy = $this->getStrategy($this->game()->getCurrentPlayer());
        $move = $this->ai->choice($this->game(), $strategy['strategy'], $strategy['searchLevel']);
        $flip = [];
        if ($move === 'pass') {
            $this->game()->next();
        } else {
            $moves = $this->game()->moves();
            $flip = $moves[$move]->flipCells;
            $this->game()->move($move);
        }
        $this->history()->push($this->game()->toHistory());
        header('Content-Type: application/json');
        echo $this->gameJson($move, $flip);
    }

    private function gameJson(string $choice = '', array $flip = [])
    {
        $moves = $this->game()->moves();
        $board = $this->game()->board()->toArrayForJson();
        $histories = [];
        foreach ($this->history() as $hash => $history) {
            $histories[$history->moveCount] = $hash;
        }
        $data = [
            'board' => $board,
            'moves' => $moves->hasMoves() ? $moves->getAll() : ['pass' => 'pass'],
            'state' => $this->game()->state()->value,
            'end' => $this->game()->isGameEnd() ? 1 : 0,
            'currentPlayer' => $this->game()->getCurrentPlayer()->name,
            'userColor' => $this->game(),
            'history' => $histories,
            'moveCount' => $this->game()->moveCount(),
            'strategy' => $this->getStrategy(),
            'choice' => $choice,
            'flippedCells' => $flip,
            'nodeCount' => $this->ai->nodeCount,
        ];
        if (DEBUG) {
            $data['memoryUsage'] = number_format((memory_get_usage() / 1000)) . 'KB';
        }
        return json_encode($data);
    }

    /**
     * @Post
     */
    public function historyBack(string $hash)
    {
        $histories = $this->history();
        if ($histories->has($hash)) {
            $_SESSION['game'] = Game::fromHistory($histories->get($hash));
        }
        header('Content-Type: application/json');
        echo $this->gameJson();
    }

    private function getStrategy(?Player $player = null) : array
    {
        if (!isset($_SESSION['strategy'])) {
            $_SESSION['strategy'] = [
                Player::WHITE->name => ['strategy' => 'alphabeta', 'searchLevel' => 5],
                Player::BLACK->name => ['strategy' => 'alphabeta', 'searchLevel' => 5],
            ];
        }
        if ($player) {
            return $_SESSION['strategy'][$player->name];
        }
        return $_SESSION['strategy'];
    }

    private function setStrategy(string $strategy, Player $player, ?int $searchLevel = null)
    {
        $strategies = $this->ai->strategies();
        if (!in_array($strategy, $strategies)) {
            return;
        }
        $_SESSION['strategy'][$player->name]['strategy'] = $strategy;
        if (!is_null($searchLevel) && $searchLevel > 0) {
            $_SESSION['strategy'][$player->name]['searchLevel'] = $searchLevel;
        }
    }

    /**
     * @Post
     */
    public function strategy(string $strategy, string $player, ?int $searchLevel = null)
    {
        $strategies = $this->ai->strategies();
        $player = strtolower(Player::WHITE->name) === strtolower($player) ? Player::WHITE : Player::BLACK;
        $this->setStrategy($strategy, $player, $searchLevel);
        header('Content-Type: application/json');
        echo $this->gameJson();
    }
}
