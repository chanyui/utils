<?php

namespace chanyu\Tools;

class ArrayTool
{
    /**
     * 获取数组中某个键名的所有的键值
     *
     * @param array $array 数组
     * @param string $row 键名
     * @return array
     */
    public static function get_rows_by_array($array, $row)
    {
        if (empty($array)) {
            return [];
        }
        $result = array();
        foreach ($array as $k => $value) {
            $result[] = $value[$row];
        }
        return $result;
    }

    /**
     * 提取数组中部分数组
     *
     * @param array $array 数组
     * @param $fieldArray
     * @param false $remove
     * @return array
     */
    public static function get_sub_by_array($array, $fieldArray, $remove = false)
    {
        $result = array();
        foreach ($fieldArray as $k => $val) {
            $result[$val] = isset($array[$val]) ? $array[$val] : '';
        }
        if ($remove) {
            $result = array_diff($array, $result);
        }
        return $result;
    }

    /**
     * 以数组中某个键名作为数组的键名得到新的数组
     *
     * @param array $array 需要操作的数组
     * @param string $row 键名
     * @param bool $repeat 是否获取的是多个数组
     * @param string $except 是否去除数组中的某个字段
     * @return array
     */
    public static function get_array_by_rows($array, $row, $repeat = false, $except = '')
    {
        $result = array();
        if (empty($array)) {
            return $result;
        }
        foreach ($array as $k => $value) {
            $tmpValue = $value;
            if ($except) {
                unset($tmpValue[$except]);
            }
            if ($repeat === false) {
                $result[$value[$row]] = $tmpValue;
            } else {
                $result[$value[$row]][] = $tmpValue;
            }
        }
        return $result;
    }

    /**
     * 拼接数组的某个字段的的值
     *
     * @param $array
     * @param string $field
     * @param string $way
     * @return string
     */
    public static function get_field_string($array, $field = 'id', $way = ',')
    {
        foreach ($array as $k => $v) {
            $return[] = $v[$field];
        }
        $fieldStr = implode($way, $return);
        return $fieldStr;
    }

    /**
     * 以数组中的两个键值分别作为数组的键名，键值得到新的数组
     *
     * @param array $array 数据
     * @param string $key1 第一个键
     * @param string $key2 第二个键
     * @return array
     */
    public static function get_array_by_key($array, $key1, $key2)
    {
        $result = array();
        foreach ($array as $k => $value) {
            $result[$value[$key1]] = $value[$key2];
        }
        return $result;
    }

    /**
     * 根据规则数组筛选出数据数组中对应数据
     *
     * @param array $array 数据数组
     * @param array $match 规则数组
     * @return array|null
     */
    public static function get_sub_array($array = [], $match = [])
    {
        if (empty($match) || empty($array)) {
            return null;
        }

        $data = [];
        foreach ($match as $matchkey => $matchValue) {
            if (is_numeric($matchkey)) {
                foreach ($array as $arrayKey => $arrayValue) {
                    if (is_array($arrayValue) && is_numeric($arrayKey)) {
                        $data[$arrayKey][StringTool::convertUnderline($matchValue)] = !(is_array($arrayValue[$matchValue]) && empty($arrayValue[$matchValue])) ? $arrayValue[$matchValue] : null;
                    } else {
                        $data[StringTool::convertUnderline($matchValue)] = isset($array[$matchValue]) && !(is_array($array[$matchValue]) && empty($array[$matchValue])) ? $array[$matchValue] : null;
                    }
                }
            } elseif (is_string($matchkey)) {
                $data[StringTool::convertUnderline($matchkey)] = self::get_sub_array($array[$matchkey], $matchValue);
            }
        }

        return $data;
    }

    /**
     * 统一处理后台返回的数据
     *
     * @param $dealArr
     * @param false $output
     * @return array|mixed
     */
    public static function extractData($dealArr, $output = false)
    {
        $data = [];
        foreach ($dealArr as $key => $value) {
            if ($value['code'] != '200') {
                if ($output == true) {
                    return $value;
                }
                $$key = null;
            } else {
                $$key = isset($value['data']) ? $value['data'] : [];
            }
            $data[$key] = $$key;
        }

        return $data;
    }

    /**
     * 转化树结构为列表结构
     *
     * @param array $tree 树结构数组
     * @param $field
     * @return array
     */
    public static function changeTreeToListByPid($tree, $field)
    {
        if (empty($tree)) {
            return [];
        }
        $list = [];
        foreach ($tree as $key => $value) {
            $childList = [];
            if (isset($value[$field])) {
                $childList = self::changeTreeToListByPid($value[$field], $field);
                unset($value[$field]);
            }
            $list[] = $value;
            $list = array_merge($list, $childList);
        }
        return $list;
    }

    /**
     * 转化列表结构为树结构
     *
     * @param $list
     * @param string $idAlias
     * @param string $pidAlias
     * @return array
     */
    public static function changeListToTree($list, $idAlias = 'id', $pidAlias = 'pid', $child = 'child')
    {
        if (empty($list)) {
            return [];
        }
        $list = array_combine(array_column($list, $idAlias), $list);
        $pids = array_column($list, $pidAlias);
        $pList = array_intersect_key($list, array_flip($pids));
        if (empty($pList)) {
            return $list;
        }
        $remain = array_diff_key($list, $pList);
        foreach ($remain as $key => $value) {
            if (isset($pList[$value[$pidAlias]])) {
                $pList[$value[$pidAlias]][$child][] = $value;
            } else {
                $pList[$value[$idAlias]] = $value;
            }
        }
        $list = self::changeListToTree($pList, $idAlias, $pidAlias);
        return $list;
    }

    /**
     * 获取数组中字段
     *
     * @param $arr
     * @param $fieldArr
     * @param string $separator
     * @return array
     */
    public static function arrGetField($arr, $fieldArr, $separator = '.')
    {
        $data = [];
        foreach ($fieldArr as $key => $field) {
            if (is_array($field)) {
                $tmp = self::arrGetField($arr, $field);
            } else {
                $fieldMap = strpos($field, '=>') != false ? explode('=>', $field) : [];
                if (!empty($fieldMap)) {
                    $key = $fieldMap[0];
                    $field = $fieldMap[1];
                }

                if (strpos($field, $separator) >= 0) {
                    $fieldsDeal = explode($separator, $field);
                    $tmp = $arr;
                    foreach ($fieldsDeal as $v) {
                        $tmp = isset($tmp[$v]) ? $tmp[$v] : null;
                    }
                } else {
                    $tmp = $arr[$field];
                }
            }
            $keyNew = is_numeric($key) ? $field : $key;
            $data[StringTool::convertUnderline($keyNew)] = $tmp;
        }
        return $data;
    }

    /**
     * 组装数据
     *
     * @param $arr
     * @param $child
     * @param string $field
     * @return array
     */
    public static function array_push_child($arr, $child, $field = 'child')
    {
        if (!is_array($arr)) {
            return array();
        }
        if (!empty($arr[$field])) {
            $arr[$field] = self::array_push_child($arr[$field], $child, $field);
        } else {
            $arr[$field] = $child;
        }
        return $arr;
    }

    /**
     * 作用：array转xml
     */
    public static function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";

            } else
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
        }
        $xml .= "</xml>";
        return $xml;
    }

    /**
     * 作用：将xml转为array
     */
    public static function xmlToArray($xml)
    {
        //将XML转为array
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $array_data;
    }

    /**
     * 根据数组获取父类
     * @param $arr
     * @param int $id
     */
    public static function getFatherArr($arr, $id = 0)
    {
        $father = [];
        if (isset($arr[$id]) && $arr[$id]['pid'] != '0') {
            $father = self::getFatherArr($arr, $arr[$id]['pid']);
        }
        $idArr = isset($father['idArr']) ? $father['idArr'] : [];
        $nameArr = isset($father['nameArr']) ? $father['nameArr'] : [];
        isset($arr[$id]['id']) && $idArr[] = $arr[$id]['id'];
        isset($arr[$id]['name']) && $nameArr[] = $arr[$id]['name'];
        return $info = array('idArr' => $idArr, 'nameArr' => $nameArr);
    }


    /**
     * 把二维数组转化为树状结构
     *
     * @param $items
     * @param string $pk 主键字段
     * @param string $pid 父键字段
     * @param string $child
     * @return array
     */
    public static function turn_list_to_tree($array, $pk = 'id', $pid = 'pid', $child = 'children')
    {
        $tree = [];
        if (is_array($array)) {
            $array = self::get_array_by_rows($array, $pk);
            foreach ($array as $value) {
                if (isset($array[$value[$pid]])) {
                    $array[$value[$pid]][$child][] = &$array[$value[$pk]];
                } else {
                    $tree[] = &$array[$value[$pk]];
                }
            }
        }
        return $tree;
    }

    /**
     * 二维数组根据某个字段排序
     *
     * @param array $multi_array 排序数组
     * @param string $sort_key 排序字段
     * @param int $sort 排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
     * @return array|false
     */
    public static function multi_array_sort($multi_array, $sort_key, $sort = SORT_ASC)
    {
        if (is_array($multi_array)) {
            foreach ($multi_array as $row_array) {
                if (is_array($row_array)) {
                    $key_array[] = $row_array[$sort_key];
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
        array_multisort($key_array, $sort, $multi_array);
        return $multi_array;
    }

    /**
     * 根据日期获取日期数组
     *
     * @author yc
     * @param $startDate
     * @param $endDate
     * @param $format
     * @return array
     */
    public static function get_date_by_between($startDate, $endDate, $format = 'Y-m-d')
    {
        $format_start = strtotime($startDate);
        $format_end = strtotime($endDate);
        if ($format_start > $format_end) {
            $tmp = $format_start;
            $format_start = $format_end;
            $format_end = $tmp;
        }

        // 计算日期段内有多少天
        $days = (($format_end - $format_start) / 86400) + 1;

        // 保存每天日期
        $date = [];
        for ($i = 0; $i < $days; $i++) {
            $date[] = date($format, $format_start + (86400 * $i));
        }

        return $date;
    }


    /**
     * 根据日期获取日期数组
     *
     * @author yc
     * @param $begin_date
     * @param $end_date
     * @return mixed
     */
    public static function get_date_arr($begin_date, $end_date)
    {
        $day_list = [];
        $day_key = ceil(self::diff_between_two_days($begin_date, $end_date));  //计算跨度天数
        for ($i = 1; $i <= $day_key; $i++) {
            //判断是否是第一天
            $day_info = array("start_time" => '0:00', "end_time" => '23:59');
            $key = $i - 1;
            $week_i = $key;
            if ($i == 1) {
                $data_time = strtotime($begin_date);
                $day_info['start_time'] = date("H:i", strtotime($begin_date));
            } else {
                $data_time = strtotime("$begin_date +$week_i day");
            }
            //判断是否是最后一天 是否是第一天
            if ($i == $day_key) {
                $day_info['end_time'] = date("H:i", strtotime($end_date));
            }
            $day_info['day_time'] = date("Y-m-d", $data_time);
            $day_info['week_time'] = $data_time;
            $day_info['week'] = date("w", $data_time);
            $day_list[] = $day_info;
        }
        return $day_list;
    }

    /**
     * 计算两个日期间隔的天数
     *
     * @author yc
     * @param $day1
     * @param $day2
     * @return float|int
     */
    public static function diff_between_two_days($day1, $day2)
    {
        $second1 = strtotime($day1);
        $second2 = strtotime($day2);
        if ($second1 > $second2) {
            $tmp = $second1;
            $second1 = $second2;
            $second2 = $tmp;
        }
        return (($second2 - $second1) / 86400) + 1;
    }


    /**
     * 快速排序法
     *
     * @author yc
     * @param $arr
     * @return array
     */
    public static function quick_sort($arr)
    {
        $count = count($arr);
        if ($count <= 1) return $arr;
        $key = $arr[0];
        $left = $right = [];
        for ($i = 0; $i < $count; $i++) {
            if ($arr[$i] < $key) {
                $left[] = $arr[$i];
            } else {
                $right[] = $arr[$i];
            }
        }
        if (count($left) > 1) {
            $left = self::quick_sort($left);
        }
        if (count($right)) {
            $right = self::quick_sort($right);
        }
        return array_merge($left, [$key], $right);
    }

    /**
     * 冒泡排序法
     *
     * @author yc
     * @param $arr
     * @return bool
     */
    public static function bubble_sort($arr)
    {
        $len = count($arr);
        if ($len <= 0) {
            return false;
        }
        for ($i = 0; $i < $len; $i++) {
            for ($j = 0; $j < $len - 1 - $i; $j++) {
                if ($arr[$j] < $arr[$j + 1]) {
                    $tmp = $arr[$j];
                    $arr[$j] = $arr[$j + 1];
                    $arr[$j + 1] = $tmp;
                }
            }
        }
        return $arr;
    }

    /**
     * 根据视频总时长计算核验的时间点
     * $duration（秒） 总时长
     * $memory_time（秒） 上次观看的时长
     */
    public static function calAlertTimeByDuration($duration, $memory_time)
    {
        // 小于15分钟不提示核验 || 已经观看完结
        if ($duration < 900 || $memory_time >= $duration) {
            return [];
        }
        // 第一次核验为12~15分钟之间
        $min = 720;
        $max = 900;
        if ($memory_time && ($memory_time > $min && $memory_time < $max)) {
            $min = $memory_time;
        }
        $first_time = mt_rand($min, $max);

        // 1个课时为45分钟，2700秒，10~15分钟弹一次，大于50小于90分钟两次
        // 需要核验的次数
        $total = ceil($duration / 2700);

        $retult[] = $first_time;

        for ($i = 0; $i < $total; $i++) {
            $lin_1 = 3000 + ($i * 2400);
            $lin_2 = $lin_1 + 2400;
            if ($duration > $lin_1 && $duration <= $lin_2) {
                $retult[] = ($duration - $duration * 0.2) + mt_rand(300, 420);
            } elseif ($duration > $lin_2) {
                $retult[] = ($lin_1 - $lin_1 * 0.15) + mt_rand(300, 420);
            }
        }
        return $retult;
    }
}