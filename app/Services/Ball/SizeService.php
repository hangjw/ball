<?php

namespace App\Services\Ball;

class SizeService
{

    private $initSize = [
        'width' => 30,
        'height' => 30,
    ];


    public function getInitSize()
    {
        return $this->initSize;
    }




}
