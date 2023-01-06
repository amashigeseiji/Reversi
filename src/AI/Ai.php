<?php
namespace Tenjuu99\Reversi\AI;

use Tenjuu99\Reversi\Model\Game;
use Tenjuu99\Reversi\Model\Move;

class Ai
{

    private Random $random;
    private MiniMax $miniMax;

    public function __construct()
    {
        $this->random = new Random();
        $this->miniMax = new MiniMax();
    }

    public function choice(Game $game, string $strategy, int $searchLevel = 2): ?Move
    {
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
        default:
            return $this->random;
        }
    }
}
