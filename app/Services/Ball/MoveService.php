<?php

namespace App\Services\Ball;

use Illuminate\Support\Facades\Cache;

class MoveService
{

    const SPEED = 10;

    public function getSpeed($fd)
    {
        return self::SPEED;
    }

    public function moveTo($fd, $info, $position)
    {
        $speed = $this->getSpeed($fd);
        swoole_timer_tick(10, function($id, $param){
            $fd = $param['fd'];
            $moveTo = $param['moveTo'];
            $speed = $param['speed'];
            $data = $param['info'];
            $position = $data['position'];
            $moveId = $data['id'];
            $total = sqrt(pow(($moveTo['x'] - $position['x']), 2) + pow(($moveTo['y'] - $position['y']), 2));
            $return = [
                'type' => 'move',
                'moveId' => $moveId
            ];
            $ceil = $total/$speed;
            if ($ceil) {
                $position = $return['moveTo'] = [
                    'x' => $moveTo['x'],
                    'y' => $moveTo['y'],
                ];
                swoole_timer_clear($id);
            } else {
                $deviation = [
                    'x' => ($speed/$total) * ($moveTo['x'] - $position['x']),
                    'y' => ($speed/$total) * ($moveTo['y'] - $position['y']),
                ];
                $position = $return['moveTo'] = [
                    'x' => $deviation['x'] * ($moveTo['x'] > $position['x'] ? 1 : -1) + $position['x'],
                    'y' => $deviation['y'] * ($moveTo['y'] > $position['y'] ? 1 : -1) + $position['y'],
                ];
            }
            $data['position'] = $position;
            Cache::forever($fd, $data);
            app('webSocket')->send($return);
        }, ['fd' => $fd, 'moveTo' => $position, 'speed' => $speed, 'info' => $info]);
    }
}
