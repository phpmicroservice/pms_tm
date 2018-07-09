<?php

namespace app\controller;

use pms\bear\Counnect;

/**
 * 服务处理
 *
 */
class Service extends \app\Controller
{

    public function demo()
    {
        $this->connect->send_succee([uniqid(), time(), date()], '成功');
    }


    /**
     * 创建全局事务
     */
    public function create()
    {
        $server = $this->getData('server');
        $tx_name = $this->getData('tx_name');
        $tx_data = $this->getData('tx_data');
        $xid = uniqid();
        $data = [
            'name' => $tx_name,
            'data' => $tx_data,
            'xid' => $xid
        ];
        $connect = $this->connect;
        $this->gCache->save($xid . '_status', 0);
        $this->gCache->save($xid . '_sub', [$server => 0]);
        $this->swoole_server->task(['create', [
            'xid' => $xid,
            'data' => $data,
            'server' => $server
        ]], -1, function ($s, $wid, $re) {
            output($re, '创建task执行结果');
        });

    }

    /**
     * 事务回滚
     */
    public function rollback()
    {
        $xid = $this->getData('xid');
        $name = $this->getData('name');
        $connect = $this->connect;
        $this->swoole_server->task(['rollback', [
            'xid' => $xid,
            'name' => $name
        ]], -1, function (\swoole_server $serv, $task_id, $data) use ($connect) {
            if ($data['re']) {
                $connect->send_succee(true);
            } else {
                $connect->send_error('事务协调器不允许事务成功!');
            }
        });
    }

    /**
     * 事务 预提交 完成
     */
    public function prepare()
    {
        $xid = $this->getData('xid');
        $name = $this->getData('name');
        $connect = $this->connect;
        $this->swoole_server->task(['prepare', [
            'xid' => $xid,
            'name' => $name
        ]], -1, function (\swoole_server $serv, $task_id, $data) use ($connect) {
            if ($data['re']) {
                $connect->send_succee(true);
            } else {
                $connect->send_error('超时');
            }
        });
    }

    /**
     * 事务准备完成
     *
     */
    public function end()
    {
        $xid = $this->getData('xid');
        $name = $this->getData('name');
        $connect = $this->connect;
        $this->swoole_server->task(['end', [
            'xid' => $xid,
            'name' => $name
        ]], -1, function (\swoole_server $serv, $task_id, $data) use ($connect) {
            if ($data['re']) {
                $connect->send_succee(true);
            } else {
                $connect->send_error('超时');
            }
        });


    }

    /**
     * 事务的提交
     */
    public function commit()
    {
        $xid = $this->getData('xid');
        $name = $this->getData('name');
        $connect = $this->connect;
        $this->swoole_server->task(['commit', [
            'xid' => $xid,
            'name' => $name
        ]], -1, function (\swoole_server $serv, $task_id, $data) use ($connect) {
            if ($data['re']) {
                $connect->send_succee(true);
            } else {
                $connect->send_error('超时');
            }
        });
    }

    /**
     * 事务的依赖增加
     */
    public function add()
    {
        $xid = $this->getData('xid');
        $name = $this->getData('name');
        $data125 = $this->getData('data');
        $connect = $this->connect;
        $this->swoole_server->task(['add', [
            'xid' => $xid,
            'name' => $name,
            'data' => $data125
        ]], -1, function (\swoole_server $server, $task_id, $data) use ($connect) {
            if ($data['re']) {
                $connect->send_succee(true);
            } else {
                $connect->send_error('超时!');
            }
        });

        $this->call_yl($xid, $data125);
    }

    /**
     * 处理依赖
     * @param $xid
     * @param $data
     */
    private function call_yl($xid, $data)
    {
        var_dump(['call_yl', $xid, $data]);
        foreach ($data as $value) {
            $data = [
                'name' => $value['tx_name'],
                'data' => $value['tx_data'],
                'xid' => $xid
            ];
            $this->swoole_server->task(['create', [
                'xid' => $xid,
                'data' => $data,
                'server' => $value['server']
            ]], -1, function ($s, $wid, $re) {
                output($re, '创建task执行结果');
            });
        }
    }

    /**
     * 依赖处理完毕
     */
    public function dependency()
    {
        $xid = $this->getData('xid');
        $name = $this->getData('name');
        $connect = $this->connect;
        $this->swoole_server->task(['dependency', [
            'xid' => $xid,
            'name' => $name
        ]], -1, function (\swoole_server $serv, $task_id, $data) use ($connect) {
            if ($data['re']) {
                $connect->send_succee(true);
            } else {
                $connect->send_error('超时!');
            }
        });

    }

}