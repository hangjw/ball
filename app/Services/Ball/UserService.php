<?php

namespace App\Services\Ball;

use Illuminate\Support\Facades\Cache;

class UserService
{

    const ALL = 'all';


    public function __construct()
    {
        $this->all = md5(self::ALL);
    }


    // 获取所有用户句柄
    public function getAllUser()
    {
        $all = Cache::get($this->all);
        $users = [];
        foreach ($all as $fd) {
            $user = Cache::get($fd);
            $user && $users[] = $user;
        }
        return $users;
    }

    /** 添加用户 */
    public function addUser($fd)
    {
        $all = Cache::get($this->all) ?: [];
        // 加入总数据
        $all[] = $fd;
        Cache::forever($this->all, $all);
    }

    /** 储存用户信息 */
    public function saveUserInfo($fd, $info)
    {
        $info['last_time'] = time();
        Cache::forever($fd, $info);
    }

    /** 获取用户信息 */
    public function getUserInfo($fd)
    {
        return Cache::get($fd);
    }

    /** 生成用户唯一id */
    public function buildId()
    {
        return uniqid();
    }

    /** 退出 */

    public function close($fd)
    {
        Cache::forget($fd);
    }

}
