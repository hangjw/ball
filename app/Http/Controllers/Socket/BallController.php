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

class BallController extends Controller
{

    public function open(UserService $userService, PositionService $positionService, SizeService $sizeService, Request $request)
    {
        $id = $userService->buildId();
        $position = $positionService->getInitPosition();
        $size     = $sizeService->getInitSize();
        $user = [
            'id' => $id,
            'position' => $position,
            'size' => $size
        ];
        // 储存该用户数据并入数据总和
        $userService->saveUserInfo($request->fd, $user);
        $userService->addUser($request->fd);
    }


    // 移动
    public function move(Request $request, MoveService $moveService, UserService $userService)
    {
        $data = $request->data;
        $moveTo = [
            'x' => $data['x'],
            'y' => $data['y']
        ];
        $info = $userService->getUserInfo($request->fd);
        $moveService->moveTo($request->fd, $info, $moveTo);
    }

    // 关闭客户端
    public function close(Request $request, UserService $userService)
    {
        $fd = $request->fd;
        $closeUser = $userService->getUserInfo($request->fd);
        $userService->close($fd);
        $return = [
            'type' => 'close',
            'close_id' => $closeUser['id'],
        ];
        app('webSocket')->send($return);
    }

    // 设置用户名称
    public function setName(UserService $userService, Request $request)
    {
        $data = $request->data;
        $user = $userService->getUserInfo($request->fd);
        $user['name'] = $data['name'];
        $userService->saveUserInfo($request->fd, $user);
        $users = $userService->getAllUser();
        $return = [
            'type' => 'start',
            'user' => $users,
        ];

        app('webSocket')->send($return);
    }


    public function video(Request $request)
    {
        $data = $request->data['data'];
        $return = [
            'type' => 'video',
            'video' => $data,
        ];
        file_put_contents(__DIR__ . '/1', 1, FILE_APPEND);
        app('webSocket')->send($return);
    }


}
