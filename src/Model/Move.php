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
        $this->cell = $cell;
        $this->index = $cell->index;
        $this->player = $player;
        $this->flipCells = $this->flipCells();
    }

    public function execute()
    {
        $this->cell->put($this->player->toCellState());
        $orientations = [
            'right', 'left', 'upper', 'lower',
            'upperRight', 'upperLeft', 'lowerRight', 'lowerLeft',
        ];
        foreach ($this->flipCells as $flipCell) {
            $flipCell->flip();
        }
    }

    private function flipCells(): array
    {
        $orientations = [
            'right', 'left', 'upper', 'lower',
            'upperRight', 'upperLeft', 'lowerRight', 'lowerLeft',
        ];
        $flips = [];
        foreach ($orientations as $orientation) {
            $chain = $this->cell->chain($orientation);
            if (count($chain) <= 1) {
                continue;
            }
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
