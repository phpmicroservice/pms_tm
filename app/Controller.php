<?php

namespace app;


/**
 * 主控制器
 * Class Controller
 * @property \Phalcon\Cache\BackendInterface $gCache
 * @property \Phalcon\Config $dConfig
 * @property \pms\bear\ClientSync $proxyCS
 *
 * @package app\controller
 */
class Controller extends \pms\Controller
{

    /**
     * 获取数据
     * @param $pa
     */
    public function getData($name = '', $defind = null)
    {
        $d = $this->connect->getData();
        if ($name) {
            return $d[$name] ?? $defind;
        }
        return $d;
    }

}