<?php

namespace app\task;

use pms\Task\Task;
use pms\Task\TaskInterface;

/**
 * 依赖处理完成的任务类 1-> 2
 * @author Dongasai<1514582970@qq.com>
 *
 */
class End extends Task implements TaskInterface
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
        $sub[$server_name] = 3;
        $gCache->save($xid . '_sub', $sub);
        # 6秒没有依赖处理完成就是失败
        for ($i = 0; $i < 12; $i++) {
            usleep(mt_rand(400, 600));
            $create_status = $this->monitor($xid);
            if ($create_status === 2) {
                break;
            }
        }
        var_dump($data);
        var_dump($create_status);
        return $create_status;
    }

    private function getGCache(): \Phalcon\Cache\BackendInterface
    {
        return \Phalcon\Di::getDefault()->get('gCache');
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
        $status1 = 2;
        foreach ($sub as $name => $status) {
            if ($status === 2) {
                # 已经进入到构建阶段
            } else {
                $status1 = 1;
            }
        }
        # 已经完成就保存  事务状态信息
        if ($status_old === 2) {
            $this->gCache->save($xid . '_status', $status1);
        }
        $status_old = $gCache->get($xid . '_status');
        return (int)$status_old;
    }

    public function end()
    {

    }


}