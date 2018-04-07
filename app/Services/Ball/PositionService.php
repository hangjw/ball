<?php

namespace App\Services\Ball;

class PositionService
{

    private $config = [
        'x' => ['min' => 0, 'max' => 1000],
        'y' => ['min' => 0, 'max' => 600],
    ];


    public function getInitPosition()
    {
        return $this->getRandPosition();
    }


    private function getRandPosition()
    {
        return [
            'x' => mt_rand($this->config['x']['min'], $this->config['x']['max']),
            'y' => mt_rand($this->config['y']['min'], $this->config['y']['max'])
        ];
    }
}
