<?php
namespace Tenjuu99\Reversi\AI;

use Tenjuu99\Reversi\Model\Game;
use Tenjuu99\Reversi\Model\Move;

class Ai
{

    private Random $random;
    private MiniMax $miniMax;
    private AlphaBeta $alphaBeta;

    public function __construct()
    {
        $this->random = new Random();
        $this->miniMax = new MiniMax();
        $this->alphaBeta = new AlphaBeta();
    }

    public function choice(Game $game, string $strategy, int $searchLevel = 2): ?string
    {
        if ($game->isGameEnd()) {
            return null;
        }
        $think = $this->think($strategy);
        if ($think instanceof GameTreeInterface) {
            $think->searchLevel($searchLevel);
        }
        return $think->choice($game);
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
