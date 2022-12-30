<?php
namespace Tenjuu99\Reversi\Model;

class Move
{
    public readonly Cell $cell;
    public readonly string $index;
    public readonly Player $player;
    public readonly array $flipCells;

    public function __construct(Cell $cell, Player $player)
    {
        if ($cell->state !== CellState::EMPTY) {
            throw new \Exception('Cell ' . $cell->index . ' is not empty.');
        }
        $this->cell = $cell;
        $this->index = $cell->index;
        $this->player = $player;
        $this->flipCells = $this->collectFlipCells();
    }

    public function execute()
    {
        $this->cell->put($this->player->toCellState());
        foreach ($this->flipCells as $flipCell) {
            $flipCell->flip();
        }
    }

    private function collectFlipCells(): array
    {
        $flips = [];
        $flippable = $this->cell->flippableChains();
        foreach ($flippable as $orientation => $chain) {
            // 隣が敵陣ではない場合はスキップ
            if ($chain[0]->state !== $this->player->enemy()->toCellState()) {
                continue;
            }
            foreach ($chain as $cell) {
                $flips[] = $cell;
            }
        }
        return $flips;
    }
}
