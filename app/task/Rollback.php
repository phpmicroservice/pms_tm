<?php

namespace app\task;

use pms\Task\Task;
use pms\Task\TaskInterface;

/**
 * 依赖处理完成的任务类 3 -> 4
 * @author Dongasai<1514582970@qq.com>
 *
 */
class Rollback extends TaskBase implements TaskInterface
{
    public function run()
    {
        $logger=$this->getLogger();
        $data = $this->trueData['data']??$this->trueData[1];
        $server_name = strtolower($data['server_name']);
        $xid = $data['xid'];


        if (empty($xid)) {
            return true;
        }
        if ($data['ems']) {
            $this->add_message($xid, $server_name, $data['ems']['t'], $data['ems']['m'], 500);
        }
        $gCache = $this->getGCache();
        $sub = $gCache->get($xid . '_sub');
        $sub[$server_name] = -1;
        $gCache->save($xid . '_sub', $sub);
        $logger->info(microtime(true) . ' task-prepare-return' . var_export([$server_name, $gCache->get($xid . '_sub')], true));
        return false;
    }


    public function end()
    {

    }

}