<?php

namespace app\task;

use pms\Task\Task;
use pms\Task\TaskInterface;

/**
 * 依赖处理完成的任务类 3 -> 4
 * @author Dongasai<1514582970@qq.com>
 *
 */
class Rollback extends Task implements TaskInterface
{
    public function run()
    {
        $data = $this->trueData['data']??$this->trueData[1];
        $xid = $data['xid'];
        if (empty($xid)) {
            return true;
        }
        $server_name = $data['name'];
        $gCache = $this->getGCache();
        $sub = $gCache->get($xid . '_sub');
        $sub[$server_name] = -1;
        $gCache->save($xid . '_sub', $sub);
        return false;
    }

    private function getGCache(): \Phalcon\Cache\BackendInterface
    {
        return \Phalcon\Di::getDefault()->get('gCache');
    }

    public function end()
    {

    }

    /**
     * 监测是否创建成功!
     */
    private function monitor($xid): int
    {

        $gCache = $this->getGCache();
        # 读取旧装填
        $status_old = $gCache->get($xid . '_status');
        $sub = $gCache->get($xid . '_sub');
        # 判断其他的 服务依赖是否完成
        $status1 = 4;
        foreach ($sub as $name => $status) {
            if ($status === 4) {
                # 已经进入到构建阶段
            } else {
                $status1 = 3;
            }
        }
        # 已经完成就保存  事务状态信息
        if ($status_old === 4) {
            $gCache->save($xid . '_status', $status1);
        }
        $status_old = $gCache->get($xid . '_status');
        return (int)$status_old;
    }


}