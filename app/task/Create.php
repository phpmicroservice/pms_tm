<?php

namespace app\task;

use pms\bear\ClientSync;
use pms\Task\Task;
use pms\Task\TaskInterface;

/**
 * 检查创建完成的任务类 0 -> 1
 * @author Dongasai<1514582970@qq.com>
 * @data 2018年7月9日09:35:17
 */
class Create extends Task implements TaskInterface
{

    public function run()
    {
        $data = $this->trueData['data']??$this->trueData[1];
        $xid = $data['xid'];
        $server = $data['server'];
        $trdata = $data['data'];
        $proxyCS = $this->getProxyCS();
        $re = $proxyCS->request_return($server, '/transaction/create', $trdata);
        var_dump($re);
        $gCache = $this->getGCache();
        $sub = $gCache->get($xid . '_sub');
        $sub[$server] = 1;
        $gCache->save($xid . '_sub', $sub);

        return $this->a();
    }

    private function getProxyCS(): ClientSync
    {
        return \Phalcon\Di::getDefault()->get('proxyCS');

    }

    private function getGCache(): \Phalcon\Cache\BackendInterface
    {
        return \Phalcon\Di::getDefault()->get('gCache');
    }

    public function a()
    {
        $data = $this->trueData['data']??$this->trueData[1];
        $xid = $data['xid'];
        $server_name = $data['name'];
        if (empty($xid)) {
            return false;
        }


        # 5秒其他的依赖没有处理完成就是失败
        for ($i = 0; $i < 10; $i++) {
            sleep(0.5);
            $create_status = $this->monitor($xid);
            if ($create_status === 1) {
                break;
            }
        }

        output([$create_status], 'create-task-a');
        return $create_status;
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
        $status1 = 1;
        foreach ($sub as $name => $status) {
            if ($status !== 1) {
                $status1 = 0;
            }
        }
        # 已经完成就保存  事务状态信息
        if ($status_old !== $status1) {
            $gCache->save($xid . '_status', $status1);
        }
        $status_old = $gCache->get($xid . '_status');
        return (int)$status_old;
    }

    public function end()
    {

    }


}