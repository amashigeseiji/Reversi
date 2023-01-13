<?php
namespace Tenjuu99\Reversi\Renderer;

use ReflectionClass;
use ReflectionMethod;
use Tenjuu99\Reversi\Model\Player;
use Tenjuu99\Reversi\Reversi;

class Api
{
    /**
     * @var array<string, ReflectionMethod[]>
     */
    private array $handler;

    private Reversi $reversi;

    private $retry = 0;

    public function __construct()
    {
        session_start();
        $reversi = isset($_SESSION['reversi']) ? $_SESSION['reversi'] : new Reversi;
        $_SESSION['reversi'] = $reversi;
        $this->reversi = $reversi;
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
        [$move, $flip] = $this->reversi->move($index);
        header('Content-Type: application/json');
        echo $this->gameJson($move, $flip);
    }

    /**
     * @Post
     */
    public function reset(int $boardSizeX = 8, int $boardSizeY = 8)
    {
        $this->reversi = new Reversi($boardSizeX, $boardSizeY, $this->reversi->getStrategy());
        $_SESSION['reversi'] = $this->reversi;
    }

    /**
     * @Post
     */
    public function pass()
    {
        $this->reversi->pass();
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
        [$move, $flip] = $this->reversi->compute();
        header('Content-Type: application/json');
        echo $this->gameJson($move, $flip);
    }

    private function gameJson(?string $choice = '', ?array $flip = []) : string
    {
        $array = $this->reversi->toArray();
        $array['choice'] = $choice;
        $array['flippedCells'] = $flip;
        return json_encode($array);
    }

    /**
     * @Get
     */
    public function historyBack(string $hash)
    {
        $header = getallheaders();
        header('Cache-Control: max-age=31536000, must-revalidate');
        header('ETag: '. $hash .'');
        if (isset($header['If-None-Match']) && $header['If-None-Match'] === $hash && $this->reversi->hasHistory($hash)) {
            header('HTTP/1.1 304');
            return;
        }
        $this->reversi->historyBack($hash);
        header('Content-Type: application/json');
        echo $this->gameJson();
    }

    /**
     * @Post
     */
    public function strategy(string $strategy, string $player, ?int $searchLevel = null)
    {
        $strategies = $this->reversi->strategyList();
        $player = strtolower(Player::WHITE->name) === strtolower($player) ? Player::WHITE : Player::BLACK;
        $this->reversi->setStrategy($strategy, $player, $searchLevel);
        header('Content-Type: application/json');
        echo $this->gameJson();
    }
}
