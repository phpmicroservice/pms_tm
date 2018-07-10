<?php

namespace app\task;

use pms\Task\Task;

class TaskBase extends Task
{

    protected function getGCache(): \Phalcon\Cache\BackendInterface
    {
        return \Phalcon\Di::getDefault()->get('gCache');
    }

    protected function getLogger(): \Phalcon\Logger\AdapterInterface
    {
        $data = $this->trueData['data']??$this->trueData[1];
        $xid = $data['xid'];
        defined('LOG_DIR') || define('LOG_DIR', RUNTIME_DIR . 'log/');
        if (!is_dir(LOG_DIR . date('Ymd'))) {
            mkdir(LOG_DIR . date('Ymd'));
        }
        $logger = new \Phalcon\Logger\Adapter\File(LOG_DIR . date('Ymd/') . $xid . '.log');
        return $logger;
    }
}