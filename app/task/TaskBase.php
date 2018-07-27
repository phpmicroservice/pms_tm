<?php

namespace app\task;

use pms\Task\Task;

class TaskBase extends Task
{


    protected function getLogger(): \Phalcon\Logger\AdapterInterface
    {
        $data = $this->trueData['data']??$this->trueData[1];
        $xid = $data['xid'];
        defined('LOG_DIR') || define('LOG_DIR', RUNTIME_DIR . 'log/');
        if (!is_dir(LOG_DIR . date('Ymd/H/i/'))) {
            mkdir(LOG_DIR . date('Ymd/H/i/'), 777, true);
        }
        $logger = new \Phalcon\Logger\Adapter\File(LOG_DIR . date('Ymd/H/i/') . $xid . '.log');
        return $logger;
    }

    /**
     * 监测是否创建成功!
     */
    protected function monitor2($xid, $newstatus1 = 1): int
    {

        $gCache = $this->getGCache();
        # 读取旧装填
        $status_old = $gCache->get($xid . '_status');
        $sub = $gCache->get($xid . '_sub');
        # 判断其他的 服务依赖是否完成
        $status1 = $newstatus1;
        foreach ($sub as $name => $status) {
            if ($status === -1) {
                $status1 = -1;
                break;
            }
            if ($status >= $newstatus1) {
                # 已经进入下一个
            } else {
                $status1 = $newstatus1 - 1;
            }

        }
        # 已经完成就保存  事务状态信息
        if ($status1 === $newstatus1) {
            $gCache->save($xid . '_status', $status1);
        }
        # 事务回滚
        if ($status1 === -1) {
            $gCache->save($xid . '_status', $status1);
        }
        $status_old = $gCache->get($xid . '_status');
        return (int)$status_old;
    }

    protected function getGCache(): \Phalcon\Cache\BackendInterface
    {
        return \Phalcon\Di::getDefault()->get('gCache');
    }

    protected function add_message($xid, $server, $type, $mes, $code)
    {
        $gCache = $this->getGCache();
        $messages = $gCache->get($xid . '-message');
        if (!is_array($messages)) {
            $messages = [];
        }
        $messages[] = [
            'type' => $type,
            'message' => $mes,
            'code' => $code,
            'server' => $server
        ];
        $gCache->save($xid . '-message', $messages);

    }

}