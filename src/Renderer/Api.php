<?php
namespace Tenjuu99\Reversi\Renderer;

use ReflectionClass;
use ReflectionMethod;
use Tenjuu99\Reversi\Application\Http;
use Tenjuu99\Reversi\Model\Player;
use Tenjuu99\Reversi\Reversi;

#[Http]
class Api
{
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
            $attributes = $method->getAttributes();
            if ($attributes) {
                foreach ($attributes as $attribute) {
                    if ($attribute->getName() === Http::class) {
                        $http = $attribute->newInstance();
                        $handler[$http->method][strtolower($method->name)] = ['invoker' => $method, 'attribute' => $http];
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

    private function render(array $invoker, array $args = [])
    {
        /** @var ReflectionMethod $method */
        $method = $invoker['invoker'];
        /** @var Http $http */
        $http = $invoker['attribute'];
        ob_start();
        if ($http->contentType) {
            header('Content-Type: ' . $http->contentType);
        }
        if ($method->getNumberOfParameters() > 0) {
            $method->invokeArgs($this, $args);
        } else {
            $method->invoke($this);
        }
        ob_end_flush();
    }

    #[Http(method: "Get", contentType: 'text/html')]
    public function index()
    {
        $template = __DIR__ . '/../../template/index.html';
        $gameJson = $this->gameJson();
        require($template);
    }

    #[Http(method: "post", contentType: 'application/json')]
    public function move(string $index)
    {
        [$move, $flip] = $this->reversi->move($index);
        echo $this->gameJson($move, $flip);
    }

    #[Http(method: "post")]
    public function reset(int $boardSizeX = 8, int $boardSizeY = 8)
    {
        $this->reversi = new Reversi($boardSizeX, $boardSizeY, $this->reversi->getStrategy());
        $_SESSION['reversi'] = $this->reversi;
    }

    #[Http(method: "post", contentType: 'application/json')]
    public function pass()
    {
        $this->reversi->pass();
        echo $this->gameJson();
    }

    #[Http(method: "get", contentType: 'application/json')]
    public function board()
    {
        echo $this->gameJson();
    }

    #[Http(method: "post", contentType: 'application/json')]
    public function compute()
    {
        [$move, $flip] = $this->reversi->compute();
        if ($move === 'suspend') {
            http_response_code(403);
            echo json_encode(['error' => 'suspend']);
            return;
        }
        echo $this->gameJson($move, $flip);
    }

    private function gameJson(?string $choice = '', ?array $flip = []) : string
    {
        $array = $this->reversi->toArray();
        $array['choice'] = $choice;
        $array['flippedCells'] = $flip;
        return json_encode($array);
    }

    #[Http(method: "get", contentType: 'application/json')]
    public function historyBack(string $hash)
    {
        $header = getallheaders();
        header('Cache-Control: max-age=3600, immutable, private');
        header('ETag: '. $hash .'');
        if (isset($header['If-None-Match']) && $header['If-None-Match'] === $hash && $this->reversi->hasHistory($hash)) {
            http_response_code(304);
            return;
        }
        $this->reversi->historyBack($hash);
        echo $this->gameJson();
    }

    #[Http(method: "post", contentType: 'application/json')]
    public function strategy(string $strategy, string $player, ?int $searchLevel = null)
    {
        $strategies = $this->reversi->strategyList();
        $player = strtolower(Player::WHITE->name) === strtolower($player) ? Player::WHITE : Player::BLACK;
        $this->reversi->setStrategy($strategy, $player, $searchLevel);
        echo $this->gameJson();
    }

    #[Http(method: "get", contentType: 'application/json')]
    public function resume()
    {
        $this->reversi->resume();
        echo $this->gameJson();
    }
}
