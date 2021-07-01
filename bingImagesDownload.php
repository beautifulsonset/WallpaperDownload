<?php
/*
 * @Description: 必应壁纸一键下载
 * @version: 1.0
 * @Author: ltt
 * @Date: 2021-06-28 12:47:23
 * @LastEditors: ltt
 * @LastEditTime: 2021-07-01 11:11:13
 */

define("URL", "https://bing.ioliu.cn?p=%s");
define("MAX_PAGE", 166);
/**
 * curl获取数据
 * @param $url
 * @return mixed
 */
function get_url($url)
{
    $ifpost = 0;
    $datafields = '';
    $cookiefile = '';
    $v = false;
    //构造随机ip
    $ip_long = array(
        array('607649792', '608174079'), //36.56.0.0-36.63.255.255
        array('1038614528', '1039007743'), //61.232.0.0-61.237.255.255
        array('1783627776', '1784676351'), //106.80.0.0-106.95.255.255
        array('2035023872', '2035154943'), //121.76.0.0-121.77.255.255
        array('2078801920', '2079064063'), //123.232.0.0-123.235.255.255
        array('-1950089216', '-1948778497'), //139.196.0.0-139.215.255.255
        array('-1425539072', '-1425014785'), //171.8.0.0-171.15.255.255
        array('-1236271104', '-1235419137'), //182.80.0.0-182.92.255.255
        array('-770113536', '-768606209'), //210.25.0.0-210.47.255.255
        array('-569376768', '-564133889'), //222.16.0.0-222.95.255.255
    );
    $rand_key = mt_rand(0, 9);
    $ip = long2ip(mt_rand($ip_long[$rand_key][0], $ip_long[$rand_key][1]));
    //模拟http请求header头
    $header = array("Connection: Keep-Alive", "Accept: text/html, application/xhtml+xml, */*", "Pragma: no-cache", "Accept-Language: zh-Hans-CN,zh-Hans;q=0.8,en-US;q=0.5,en;q=0.3", "User-Agent: Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; WOW64; Trident/6.0)", 'CLIENT-IP:' . $ip, 'X-FORWARDED-FOR:' . $ip);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_REFERER, $url);
    curl_setopt($ch, CURLOPT_HEADER, $v);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    $ifpost && curl_setopt($ch, CURLOPT_POST, $ifpost);
    $ifpost && curl_setopt($ch, CURLOPT_POSTFIELDS, $datafields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $cookiefile && curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiefile);
    $cookiefile && curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiefile);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60); //允许执行的最长秒数
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $ok = curl_exec($ch);
    curl_close($ch);
    unset($ch);
    return $ok;
}

function getResource($page = 0)
{
    $url = sprintf(URL, $page);
    return get_url($url);
}

function isUrl($url)
{
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

function downloadFile($fileUrl)
{
    $fileName = basename($fileUrl);
    $savePath = __DIR__ . "\\download\\" . $fileName;
    $content = file_get_contents($fileUrl);
    $ret = file_put_contents($savePath, $content);
    return $ret ? $savePath : "";
}

function parseResource($resource, $page = 0)
{
    $count = 0;
    for ($i = 0; $i >= 0 && $i < strlen($resource); $i++) {
        $startPos = strpos($resource, 'pic=', $i) + 4;
        if ($startPos === false) {
            break;
        }
        $endPos = strpos($resource, ".jpg", $startPos) + 4;
        if ($endPos === false) {
            break;
        }
        $url = substr($resource, $startPos, $endPos - $startPos);
        if (!isUrl($url)) {
            break;
        }
        $ret = downloadFile($url);
        $count++;
        echo "downloaded: " . $url . "save path: " . $ret . PHP_EOL;
        $i = $endPos;
    }
    return $count;
}

function main()
{
    $totalPage = MAX_PAGE;
    $total = 0;
    for ($page = 0; $page < $totalPage; $page++) {
        echo "dowanload page/total: " . $page . '/' . $totalPage . ',completed: ' . $total . PHP_EOL;
        $resource = getResource($page);
        $count = parseResource($resource, $page);
        $total += $count;
    }
    echo "下载完成，请打开当前目录下的 dowanload 文件夹" . PHP_EOL;
}

main();
