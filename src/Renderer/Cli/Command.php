<?php
namespace Tenjuu99\Reversi\Renderer\Cli;

use ReflectionClass;
use ReflectionMethod;
use Tenjuu99\Reversi\Error\InvalidMoveException;
use Tenjuu99\Reversi\Model\Board;
use Tenjuu99\Reversi\Model\GameState;
use Tenjuu99\Reversi\Model\Player;
use Tenjuu99\Reversi\Renderer\Cli;
use Tenjuu99\Reversi\Reversi;

class Command
{
    private Player $userPlayer;
    /** @var ReflectionMethod[] */
    private $commands = [];

    public int $boardSizeX;
    public int $boardSizeY;

    public bool $opponentComputer = true;
    public bool $auto = false;
    public float|int $sleep = 0;

    private Cli $cli;
    private Reversi $reversi;

    public function __construct(Cli $cli, Player $player, int $boardSizeX = 8, int $boardSizeY = 8)
    {
        $this->reversi = new Reversi($boardSizeX, $boardSizeY);
        $this->reversi->setStrategy('random', Player::WHITE);
        $this->reversi->setStrategy('random', Player::BLACK);
        $this->cli = $cli;
        $this->userPlayer = $player;
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
    }

    /**
     * Usage:
     * - move [index] 手を実行します。 "1-1" のように move コマンドを省略しても問題ありません。
     */
    public function move(string $index)
    {
        try {
            $this->reversi->move($index);
        } catch (InvalidMoveException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Usage:
     * - pass 相手の手番になります
     */
    public function pass()
    {
        $this->reversi->pass();
    }

    /**
     * Usage:
     * - reset 盤面を初期化します
     */
    public function reset()
    {
        $this->auto = false;
        $this->reversi->newGame($this->boardSizeX, $this->boardSizeY);
        $this->reversi->clearHistory();
    }

    public function moves() : string
    {
        return '[' . implode(' ', $this->reversi->getMoves()->indices()) . ']';
    }

    public function board(): Board
    {
        return $this->reversi->getBoard();
    }

    public function isMyTurn() : bool
    {
        if ($this->auto) {
            return false;
        }
        return $this->userPlayer === $this->reversi->getCurrentPlayer();
    }

    public function currentPlayer(): string
    {
        return $this->reversi->getCurrentPlayer()->name;
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
        [$move, $flip] = $this->reversi->compute();
        return $move;
    }

    public function state() : GameState
    {
        return $this->reversi->gameState();
    }

    /**
     * Usage:
     * - history  ヒストリーのハッシュ値をリストします。 back コマンドの引数にわたします。
     */
    public function history(): string
    {
        $keys = $this->reversi->getHistoriesHashList();
        return implode(' ', $keys);
    }

    /**
     * Usage:
     * - back [hash] ヒストリーを遡ります
     */
    public function back(string $hash)
    {
        $this->reversi->historyBack($hash);
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
    public function strategy(string $strategy = '', ?int $searchLevel = null, int $endgameThreshold = 0, ?string $player = 'WHITE') : ?string
    {
        // todo なんかおかしい
        $strategies = $this->reversi->getStrategy();
        if (!$strategy) {
            return 'white: ' . $strategies[Player::WHITE->name]['strategy']. ', black: ' . $strategies[Player::BLACK->name]['strategy'];
        }
        if (isset($strategies[strtoupper($player)])) {
            $player = strtoupper($player) === Player::WHITE->name ? Player::WHITE : Player::BLACK;
            $this->reversi->setStrategy($strategy, $player, $searchLevel, $endgameThreshold);
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
