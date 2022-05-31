<?php

namespace yc\Tools;

class GenerateTool
{
    /**
     * 生成16位订单号（3万 0.4秒）
     * 做了判断不会出现重复数据，可能会被猜出结构
     *
     * @author yc
     * @return string
     */
    public static function makeOrderSn()
    {
        static $orderSn = [];                                        //静态变量
        list($usec, $sec) = explode(' ', microtime());      //返回当前 Unix 时间戳和微秒数
        //$ors = date('ymd') . substr($sec, -5) . substr($usec, 2, 5);     //生成16位数字基本号
        $ors = date('ymd') . substr($sec, -3) . substr($usec, 2, 5);     //生成14位数字基本号
        if (isset($orderSn[$ors])) {                                    //判断是否有基本订单号
            $orderSn[$ors]++;                                           //如果存在,将值自增1
        } else {
            $orderSn[$ors] = mt_rand(1, 9);
        }
        return $ors . str_pad($orderSn[$ors], 2, '0', STR_PAD_LEFT);     //链接字符串
    }

    /**
     * 生成13位订单号（3万 0.5秒）
     * 做了判断不会出现重复数据，可能会被猜出结构
     *
     * @author yc
     * @return string
     */
    public static function buildOrderSn()
    {
        static $orderSn = [];
        $ors = date('ymd') . substr(microtime(), 2, 5);    //生成11位数字基本号
        if (isset($orderSn[$ors])) {                                          //判断是否有基本订单号
            $orderSn[$ors]++;                                                 //如果存在,将值自增1
        } else {
            $orderSn[$ors] = 1;
        }
        return $ors . str_pad($orderSn[$ors], 2, '0', STR_PAD_LEFT);   //链接字符串
    }

    /**
     * 生成的订单号（不使用，仅供参考）
     * 缺点：太长 22位数字（上万就会出现重复）
     * 示例：2020112512251748848795
     *
     * @author yc
     * @return string
     */
    public static function makeOrderNo()
    {
        return date('Ymd') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(1000, 9999));
    }

    /**
     * 生成订单号（不使用，仅供参考）
     * 缺点：存在字母，不易解读（3万以内不太出现重复率）
     * 长度为18位，示例：EB2598114270051204
     *
     * @author yc
     * @return string
     */
    public static function makeOrderSnChar()
    {
        list($usec, $sec) = explode(" ", microtime());
        $mt = substr($usec, 2, 6);
        $randNum = mt_rand(100, 999);
        $orderSn = chr(date('Y') - 1951) . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . $mt . sprintf('%03d', $randNum);
        return $orderSn;
    }

    /**
     * 工单生成规则
     *
     * @author yc
     * @param $userId
     * @param $key
     * @return string
     */
    public static function makeTicketNo($userId, $key)
    {
        $userId = str_pad($userId, 5, '0', STR_PAD_LEFT);
        $key = str_pad($key, 4, '0', STR_PAD_LEFT);
        $userIdStr = (string)$userId;
        return dechex(date('m')) . $userIdStr[0] . date('s') . $userIdStr[1] . date('i') . $userIdStr[2] . (string)date('y')[1] . $userIdStr[3] . date('d') . $userIdStr[4] . date('H') . $key;
    }

    /**
     * 巡检点位生成规则
     *
     * @author yc
     * @param $key
     * @return string
     */
    public static function makePTNo($key)
    {
        list($msec, $sec) = explode(' ', microtime());
        $msec = (string)((int)($msec * 10000));
        $msec = str_pad($msec, 4, '0', STR_PAD_LEFT);
        $key = str_pad($key, 2, '0', STR_PAD_LEFT);
        return dechex(date('m')) . $msec[0] . date('s') . $msec[1] . date('i') . $msec[2] . (string)date('y')[1] . $msec[3] . date('d') . date('H') . $key;
    }

    /**
     * 生成提现单号
     *
     * @author yc
     * @param $userId
     * @return string
     */
    public static function generateCashOutNo($userId)
    {
        return self::makeCashOutNo($userId);
    }

    /**
     * 生成规则
     *
     * @author yc
     * @param $userId
     * @return string
     */
    public static function makeCashOutNo($userId)
    {
        $userId = str_pad($userId, 5, '0', STR_PAD_LEFT);
        $key = StringTool::randomKeys(4);
        $userIdStr = (string)$userId;
        return date('m') . $userIdStr[0] . date('s') . $userIdStr[1] . date('i') . $userIdStr[2] . (string)date('y')[1] . $userIdStr[3] . date('d') . $userIdStr[4] . date('H') . $key;
    }


    /**
     * 生成订单号，带字母
     *
     * @author yc
     * @param int $lenth
     * @return string
     */
    public static function makeOrderOne($lenth = 13)
    {
        // 获取当前时间 格式为：201105
        $date = date('ymd');
        // 生成指定个数的随机字节
        $bytes = openssl_random_pseudo_bytes(ceil($lenth / 2));
        // 将字符串/字符接转换为十六进制
        $bytes = bin2hex($bytes);
        // 随机生成1到9999的随机数，如果不足5位数则从左边开始填充0
        $str_pad = str_pad(rand(1, 9999), 5, 0, STR_PAD_LEFT);
        return "{$date}{$bytes}{$str_pad}";
    }
}