<?php
namespace Tenjuu99\Reversi\AI;

use Tenjuu99\Reversi\Model\Game;
use Tenjuu99\Reversi\Model\Move;
use Tenjuu99\Reversi\Model\Player;

class Ai
{

    private Random $random;
    private MiniMax $miniMax;
    private AlphaBeta $alphaBeta;
    public int $nodeCount = 0;
    private Config $configWhite;
    private Config $configBlack;

    public function __construct()
    {
        $this->random = new Random();
        $this->miniMax = new MiniMax();
        $this->alphaBeta = new AlphaBeta();
        $defaultConfig = new Config('alphabeta', 4, 10, ['calc', 'cornerPoint', 'moveCount']);
        $this->configWhite = $defaultConfig;
        $this->configBlack = $defaultConfig;
    }

    public function choice(Game $game, string $strategy, int $searchLevel = 2): ?string
    {
        if ($game->isGameEnd) {
            return null;
        }
        $think = $this->think($strategy);
        if ($think instanceof GameTreeInterface) {
            $config = $game->getCurrentPlayer() === Player::WHITE ? $this->configWhite : $this->configBlack;
            $think->configure($config);
        }
        $choice = $think->choice($game);
        if ($think instanceof GameTreeInterface) {
            $this->nodeCount = $think->nodeCount();
        }
        return $choice;
    }

    public function configure(Config $config, Player $player)
    {
        if ($player === Player::WHITE) {
            $this->configWhite = $config;
        } else {
            $this->configBlack = $config;
        }
    }

    private function think(string $strategy) : ThinkInterface
    {
        switch($strategy) {
        case 'random':
            return $this->random;
        case 'minimax':
            return $this->miniMax;
        case 'alphabeta':
            return $this->alphaBeta;
        default:
            return $this->random;
        }
    }

    public function strategies() : array
    {
        return [
            'random',
            'minimax',
            'alphabeta',
        ];
    }
}
