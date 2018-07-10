<?php

namespace app\task;

use pms\Task\Task;
use pms\Task\TaskInterface;

/**
 * 依赖处理完成的任务类 3-> 4
 * @author Dongasai<1514582970@qq.com>
 *
 */
class End extends TaskBase implements TaskInterface
{
    public function run()
    {
        $logger = $this->getLogger();
        $logger->info('task-end-start' . var_export($this->trueData, true));

        $data = $this->trueData['data']??$this->trueData[1];
        $xid = $data['xid'];
        if (empty($xid)) {
            return true;
        }
        $server_name = $data['server_name'];
        $gCache = $this->getGCache();
        $sub = $gCache->get($xid . '_sub');
        $sub[$server_name] = 4;
        $gCache->save($xid . '_sub', $sub);
        # 4 秒没有依赖处理完成就是失败
        for ($i = 0; $i < 20; $i++) {
            $create_status = $this->monitor($xid);
            if ($create_status === 4) {
                break;
            }
            usleep(200000);;
        }
        $logger->info('task-end-return' . var_export($create_status === 4, true));
        return $create_status === 4;
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
            if ($status >= 4) {
                # 已经进入到构建阶段
            } else {
                $status1 = 3;
            }
        }
        # 已经完成就保存  事务状态信息
        if ($status1 === 4) {
            $gCache->save($xid . '_status', $status1);
        }
        $status_old = $gCache->get($xid . '_status');
        return (int)$status_old;
    }


    public function end()
    {

    }


}