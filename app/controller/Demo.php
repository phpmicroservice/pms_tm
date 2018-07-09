<?php

namespace app\controller;
/**
 * 测试
 * Class Demo
 * @package app\controller
 */
class Demo extends \pms\Controller
{


    /**
     * @param $data
     */
    public function index($data)
    {
        $this->connect->send_succee([
            $data,
            "我是" . SERVICE_NAME . "分组",
            mt_rand(1, 99999), APP_HOST_IP, APP_HOST_PORT
        ]);
    }


}