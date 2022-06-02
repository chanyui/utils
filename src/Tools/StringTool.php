<?php

namespace chanyu\Tools;

class StringTool
{
    /**
     * 下划线转驼峰
     *
     * @author yc
     * @param $str
     * @return mixed
     */
    public static function convertUnderline($str)
    {
        $str = preg_replace_callback('/([-_]+([a-z]{1}))/i', function ($matches) {
            return strtoupper($matches[2]);
        }, $str);
        return $str;
    }

    /**
     * 驼峰转下划线
     *
     * @author yc
     * @param $str
     * @return mixed
     */
    public static function humpToLine($str)
    {
        $str = preg_replace_callback('/([A-Z]{1})/', function ($matches) {
            return '_' . strtolower($matches[0]);
        }, $str);
        return $str;
    }

    /**
     * 驼峰命名转下划线命名
     * 思路: 小写和大写紧挨一起的地方,加上分隔符,然后全部转小写
     *
     * @author yc
     * @param $camelCaps
     * @param string $separator
     * @return string
     */
    public static function uncamelize($camelCaps, $separator = '_')
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
    }


    /**
     * 返回文件mime类型后缀
     *
     * @author yc
     * @param string $mineType 文件类型
     * @param string $fileName 文件名
     * @return bool|mixed
     */
    public static function searchFileMimeType($mineType, $fileName)
    {
        $mimetypelist["application/msword"] = "doc";
        $mimetypelist["application/vnd.openxmlformats-officedocument.wordprocessingml.document"] = "docx";
        $mimetypelist["application/vnd.ms-excel"] = "xls";
        $mimetypelist["application/x-excel"] = "xls";
        $mimetypelist["application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"] = "xlsx";
        $mimetypelist["application/vnd.ms-powerpoint"] = "ppt";
        $mimetypelist["application/vnd.openxmlformats-officedocument.presentationml.presentation"] = "pptx";
        $mimetypelist["application/pdf"] = "pdf";
        $mimetypelist["image/png"] = "png";
        $mimetypelist["image/jpeg"] = "jpeg";
        $mimetypelist["image/jpg"] = "jpg";

        // key 存在
        if (isset($mimetypelist[$mineType])) {
            return $mimetypelist[$mineType];
        }

        // vaule 值存在
        $arraySearchResult = array_search($mineType, $mimetypelist);
        if ($arraySearchResult) {
            return $mineType;
        }

        // 截取后缀
        $mineType = end(explode('.', $fileName));
        if (strlen($mineType) < 10) {
            return $mineType;
        }

        // 都不存在
        return false;
    }

    /**
     * emoji检查
     * @param $str
     * @return bool
     */
    public static function emojiCheck($str)
    {
        $result = true;
        $length = mb_strlen($str, 'utf-8');
        for ($i = 0; $i < $length; $i++) {
            $_tmpStr = mb_substr($str, $i, 1, 'utf-8');
            if (strlen($_tmpStr) >= 4) {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * 转换数字金额为汉字大写金额
     *
     * @author yc
     * @param $dValue
     * @param $maxDec
     * @return string
     */
    public static function amountInWords($dValue, $maxDec)
    {
        $dValue = str_replace('/,/g', '', $dValue);
        $dValue = str_replace('/^0+/', '', $dValue);

        if ($dValue == "") {
            return "零元整";
        } elseif (!is_numeric($dValue)) {
            return "错误：金额不是合法的数值！";
        }

        $minus = "";// 负数的符号“-”的大写：“负”字。可自定义字符，如“（负）”。

        $CN_SYMBOL = "";// 币种名称（如“人民币”，默认空）

        if (strlen($dValue) > 1) {
            if (strpos($dValue, '-') === 0) {
                $dValue = str_replace('-', '', $dValue);
                $minus = "负";
            }   // 处理负数符号“-”
            if (strpos($dValue, '+') === 0) {
                $dValue = str_replace('+', '', $dValue);
            }
        }

        // 变量定义：
        $vInt = "";
        $vDec = "";    // 字符串：金额的整数部分、小数部分
        $resAIW = '';    // 字符串：要输出的结果
        $parts = '';    // 数组（整数部分.小数部分），strlen=1时则仅为整数。
        $digits = $radices = $bigRadices = $decimals = '';    // 数组：数字（0~9——零~玖）；基（十进制记数系统中每个数字位的基是10——拾,佰,仟）；大基（万,亿,兆,京,垓,杼,穰,沟,涧,正）；辅币（元以下，角/分/厘/毫/丝）。
        $zeroCount = '';    // 零计数
        $i = $p = $d = '';    // 循环因子；前一位数字；当前位数字。
        $quotient = $modulus = '';    // 整数部分计算用：商数、模数。

        // 金额数值转换为字符，分割整数部分和小数部分：整数、小数分开来搞（小数部分有可能四舍五入后对整数部分有进位）。
        $NoneDecLen = ($maxDec == null || (int)$maxDec < 0 || (int)$maxDec > 5);     // 是否未指定有效小数位（true/false）
        $parts = explode('.', $dValue);// 数组赋值：（整数部分.小数部分），Array的strlen=1则仅为整数。

        if (count($parts) > 1) {
            $vInt = $parts[0];
            $vDec = $parts[1];    // 变量赋值：金额的整数部分、小数部分

            if ($NoneDecLen) {
                $maxDec = strlen($vDec) > 5 ? 5 : strlen($vDec);
            }    // 未指定有效小数位参数值时，自动取实际小数位长但不超5。
            $rDec = '0.' . $vDec;

            $rDec *= pow(10, $maxDec);

            $rDec = round(abs($rDec));

            $rDec /= pow(10, $maxDec);    // 小数四舍五入

            $aIntDec = explode('.', $rDec);

            if ((int)$aIntDec[0] == 1) {
                $vInt = (int)$vInt + 1;
            }    // 小数部分四舍五入后有可能向整数部分的个位进位（值1）

            if (count($aIntDec) > 1) {
                $vDec = $aIntDec[1];
            } else {
                $vDec = "";
            }
        } else {
            $vInt = $dValue;
            $vDec = "";
            if ($NoneDecLen) {
                $maxDec = 0;
            }
        }
        if (strlen($vInt) > 44) {
            return "错误：金额值太大了！整数位长【" . strlen($vInt) . "】超过了上限——44位/千正/10^43（注：1正=1万涧=1亿亿亿亿亿，10^40）！";
        }

        // 准备各字符数组 Prepare the characters corresponding to the digits:
        $digits = ["零", "壹", "贰", "叁", "肆", "伍", "陆", "柒", "捌", "玖"];
        $radices = ["", "拾", "佰", "仟"];    // 拾,佰,仟
        $bigRadices = ["", "万", "亿", "兆", "京", "垓", "杼", "穰", "沟", "涧", "正"];
        $decimals = ["角", "分", "厘", "毫", "丝"];

        $resAIW = ""; // 开始处理

        // 处理整数部分（如果有）
        if ((int)$vInt > 0) {
            $zeroCount = 0;
            for ($i = 0; $i < strlen($vInt); $i++) {
                $p = strlen($vInt) - $i - 1;

                $d = substr($vInt, $i, 1);

                $quotient = $p / 4;

                $modulus = $p % 4;

                if ($d == "0") {
                    $zeroCount++;
                } else {
                    if ($zeroCount > 0) {
                        $resAIW .= $digits[0];
                    }
                    $zeroCount = 0;
                    $resAIW .= $digits[(int)$d] . $radices[$modulus];
                }

                if ($modulus == 0 && $zeroCount < 4) {
                    $resAIW .= $bigRadices[$quotient];
                }
            }

            $resAIW .= "元";
        }

        // 处理小数部分（如果有）
        for ($i = 0; $i < strlen($vDec); $i++) {
            $d = substr($vDec, $i, 1);
            if ($d != "0") {
                $resAIW .= $digits[(int)$d] . $decimals[$i];
            }
        }

        // 处理结果
        if ($resAIW == "") {
            $resAIW = "零" . "元";
        }     // 零元
        if ($vDec == "") {
            $resAIW .= "整";
        }    // ...元整
        $resAIW = $CN_SYMBOL . $minus . $resAIW;    // 人民币/负......元角分/整
        return $resAIW;
    }

    /**
     * 获取字符串的字符个数
     *
     * @author yc
     * @param $string
     * @return int
     */
    public static function get_string_length($string)
    {
        $len = strlen($string);
        preg_match_all("/./us", $string, $match);
        $len = count($match[0]);
        return $len;
    }

    /**
     * 获取中文字符拼音首字母
     *
     * @author yc
     * @param $str
     * @return null|string
     */
    public static function getFirstCharter($str)
    {
        if (empty($str)) {
            return '';
        }
        $encode = mb_detect_encoding($str, array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5'));
        if ($encode != 'GB2312') {
            $str = iconv($encode, 'GB2312', trim($str));
        }
        $fchar = ord($str{0});
        if ($fchar >= ord('A') && $fchar <= ord('Z')) return strtoupper($str{0});
        $asc = ord($str{0}) * 256 + ord($str{1}) - 65536;
        if ($asc >= -20319 && $asc <= -20284) return 'A';
        if ($asc >= -20283 && $asc <= -19776) return 'B';
        if ($asc >= -19775 && $asc <= -19219) return 'C';
        if ($asc >= -19218 && $asc <= -18711) return 'D';
        if ($asc >= -18710 && $asc <= -18527) return 'E';
        if ($asc >= -18526 && $asc <= -18240) return 'F';
        if ($asc >= -18239 && $asc <= -17923) return 'G';
        if ($asc >= -17922 && $asc <= -17418) return 'H';
        if ($asc >= -17417 && $asc <= -16475) return 'J';
        if ($asc >= -16474 && $asc <= -16213) return 'K';
        if ($asc >= -16212 && $asc <= -15641) return 'L';
        if ($asc >= -15640 && $asc <= -15166) return 'M';
        if ($asc >= -15165 && $asc <= -14923) return 'N';
        if ($asc >= -14922 && $asc <= -14915) return 'O';
        if ($asc >= -14914 && $asc <= -14631) return 'P';
        if ($asc >= -14630 && $asc <= -14150) return 'Q';
        if ($asc >= -14149 && $asc <= -14091) return 'R';
        if ($asc >= -14090 && $asc <= -13319) return 'S';
        if ($asc >= -13318 && $asc <= -12839) return 'T';
        if ($asc >= -12838 && $asc <= -12557) return 'W';
        if ($asc >= -12556 && $asc <= -11848) return 'X';
        if ($asc >= -11847 && $asc <= -11056) return 'Y';
        if ($asc >= -11055 && $asc <= -10247) return 'Z';
        return null;
    }

###############################################加密、随机字符串相关#########################################################

    /**
     * 密码加密 str
     *
     * @author yc
     * @param null $password
     * @return bool|string
     */
    public static function passwordEncrypt($password = null)
    {
        if ($password == null) {
            return false;
        }

        $newPasswd = md5($password . 'common-password-salt');

        return $newPasswd;
    }

    /**
     * 获取随机码
     * @param int $length 随机码的长度
     * @param int $numeric 0是字母和数字混合码，不为0是数字码
     * @return string
     */
    public static function random_char($length, $numeric = 1)
    {
        PHP_VERSION < '4.2.0' ? mt_srand((double)microtime() * 1000000) : mt_srand();
        $seed = base_convert(md5(print_r($_SERVER, 1) . microtime()), 16, $numeric ? 10 : 35);
        $seed = $numeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'zZ' . strtoupper($seed));
        $hash = '';
        $max = strlen($seed) - 1;
        for ($i = 0; $i < $length; $i++) {
            $hash .= $seed[mt_rand(0, $max)];
        }
        return $hash;
    }

    /**
     * 唯一ID
     *
     * @author yc
     * @return string
     */
    public static function getUniqueId()
    {
        $max = rand(5, 10);
        $rand = '';
        for ($i = 0; $i < $max; $i++) {
            $rand .= rand();
        }
        return strtoupper(md5($rand . uniqid()));
    }

    /**
     * 生成随机数
     *
     * @author yc
     * @param int $num
     * @return string
     */
    public static function makeRand($num = 5)
    {
        mt_srand((double)microtime() * 1000000);//用 seed 来给随机数发生器播种。
        $strand = str_pad(mt_rand(1, 99999), $num, "0", STR_PAD_LEFT);
        return $strand;
    }

    /**
     * 随机数
     *
     * @author yc
     * @param $len
     * @return bool|string
     */
    public static function randomKeys($len)
    {
        $pattern = '1234567890123456789012345678901234567890';
        $key = substr(str_shuffle($pattern), 0, $len);
        return $key;
    }

    /**
     * @desc id加密（会溢出）
     * @author yc
     * @param $id
     * @return int
     */
    public static function encode_id($id)
    {
        $sid = ($id & 0xff000000);
        $sid += ($id & 0x0000ff00) << 8;
        $sid += ($id & 0x00ff0000) >> 8;
        $sid += ($id & 0x0000000f) << 4;
        $sid += ($id & 0x000000f0) >> 4;
        $sid ^= 2147483647;
        return $sid;
    }

    /**
     * @desc ID解密
     * @author yc
     * @param $sid
     * @return bool|int
     */
    public static function decode_id($sid)
    {
        if (!is_numeric($sid)) {
            return false;
        }
        $sid ^= 2147483647;
        $id = ($sid & 0xff000000);
        $id += ($sid & 0x00ff0000) >> 8;
        $id += ($sid & 0x0000ff00) << 8;
        $id += ($sid & 0x000000f0) >> 4;
        $id += ($sid & 0x0000000f) << 4;
        return $id;
    }

    /**
     * 生成hashCode
     * @return string
     */
    public static function hashCode($str)
    {
        if (empty($str))
            return '';
        $str = strtoupper($str);
        $mdv = md5($str);
        $mdv1 = substr($mdv, 0, 16);
        $mdv2 = substr($mdv, 16, 16);
        $crc1 = abs(crc32($mdv1));
        $crc2 = abs(crc32($mdv2));
        return bcmul($crc1, $crc2);
    }


    /**
     * 加密解密（可逆）
     *
     * @author yc
     * @param string $string 加密的字符串
     * @param string $operation DECODE表示解密,其它表示加密
     * @param string $key 密钥
     * @param int $expiry 密文有效期
     * @return bool|string
     */
    public static function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0)
    {
        $ckey_length = 4;
        $key = md5($key ? $key : "da7b4db15be94a4c597a34f9cf902b01");
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

        $cryptkey = $keya . md5($keya . $keyc);
        $key_length = strlen($cryptkey);

        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
        $string_length = strlen($string);

        $result = '';
        $box = range(0, 255);

        $rndkey = [];
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }

        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }

        if ($operation == 'DECODE') {
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            return $keyc . str_replace('=', '', base64_encode($result));
        }
    }

    /**
     * OpenSSL 加密/解密
     * @author yc
     * @param $data
     * @param string $type
     * @param string $key
     * @param string $iv
     * @return string
     */
    public static function openssl_authcode($data, $type = 'ENCODE', $key = '', $iv = '')
    {
        $key = md5($key ? $key : '73497cba2e2f17ad412dda1ab8b24a8e');
        // 非 NULL 的初始化向量。
        $iv = md5($iv ? $iv : '30aae6075fe1787b94878793820e26d2', true);

        if ($type == 'ENCODE') {
            return base64_encode(openssl_encrypt($data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv));
        } else {
            return openssl_decrypt(base64_decode($data), 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        }
    }


    /**
     * 生成密码
     *
     * @author yc
     * @param string $password 原始密码
     * @param string $salt 密钥
     * @return string
     */
    public static function get_password($password, $salt = 'COMMON')
    {
        $slt = $password . '{' . $salt . "}";
        $h = 'sha256';

        $digest = hash($h, $slt, true);

        for ($i = 1; $i < 5000; $i++) {
            $digest = hash($h, $digest . $slt, true);
        }

        return base64_encode($digest);
    }

    /**
     * 验证密码
     *
     * @author yc
     * @param string $password 原始密码
     * @param string $pwd 加密后密码
     * @param string $salt 密钥
     * @return bool
     */
    public static function check_password($password, $pwd, $salt = 'COMMON')
    {
        if (self::get_password($password, $salt) == $pwd) {
            return true;
        } else {
            return false;
        }
    }

####################################################身份证相关############################################################

    /**
     *  判断字符串是否是身份证号
     *  author:xiaochuan
     * @param string $idcard 身份证号码
     */
    public static function isIdCard($idcard)
    {
        $idcard = trim($idcard);
        $slen = strlen($idcard);
        if ($slen != 18) {
            return false;
        }
        if (empty($idcard)) return false;
        #  转化为大写，如出现x
        $idcard = strtoupper($idcard);
        #  加权因子
        $wi = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
        $ai = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];
        #  按顺序循环处理前17位
        $sigma = 0;
        #  提取前17位的其中一位，并将变量类型转为实数
        for ($i = 0; $i < 17; $i++) {
            $b = (int)$idcard{$i};
            #  提取相应的加权因子
            $w = $wi[$i];
            #  把从身份证号码中提取的一位数字和加权因子相乘，并累加
            $sigma += $b * $w;
        }
        #  计算序号
        $sidcard = $sigma % 11;
        #  按照序号从校验码串中提取相应的字符。
        $check_idcard = $ai[$sidcard];
        if ($idcard{17} == $check_idcard) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 检测身份证信息
     *
     * @author yc
     * @param $idcard
     * @return array
     */
    public static function get_check_idcard($idcard)
    {
        if (!self::isIdCard($idcard)) {
            return [false, [], '身份证号不正确！'];
        }
        $sex = self::getSexByIdCard($idcard);
        $birthday = self::get_birthday_by_idcard($idcard);
        $address = self::get_address_by_idcard($idcard);
        $data = [
            'sex'      => $sex,
            'birthday' => $birthday,
            'area'     => $address,
        ];
        return [true, $data, '成功'];
    }

    /**
     * 根据身份证号获取性别
     * @param $idcard
     * @return string
     */
    public static function getSexByIdCard($idcard)
    {
        if (($idcard[16] % 2) == 0) {
            return '女';
        } else {
            return '男';
        }
    }

    /**
     * 通过身份证判断性别
     *
     * @author yc
     * @param string $idcard 身份证号码
     * @return int (1-男，2-女)
     */
    public static function get_sex_by_idcard($idcard)
    {
        $len = strlen(trim($idcard));
        $idcard = $len == 18 ? (int)substr($idcard, 0, 17) : (int)$idcard;
        if ($idcard % 2 == 0) {
            return 2;
        } else {
            return 1;
        }
    }

    /**
     * 根据身份证号码获取生日
     * @param string $idcard 身份证号码
     * @return $birthday
     */
    public static function get_birthday_by_idcard($idcard)
    {
        if (empty($idcard)) return null;
        $bir = substr($idcard, 6, 8);
        $year = (int)substr($bir, 0, 4);
        $month = substr($bir, 4, 2);
        $day = substr($bir, 6, 2);
        return $year . '-' . $month . '-' . $day;
    }

    /**
     * 根据身份证号获取地址
     *
     * @param $idcard
     * @param int $type
     * @return string
     */
    public static function get_address_by_idcard($idcard, $type = 1)
    {
        if ($type == 1) {
            $index = substr($idcard, 0, 6);
            $addressArr = getAddrIdCard()['address'];
        } else {
            $index = substr($idcard, 0, 2);
            $addressArr = getAddrIdCard()['province'];
        }
        return isset($addressArr[$index]) ? $addressArr[$index] : '';
    }

    /**
     * 根据身份号获取年龄
     *
     * @author yc
     * @param $idcard
     * @return bool|false|string
     */
    public static function get_age_by_idcard($idcard)
    {
        $year = substr($idcard, 6, 4);
        $monthDay = substr($idcard, 10, 4);

        $age = date('Y') - $year;
        if ($monthDay > date('md')) {
            $age--;
        }
        return $age;
    }


    /**
     * 判断是否是少数民族
     * @author yc
     * @param $nation
     * @return int
     */
    public static function isMinority($nation)
    {
        if (!$nation) {
            return 0;
        }
        if ($nation == '汉' || $nation == '汉族') {
            return 0;
        } else {
            return 1;
        }
    }

###################################################时间日期相关############################################################

    /**
     * 时分秒转换成秒数
     *
     * @author yc
     * @param string $time 时间 格式H:I:S
     * @return int
     */
    public static function transform_time($time)
    {
        $timeArr = explode(':', $time);
        $h = (int)$timeArr[0];
        $i = (int)$timeArr[1];
        $s = (int)$timeArr[2];

        return $h * 3600 + $i * 60 + $s;
    }

    /**
     * 把秒数转化为时分秒形式
     *
     * @author yc
     * @param $seconds
     * @return string
     */
    public static function secondToTime($seconds)
    {
        if ($seconds > 3600) {
            $hours = intval($seconds / 3600);
            $time = $hours . ":" . gmstrftime('%M:%S', $seconds);
        } else {
            $time = gmstrftime('%H:%M:%S', $seconds);
        }
        return $time;
    }

    /**
     * 生成毫秒
     * @return float
     */
    public static function millisecond()
    {
        list($msec, $sec) = explode(' ', microtime());
        $time = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        return $time;
    }

    /**
     * 转换时间差为天、时、分
     * @author yc
     * @param $start
     * @param $end
     * @return array
     */
    public static function timeDiff($start, $end)
    {
        $start = strtotime($start);
        $end = strtotime($end);
        if ($start > $end) {
            return [];
        }

        $timediff = $end - $start;
        $days = intval($timediff / 86400);
        $remain = $timediff % 86400;
        $hours = intval($remain / 3600);
        $remain = $remain % 3600;
        $mins = intval($remain / 60);
        $secs = $remain % 60;
        $res = ["day" => $days, "hour" => $hours, "min" => $mins, "sec" => $secs];
        return $res;
    }

    /**
     * 根据秒数转换为天
     * @param $day1
     * @param $day2
     * @return float|int
     */
    public static function diffBetweenTwoDays($second1, $second2)
    {
        if ($second1 < $second2) {
            $tmp = $second2;
            $second2 = $second1;
            $second1 = $tmp;
        }
        return ceil(($second1 - $second2) / 86400);
    }

    /**
     * 根据秒数转换为时分 str
     * @param int $expire_sec 秒
     * @return string
     */
    public static function formatTimeHourM($expire_sec, $formatH = 'h', $formatM = 'm')
    {
        $hour = floor($expire_sec / 3600);
        $spendTime = '';
        if ($hour > 0) {
            $spendTime = floor($expire_sec / 3600) . $formatH;
        }
        $minute = ceil(($expire_sec % 3600) / 60);
        if ($minute > 0) {
            $spendTime .= $minute . $formatM;
        }

        return $spendTime;
    }

    /**
     * 转化时间格式 小时 分
     *
     * @author yc
     * @param string $seconds 需要处理的时间戳
     * @return string
     */
    public static function spendTimeFormat($seconds)
    {
        $h = floor($seconds / 3600);
        $m = floor(($seconds - 3600 * $h) / 60);
        $s = floor((($seconds - 3600 * $h) - 60 * $m) % 60);

        return (empty($h) ? (empty($m) ? $s . '秒' : (empty($s) ? $m . '分' : $m . '分' . $s . '秒')) : (empty($m) && empty($s) ? $h . '小时' : (empty($m) ? $h . '小时' . $s . '秒' : (empty($s) ? $h . '小时' . $m . '分' : $h . '小时' . $m . '分' . $s . '秒'))));
    }

    /**
     * 菲波那切数列递归版（没有使用静态变量，效率非常低）
     *
     * @author yc
     * @param $a
     * @return int
     */
    public static function fibonacci($a)
    {
        if ($a <= 2) {
            return $cache[1] = $cache[2] = 1;
        } else {
            return $cache[$a] = self::fibonacci($a - 1) + self::fibonacci($a - 2);
        }
    }

    /**
     * 菲波那切数列递归版（使用静态变量，效率高）
     *
     * @author yc
     * @param $a
     * @return int|mixed
     */
    public static function fibonacci_cache($a)
    {
        static $cache = array(); //静态变量
        if (isset($cache[$a])) {
            return $cache[$a];
        } else {
            if ($a <= 2) {
                return $cache[1] = $cache[2] = 1;
            } else {
                return $cache[$a] = self::fibonacci_cache($a - 1) + self::fibonacci_cache($a - 2);
            }
        }
    }

    /**
     * 获取用户IP
     *
     * @return array|false|string
     */
    public static function getClientIP()
    {
        if (getenv("HTTP_CLIENT_IP")) {
            $ip = getenv("HTTP_CLIENT_IP");
        } else if (getenv("HTTP_X_FORWARDED_FOR")) {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        } else if (getenv("REMOTE_ADDR")) {
            $ip = getenv("REMOTE_ADDR");
        } else {
            $ip = "Unknow";
        }
        return $ip;
    }

    /**
     * 日期转大写
     * @param int $date 时间戳
     * @param string $format 时间格式
     * @return array   array('二零一五','十','二十八')
     */
    public static function dateToCapital($date, $format = 'Y-n-j')
    {
        //"Y-n-j"为月和日前面不带0
        $date_format_str = date($format, $date);
        list($year, $month, $day) = explode('-', $date_format_str);
        $capital = ['零', '一', '二', '三', '四', '五', '六', '七', '八', '九', '十', '十一', '十二', '十三', '十四', '十五', '十六', '十七', '十八', '十九', '二十', '二十一', '二十二', '二十三', '二十四', '二十五', '二十六', '二十七', '二十八', '二十九', '三十', '三十一'];
        $date_array = ['0' => '', '1' => '', '2' => ''];
        foreach (str_split($year) as $k => $v) {
            $date_array[0] .= $capital[$v];     //年
        }

        $date_array[1] = $capital[$month];  //月
        $date_array[2] = $capital[$day];    //日

        return $date_array;
    }
}