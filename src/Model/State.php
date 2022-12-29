<?php
namespace Tenjuu99\Reversi\Model;

class State
{
    private array $whites;
    private array $blacks;

    public function put(string $cell, Player $player)
    {
        switch ($player) {
            case Player::WHITE:
                $this->whites[] = $cell;
                break;
            case Player::BLACK:
                $this->blacks[] = $cell;
                break;
        }
    }

    public function whites(): array
    {
        return $this->whites;
    }

    public function blacks(): array
    {
        return $this->blacks;
    }
}
