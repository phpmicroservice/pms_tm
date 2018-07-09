<?php
# 引导文件,初始化文件
namespace app;

use Phalcon\Events\Event;
use pms\Dispatcher;

/**
 *
 * Class guidance
 * @property \app\table\server $server_table
 * @package app
 */
class Guidance extends \Phalcon\Di\Injectable
{
    /**
     * 构造函数
     * guidance constructor.
     */
    public function __construct()
    {

    }

    /**
     * 开始之前
     * @param Event $event
     * @param \pms\Server $pms_server
     * @param \Swoole\Server $server
     */
    public function beforeStart(Event $event, \pms\Server $pms_server, \Swoole\Server $server)
    {
        output('beforeStart  beforeStart', 'beforeStart');
        # 写入依赖注入
    }

    /**
     * 启动事件
     * @param Event $event
     * @param \pms\Server $pms_server
     * @param \Swoole\Server $server
     */
    public function onWorkerStart(Event $event, \pms\Server $pms_server, \Swoole\Server $server)
    {
        output($server->taskworker, 'guidance');
        # 绑定一个权限验证
        $this->eventsManager->attach('Router:handleCall', $this);
        # 绑定一个准备判断和准备成功
        $this->eventsManager->attach('Server:readyJudge', $this);
        $this->eventsManager->attach('Server:readySucceed', $this);
        $this->eventsManager->attach('dispatch:beforeDispatch', $this);
        $this->eventsManager->attach('dispatch:beforeDispatch', new Alc(), 1);
    }

    /**
     * 调度之前
     * @param $propertyName
     */
    public function beforeDispatch($Event, Dispatcher $dispatch)
    {
        if (!$this->dConfig->ready) {
            # 服务还没准备好
            $dispatch->connect->send_error(500, 500);
            return false;
        }

    }


    /**
     * 准备判断
     */
    public function readyJudge(Event $event, \pms\Server $pms_server, $timeid)
    {
        $this->dConfig->ready = true;
        output('初始化完成', 'init');
    }

    /**
     * 准备完成
     */
    public function readySucceed()
    {
        output("readySucceed ");
    }

    /**
     * 路由事件
     * @param Event $event
     * @param \pms\Router $router
     * @param $data
     */
    public function handleCall(Event $event, \pms\Router $router, $data)
    {

        $new_key = md5(md5(APP_SECRET_KEY) . md5($data['name']));
        $old_key = $data['k'];
        if (\hash_equals($new_key, $old_key)) {
            return true;
        }
        output([$data, APP_SECRET_KEY, $new_key, '没有权限'], 'handleCall403');
        $router->connect->send_error('没有权限', [], 403);
        return false;
    }


}