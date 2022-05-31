<?php

namespace yc\Tools;

class GetMacTool
{
    public static $result = [],
        $macAddrs = [],  //所有mac地址
        $macAddr; //第一个mac地址

    public function __construct($OS)
    {
        self::GetMac($OS);
    }

    public static function GetMac($OS)
    {
        switch (strtolower($OS)) {
            case "unix":
                break;
            case "solaris":
                break;
            case "aix":
                break;
            case "linux":
            case "darwin":
                self::getLinux();
                break;
            default:
                self::getWindows();
                break;
        }
        $tem = array();
        foreach (self::$result as $val) {
            if (preg_match("/[0-9a-f][0-9a-f][:-]" . "[0-9a-f][0-9a-f][:-]" . "[0-9a-f][0-9a-f][:-]" . "[0-9a-f][0-9a-f][:-]" . "[0-9a-f][0-9a-f][:-]" . "[0-9a-f][0-9a-f]/i", $val, $tem)) {
                self::$macAddr = $tem[0];//多个网卡时，会返回第一个网卡的mac地址，一般够用。
                break;
                //self::macAddrs[] = $tem[0];//返回所有的mac地址
            }
        }
        unset($tem);
        return self::$macAddr;
    }

    /**
     * Linux系统
     *
     * @return array
     */
    public static function getLinux()
    {
        @exec("ifconfig -a", self::$result);
        return self::$result;
    }

    /**
     * Windows系统
     *
     * @return array
     */
    public static function getWindows()
    {
        @exec("ipconfig /all", self::$result);
        if (self::$result) {
            return self::$result;
        } else {
            $ipconfig = $_SERVER["WINDIR"] . "\system32\ipconfig.exe";
            if (is_file($ipconfig)) {
                @exec($ipconfig . " /all", self::$result);
            } else {
                @exec($_SERVER["WINDIR"] . "\system\ipconfig.exe /all", self::$result);
                return self::$result;
            }
        }
    }
}