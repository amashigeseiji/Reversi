<?php
namespace Tenjuu99\Reversi\Renderer;

use ReflectionClass;
use ReflectionMethod;
use Tenjuu99\Reversi\Model\Game;
use Tenjuu99\Reversi\Model\Player;

class Api
{
    private Game $game;
    /**
     * @var array<string, ReflectionMethod[]>
     */
    private array $handler;

    public function __construct()
    {
        $this->game = Game::initialize(Player::WHITE);
        $this->setHandler();
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
            if ($invoker->getNumberOfParameters() > 0) {
                $invoker->invokeArgs($this, $args);
            } else {
                $invoker->invoke($this);
            }
        } else {
            header("HTTP/1.0 404 Not Found");
            echo 'Not found';
        }
    }

    /**
     * @Get
     */
    public function index()
    {
        $template = __DIR__ . '/../../template/index.html';
        require($template);
    }

    /**
     * @Post
     */
    public function move(string $index)
    {
    }

    /**
     * @Post
     */
    public function reset()
    {
        $this->game = Game::initialize(Player::WHITE);
    }

    /**
     * @Get
     */
    public function moves()
    {
        header('Content-Type: application/json');
        echo $this->game->moves();
    }

    /**
     * @Post
     */
    public function pass()
    {
    }

    /**
     * @Get
     */
    public function board()
    {
        header('Content-Type: application/json');
        echo $this->game->cells()->json();
    }
}
