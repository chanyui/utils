<?php

namespace chanyu\Tools;

class CurlTool
{
    /**
     * cURL功能（get）
     *
     * @author yc
     * @param $url string 地址
     * @param array $header
     * @param array $params 参数
     * @param string $ua
     * @param false $print
     * @return bool|string
     */
    public static function gcurl($url, $params = [], $header = [], $ua = "Mozilla/5.0 (X11; Linux x86_64; rv:2.2a1pre) Gecko/20110324 Firefox/4.2a1pre", $print = false)
    {
        // 构造url
        $url = self::buildUrl($url, $params);
        // 构造header
        $header = self::buildHeaders($header);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
        if (!empty($ua)) {
            curl_setopt($ch, CURLOPT_USERAGENT, $ua);
        }

        $output = curl_exec($ch);
        curl_close($ch);
        if ($print) {
            print($output);
        } else {
            return $output;
        }
    }

    /**
     * cURL功能（post）
     *
     * @author yc
     * @param $url string 地址
     * @param array $post 参数
     * @param array $header
     * @param null $ref
     * @param string $ua
     * @param false $print
     * @return bool|string
     */
    public static function xcurl($url, $post = [], $params = [], $header = [], $ref = null, $print = false)
    {
        // 构造url
        $url = self::buildUrl($url, $params);
        // 构造header
        $header = self::buildHeaders($header);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        if (!empty($ref)) {
            curl_setopt($ch, CURLOPT_REFERER, $ref);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if (!empty($ua)) {
            curl_setopt($ch, CURLOPT_USERAGENT, $ua);
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($post) ? http_build_query($post) : $post);
        $output = curl_exec($ch);
        curl_close($ch);
        if ($print) {
            print($output);
        } else {
            return $output;
        }
    }

    /**
     * cURL功能（post）
     *
     * @author yc
     * @param string $url 地址
     * @param array $post 参数
     * @param null $ref
     * @param string $ua
     * @param false $print
     * @return bool|string
     */
    public static function pcurl($url, $post = [], $ref = null, $ua = "Mozilla/5.0 (X11; Linux x86_64; rv:2.2a1pre) Gecko/20110324 Firefox/4.2a1pre", $print = false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        if (!empty($ref)) {
            curl_setopt($ch, CURLOPT_REFERER, $ref);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        if (!empty($ua)) {
            curl_setopt($ch, CURLOPT_USERAGENT, $ua);
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($post) ? http_build_query($post) : $post);

        $output = curl_exec($ch);
        curl_close($ch);
        if ($print) {
            print($output);
        } else {
            return $output;
        }
    }

    /**
     * 构造 header
     *
     * @param array $headers
     * @return array
     */
    private static function buildHeaders($headers)
    {
        $result = [];
        if (!empty($headers)) {
            foreach ($headers as $k => $v) {
                $result[] = sprintf('%s:%s', $k, $v);
            }
        }
        return $result;
    }

    /**
     * 构造参数
     *
     * @param string $url
     * @param array $params 参数
     * @return string
     */
    private static function buildUrl($url, $params)
    {
        if (!empty($params)) {
            $str = http_build_query($params);
            return $url . (strpos($url, '?') === false ? '?' : '&') . $str;
        } else {
            return $url;
        }
    }
}