<?php

namespace yc\Snowflake;

class SnowflakeNew
{
    const TWEPOCH = 12888349746579;
    /**
     * 机器标识位数
     */
    const WORKER_ID_BITS = 5;
    /**
     * 数据中心标识位数
     */
    const DATA_CENTER_ID_BITS = 5;
    /**
     * 毫秒内自增位数
     */
    const SEQUENCE_BITS = 12;
    /**
     * 机器ID偏左移12位
     */
    const WORKER_ID_SHIFT = self::SEQUENCE_BITS;
    /**
     * 数据中心ID左移17位
     */
    const DATA_CENTER_ID_SHIFT = self::SEQUENCE_BITS + self::WORKER_ID_BITS;
    /**
     * 时间毫秒左移22位
     */
    const TIMESTAMP_LEFT_SHIFT = self::SEQUENCE_BITS + self::WORKER_ID_BITS + self::DATA_CENTER_ID_BITS;
    /**
     * sequence掩码，确保sequnce不会超出上限
     */
    const SEQUENCE_MASK = ~(-1 << self::SEQUENCE_BITS);
    /**
     * 上次时间戳
     */
    protected $lastTimestamp = -1;
    /**
     * 序列
     */
    protected $sequence = 0;
    /**
     * 服务器ID
     */
    protected $workerId = 1;
    /**
     * 进程编码
     */
    protected $processId = 1;

    /**
     * @param integer $dataCenter_id 数据中心的唯一ID(如果使用多个数据中心,需要设置此ID用以区分)
     * @param integer $machine_id 机器的唯一ID (如果使用多台机器,需要设置此ID用以区分)
     * @throws \Exception
     */
    public function __construct()
    {
        // 获取机器编码
        $this->workerId = StringTool::hashCode((new GetMacTool(PHP_OS))->macAddr);
        // 获取进程编码
        $this->processId = getmypid();
        // 避免编码超出最大值
        $workerMask = ~(-1 << self::WORKER_ID_BITS);
        $this->workerId = (int)$this->workerId & (int)$workerMask;
        $processMask = ~(-1 << self::DATA_CENTER_ID_BITS);
        $this->processId = $this->processId & $processMask;
    }

    /**
     * 使用雪花算法生成一个唯一ID
     *
     * @return string 生成的ID
     * @throws \Exception
     */
    public function generateID()
    {
        $timestamp = $this->getUnixTimestamp();
        if ($timestamp < $this->lastTimestamp) {
            throw new \Exception('时间倒退了!');
        }

        // 如果时间戳与上次时间戳相同
        if ($this->lastTimestamp == $timestamp) {
            // 当前毫秒内，则+1，与sequenceMask确保sequence不会超出上限
            $this->sequence = ($this->sequence + 1) & self::SEQUENCE_MASK;
            if ($this->sequence == 0) {
                // 当前毫秒内计数满了，则等待下一秒
                $timestamp = $this->tilNextMillis($this->lastTimestamp);
            }
        } else {
            $this->sequence = 0;
        }
        $this->lastTimestamp = $timestamp;

        return (($timestamp - self::TWEPOCH) << self::TIMESTAMP_LEFT_SHIFT) | ($this->processId << self::DATA_CENTER_ID_SHIFT) | ($this->workerId << self::WORKER_ID_SHIFT) | $this->sequence;
    }

    private function tilNextMillis($lastTimestamp)
    {
        $timestamp = $this->getUnixTimestamp();
        while ($timestamp <= $lastTimestamp) {
            $timestamp = $this->getUnixTimestamp();
        }

        return $timestamp;
    }

    /**
     * 获取当前时间戳
     *
     * @return integer 毫秒级别的时间戳
     */
    private function getUnixTimestamp()
    {
        return ceil(microtime(true) * 1000);
    }
}
