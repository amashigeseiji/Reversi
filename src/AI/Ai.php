<?php
namespace Tenjuu99\Reversi\AI;

use Tenjuu99\Reversi\Model\Game;
use Tenjuu99\Reversi\Model\Move;

class Ai
{
    private ThinkInterface $think;

    public function __construct(string $strategy)
    {
        switch ($strategy) {
        case 'random':
          $this->think = new Random();
          break;
        case 'minimax':
          $this->think = new MiniMax();
          break;
        default:
          $this->think = new Random();
        }
    }

    public function choice(Game $game): ?Move
    {
        return $this->think->choice($game);
    }
}
