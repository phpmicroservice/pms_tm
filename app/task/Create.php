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
class Create extends TaskBase implements TaskInterface
{

    public function run()
    {
        $gCache = $this->getGCache();
        $logger = $this->getLogger();
        $logger->info(microtime(true) . ' task-create-start' . var_export($this->trueData, true));
        $data = $this->trueData['data'] ?? $this->trueData[1];
        $xid = $data['xid'];
        $server = strtolower($data['server']);
        $trdata = $data['data'];

        # 创建
        $proxyCS = $this->getProxyCS();
        $re = $proxyCS->request_return($server, '/transaction/create', $trdata);
        var_dump($re);
        $logger->info(' task-create-request_return' . var_export([$trdata, $re], true));
        if ($re['e']) {
            # 失败
            $status = -1;
            $this->add_message($xid, $server, 'create', $re['m'], $re['e']);

        } else {
            # 成功
            $status = 1;
        }

        $sub = $gCache->get($xid . '_sub');
        $sub[$server] = $status;
        $gCache->save($xid . '_sub', $sub);

        return $this->a();
    }

    private function getProxyCS(): ClientSync
    {
        return \Phalcon\Di::getDefault()->get('proxyCS');

    }


    public function a()
    {
        $logger = $this->getLogger();

        $data = $this->trueData['data']??$this->trueData[1];
        $xid = $data['xid'];
        $server_name = $data['name'];
        if (empty($xid)) {
            return false;
        }

        # 4秒其他的依赖没有处理完成就是失败
        for ($i = 0; $i < 10; $i++) {
            $create_status = $this->monitor2($xid, 1);
            if ($create_status === 1 || $create_status === -1) {
                break;
            }

            usleep(100000 * $i);
        }
        $gCache = $this->getGCache();
        $sub = $gCache->get($xid . '_sub');
        $logger->info(' task-create-sub' . var_export($sub, true));
        $logger->info(microtime(true) . ' task-create-return' . var_export($create_status === 1, true));
        return $create_status === 1;
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
        $status1 = 1;
        foreach ($sub as $name => $status) {
            if ($status >= 1) {

            } else {
                $status1 = 0;
            }
        }
        # 已经完成就保存  事务状态信息
        if ($status1 === 1) {
            $gCache->save($xid . '_status', $status1);
        }
        $status_old = $gCache->get($xid . '_status');
        return (int)$status_old;
    }


}