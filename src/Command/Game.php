<?php
namespace Tenjuu99\Reversi\Command;

use ReflectionClass;
use ReflectionMethod;
use Tenjuu99\Reversi\AI\Ai;
use Tenjuu99\Reversi\Model\Board;
use Tenjuu99\Reversi\Model\Game as ModelGame;
use Tenjuu99\Reversi\Model\GameState;
use Tenjuu99\Reversi\Model\History;
use Tenjuu99\Reversi\Model\Move;
use Tenjuu99\Reversi\Model\Player;
use Tenjuu99\Reversi\Renderer\Cli;

class Game
{
    private ModelGame $game;
    private Player $userPlayer;
    /** @var ReflectionMethod[] */
    private $commands = [];

    public int $boardSizeX;
    public int $boardSizeY;
    private array $strategy = [
        'white' => ['strategy' => 'random', 'searchLevel' => 2],
        'black' => ['strategy' => 'ramdom', 'searchLevel' => 2],
    ];

    public bool $opponentComputer = true;
    public bool $auto = false;
    public float|int $sleep = 0;

    private Cli $cli;
    private Ai $ai;

    public function __construct(Cli $cli, Player $player, int $boardSizeX = 8, int $boardSizeY = 8)
    {
        $this->cli = $cli;
        $this->userPlayer = $player;
        $this->game = ModelGame::initialize($player, $boardSizeX, $boardSizeY);
        $reflection = new ReflectionClass($this);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        $commands = explode(' ', $this->help());
        foreach ($methods as $method) {
            $methodName = strtolower($method->name);
            if (in_array($methodName, $commands)) {
                $this->commands[strtolower($method->name)] = $method;
            }
        }
        $this->boardSizeX = $boardSizeX;
        $this->boardSizeY = $boardSizeY;
        $this->ai = new Ai();
    }

    /**
     * Usage:
     * - move [index] 手を実行します。 "1-1" のように move コマンドを省略しても問題ありません。
     */
    public function move(string $index)
    {
        $this->game->move($index);
    }

    /**
     * Usage:
     * - pass 相手の手番になります
     */
    public function pass()
    {
        $this->game->next();
    }

    /**
     * Usage:
     * - reset 盤面を初期化します
     */
    public function reset()
    {
        $this->auto = false;
        $this->game = ModelGame::initialize($this->userPlayer, $this->boardSizeX, $this->boardSizeY);
    }

    public function moves() : string
    {
        return '[' . implode(' ', array_keys($this->game->moves())) . ']';
    }

    public function board(): Board
    {
        return $this->game->board();
    }

    public function isMyTurn() : bool
    {
        if ($this->auto) {
            return false;
        }
        return $this->userPlayer === $this->game->getCurrentPlayer();
    }

    public function currentPlayer(): string
    {
        return $this->game->getCurrentPlayer()->name;
    }

    /**
     * Usage:
     * - help コマンドを出力します
     * - help [command] 指定された command のヘルプを出力します
     */
    public function help(string $command = null): string
    {
        $commands = [
            'move',
            'pass',
            'reset',
            'moves',
            'help',
            'history',
            'back',
            'computer',
            'auto',
            'simple',
            'exit',
            'resize',
            'strategy',
            'wait',
        ];
        if (!$command) {
            return implode(' ', $commands);
        }
        if (isset($this->commands[$command])) {
            $doc = $this->commands[$command]->getDocComment();
            if (!$doc) {
                return '';
            }
            return trim(str_replace(['/', '*'], '', $doc));
        }
        return '';
    }

    public function invoke(string $input)
    {
        $commandInput = explode(' ', $input);
        $command = array_shift($commandInput);
        if (preg_match('/^\d+-\d+$/', $command)) {
            $commandInput = [$command];
            $command = 'move';
        }
        foreach ($this->commands as $method) {
            if ($command === $method->name) {
                try {
                    return $method->invokeArgs($this, $commandInput);
                } catch (\ArgumentCountError $e) {
                    return '引数の数が足りません. ' . $this->help($command);
                } catch (\TypeError $e) {
                    return '引数の型がちがいます. ' . $this->help($command);
                }
            }
        }
        return "invalid command: {$input}. Commands: ["  . $this->help() . "]";
    }

    public function compute() : string
    {
        $player = strtolower($this->game->getCurrentPlayer()->name);
        $strategy = $this->strategy[$player];
        $move = $this->ai->choice($this->game, $strategy['strategy'], $strategy['searchLevel']);
        if ($move === 'pass') {
            $this->game->next();
            return 'pass';
        } else {
            $this->game->move($move);
            return $move;
        }
    }

    public function state() : GameState
    {
        return $this->game->state();
    }

    /**
     * Usage:
     * - history  ヒストリーのハッシュ値をリストします。 back コマンドの引数にわたします。
     */
    public function history(): string
    {
        return implode(' ', array_values($this->game->history()));
    }

    /**
     * Usage:
     * - back [hash] ヒストリーを遡ります
     */
    public function back(string $hash)
    {
        $this->game->historyBack($hash);
    }

    /**
     * Usage:
     * - computer [on|off] コンピュータ対戦を切り替えます。
     */
    public function computer(string $onoff)
    {
        $this->opponentComputer = $onoff === 'on' ? true : false;
    }

    /**
     * Usage:
     * - auto [sleep] 自動対戦モードです。sleep の値に数値を指定すると実行待ち時間を指定できます。
     */
    public function auto(float|int $sleep = 0)
    {
        $this->opponentComputer = true;
        $this->auto = true;
        $this->sleep = $sleep;
    }

    /**
     * Usage:
     * - simple シンプル表示モードに切り替えます。
     */
    public function simple()
    {
        $this->cli->simple();
    }

    /**
     * Usage:
     * - exit 終了します。
     */
    public function exit() : string
    {
        $this->cli->exit = true;
        return 'bye!';
    }

    /**
     * Usage:
     * - resize [x] [y] 盤面をリサイズします。
     */
    public function resize(int $x, int $y)
    {
        $this->boardSizeX = $x;
        $this->boardSizeY = $y;
        $this->reset();
    }

    /**
     * Usage:
     * - strategy [strategy] [searchLevel] [player] コンピュータ選択の戦略を設定します
     */
    public function strategy(string $strategy = '', ?int $searchLevel = null, string $player = 'black') : ?string
    {
        if (!$strategy) {
            return 'white: ' . $this->strategy['white']['strategy']. ', black: ' . $this->strategy['black']['strategy'];
        }
        if (isset($this->strategy[$player])) {
            $this->strategy[$player]['strategy'] = $strategy;
            if ($searchLevel) {
                $this->strategy[$player]['searchLevel'] = $searchLevel;
            }
        }
        return null;
    }

    /**
     * Usage:
     * - wait [sleep] コンピュータが手番のときの待機時間を設定します
     */
    public function wait(float|int $sleep = 0.2)
    {
        $this->sleep = $sleep;
    }
}
