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
        $orientations = [
            'right', 'left', 'upper', 'lower',
            'upperRight', 'upperLeft', 'lowerRight', 'lowerLeft',
        ];
        $flips = [];
        foreach ($orientations as $orientation) {
            $chain = $this->cell->chain($orientation);
            // チェーンの長さが1の場合は壁のためスキップ
            if (count($chain) <= 1) {
                continue;
            }
            // 隣が敵陣ではない場合はスキップ
            if ($chain[0]->state !== $this->player->enemy()->toCellState()) {
                continue;
            }
            $tmpFlips = [];
            foreach ($chain as $cell) {
                if ($cell->state === CellState::EMPTY) {
                    $tmpFlips = [];
                    break;
                } elseif ($cell->state === $this->player->enemy()->toCellState()) { // 敵陣
                    $tmpFlips[] = $cell;
                } else { // 自陣
                    $flips = array_merge($flips, $tmpFlips);
                    $tmpFlips = [];
                    break;
                }
            }
        }
        return $flips;
    }
}
