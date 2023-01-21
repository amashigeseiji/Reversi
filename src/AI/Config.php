<?php
namespace Tenjuu99\Reversi\AI;

class Config
{
    public string $strategy;
    public int $searchLevel;
    public int $endgameThreshold;
    public array $scoringMethods;

    public function __construct(
        string $strategy = 'alphabeta',
        int $searchLevel = 4,
        int $endgameThreshold = 13,
        array $scoringMethods = ['calc', 'cornerPoint', 'moveCount']
    ) {
        $this->strategy = $strategy;
        $this->searchLevel = $searchLevel;
        $this->endgameThreshold = $endgameThreshold;
        $this->scoringMethods = $scoringMethods;
    }
}
