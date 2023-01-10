<?php
namespace Tenjuu99\Reversi\AI;

interface GameTreeInterface
{
    /**
     * @param int $searchLevel ゲーム木を探索する深さ
     */
    public function searchLevel(int $searchLevel): void;
    /**
     * @return int ノードを展開した数
     */
    public function nodeCount(): int;
}
