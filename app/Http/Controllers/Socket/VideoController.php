<?php

namespace App\Http\Controllers\Socket;

use App\Http\Controllers\Controller;
use App\Services\Ball\PositionService;
use App\Services\Ball\SizeService;
use App\Services\Ball\UserService;
use App\Services\Ball\MoveService;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class VideoController extends Controller
{
    const VIDEO = 'video';
    const VIDEO_QUEUE = 'video_queue';

    public function __construct()
    {
        $open = Redis::get(self::VIDEO);
        if (empty($open)) {
            swoole_timer_tick(30, function($id){
                $open = Redis::set(self::VIDEO, 1);
                if (empty($open)) {
                    swoole_timer_clear($id);
                }
                $data = Redis::rPop(self::VIDEO_QUEUE);
                $return = [
                    'type' => 'video',
                    'video' => $data,
                ];
                app('webSocket')->send($return);
            });
        }
    }

    public function index(Request $request)
    {
        $data = $request->data['data'];
        Redis::lPush(self::VIDEO_QUEUE, $data);
    }


}
