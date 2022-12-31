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
                        $handler['post'][$method->name] = $method;
                        continue;
                    }
                    if (strpos($line, '@get') === 0) {
                        $handler['get'][$method->name] = $method;
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
    }

    /**
     * @Post
     */
    public function reset(int $boardSizeX = 8, int $boardSizeY = 8)
    {
        if (isset($_SESSION['game'])) {
            $_SESSION['game'] = Game::initialize(Player::WHITE, $boardSizeX, $boardSizeY);
        }
    }

    /**
     * @Get
     */
    public function moves()
    {
        header('Content-Type: application/json');
        echo $this->game()->moves();
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
    }

    private function gameJson()
    {
        $moves = [];
        foreach ($this->game()->moves() as $move) {
            $moves[$move->index] = [
                'index' => $move->index,
                'flipCells' => array_map(fn($c) => $c->index, $move->flipCells),
                'player' => $move->player->name
            ];
        }
        return json_encode([
            'board' => iterator_to_array($this->game()->cells()),
            'boardSize' => ['x' => $this->game()->cells()->xMax, 'y' => $this->game()->cells()->yMax],
            'moves' => $moves ?: ['pass'],
            'state' => $this->game()->state()->value,
            'currentPlayer' => $this->game()->getPlayer()->name,
            'userColor' => $this->game()
        ]);
    }
}