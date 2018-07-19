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
        $data = $this->trueData['data']??$this->trueData[1];
        $server_name = strtolower($data['name']);
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
        return false;
    }


    public function end()
    {

    }

}