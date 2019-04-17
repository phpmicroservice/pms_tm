<?php

namespace app;

use pms\Dispatcher;


/**
 * Class Alc
 * @package app
 */
class Alc extends Base
{
    public $user_id;
    public $publicTask = [
        'demo'
    ];

    /**
     *
     * beforeDispatch 在调度之前
     * @param \Phalcon\Events\Event $Event
     * @param \Phalcon\Mvc\Dispatcher $Dispatcher
     * @return
     */
    public function beforeDispatch(\Phalcon\Events\Event $Event, \pms\Dispatcher $dispatcher)
    {

        if (in_array($dispatcher->getTaskName(), $this->publicTask)) {
            # 公共的权限
            return true;
        }
        # 进行服务间鉴权
        return $this->server_auth($dispatcher);

    }

    /**
     * 服务间的鉴权
     * @return bool
     */
    private function server_auth(Dispatcher $dispatcher)
    {

        return true;
        $key = $dispatcher->connect->accessKey ?? '';
        \pms\output([APP_SECRET_KEY, $dispatcher->connect->getData(), $dispatcher->connect->f], 'verify_access');
        if (!\pms\verify_access($key, APP_SECRET_KEY, $dispatcher->connect->getData(), $dispatcher->connect->f)) {
            $dispatcher->connect->send_error('accessKey-error', [], 412);
            return false;
        }
        return true;
    }

}