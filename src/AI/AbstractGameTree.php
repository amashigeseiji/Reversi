<?php
namespace Tenjuu99\Reversi\AI;

use Traversable;
use Tenjuu99\Reversi\Model\Game;
class AbstractGameTree implements GameTreeInterface
{
    protected int $nodeCount = 0;
    protected int $searchLevel = 4;
    protected int $endgameThreshold = 0;
    protected array $score = ['calc'];

    public function searchLevel(int $searchLevel) : void
    {
        if ($searchLevel > 0) {
            $this->searchLevel = $searchLevel;
        }
    }

    public function nodeCount(): int
    {
        return $this->nodeCount;
    }

    public function configure(Config $config) : void
    {
        $this->searchLevel = $config->searchLevel;
        $this->endgameThreshold = $config->endgameThreshold;
        $this->score = $config->scoringMethods;
    }
}
