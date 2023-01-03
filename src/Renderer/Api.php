<?php
namespace Tenjuu99\Reversi\Renderer;

use ReflectionClass;
use ReflectionMethod;
use Tenjuu99\Reversi\Model\Game;
use Tenjuu99\Reversi\Model\Player;

class Api
{
    /**
     * @var array<string, ReflectionMethod[]>
     */
    private array $handler;

    private string $strategy = 'random';

    public function __construct()
    {
        session_start();
        $this->setHandler();
    }

    private function game() : Game
    {
        if (isset($_SESSION['game'])) {
            return $_SESSION['game'];
        }
        return $_SESSION['game'] = Game::initialize(Player::WHITE);
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
        if (isset($this->handler[$method][$uriFirst])) {
            $invoker = $this->handler[$method][$uriFirst];
            $args = $method === 'get' ? $_GET : $_POST;
            $this->render($invoker, $args);
        } else {
            header("HTTP/1.0 404 Not Found");
            echo 'Not found';
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
        $this->game()->move($index);
        header('Content-Type: application/json');
        echo $this->gameJson();
    }

    /**
     * @Post
     */
    public function reset(int $boardSizeX = 8, int $boardSizeY = 8, string $strategy = 'random')
    {
        if (isset($_SESSION['game'])) {
            $_SESSION['game'] = Game::initialize(Player::WHITE, $boardSizeX, $boardSizeY, $strategy);
            $this->strategy = $strategy;
        }
    }

    /**
     * @Post
     */
    public function pass()
    {
        $this->game()->next();
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
        $this->game()->compute();
        header('Content-Type: application/json');
        echo $this->gameJson();
    }

    private function gameJson()
    {
        $moves = [];
        foreach ($this->game()->moves() as $move) {
            $moves[$move->index] = [
                'index' => $move->index,
                'flipCells' => $move->flipCells,
            ];
        }
        $board = $this->game()->cells()->toArray();
        $data = [
            'board' => $board,
            'white' => count($board['white']),
            'black' => count($board['black']),
            'boardSize' => ['x' => $this->game()->cells()->xMax, 'y' => $this->game()->cells()->yMax],
            'moves' => $moves ?: ['pass' => 'pass'],
            'state' => $this->game()->state()->value,
            'currentPlayer' => $this->game()->getPlayer()->name,
            'userColor' => $this->game(),
            'strategy' => $this->strategy,
            'history' => $this->game()->history(),
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
        $this->game()->historyBack($hash);
        header('Content-Type: application/json');
        echo $this->gameJson();
    }
}
