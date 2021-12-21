<?php
/**
 * 公用函数
 */

use App\Constants\Business;
use EasySwoole\Utility\Str;
use Hyperf\Snowflake\IdGeneratorInterface;

/**
 * 获取数组维数
 * @param $arr
 * @param int $level
 * @return int
 */
function arrayLevel($arr, $level = 0): int
{
    //建立一个存放维度的数组,内容是0
    static $arrLevel = array(0);
    if (is_array($arr)) {
        //如果是数组，则+1
        $level++;
        //将当前维度放进数组
        array_push($arrLevel, $level);
        //循环参数数组，判断数组的元素是否是数组
        foreach ($arr as $v) {
            //递归执行本函数,php递归的最大限制是100-200之间。
            arrayLevel($v, $level);
        }
    }
    return max($arrLevel);
}

/**
 * 获取雪花ID
 * @return int
 */
function snowId(): int
{
    return container(IdGeneratorInterface::class)->generate();
}

/**
 * 生成随机数字
 */
function randomId(): int
{
    return intvalue(rand(1000, 9999) . substr(microtimes(), -6));
}

function isCrontab($value): bool
{
    if (!preg_match('/^((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)$/i', trim($value))) {
        if (!preg_match('/^((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)$/i', trim($value))) {
            return false;
        }
    }
    return true;
}

/**
 * @param $str
 * @return string|null
 */
function getFirstCharter($str): ?string
{
    if (empty($str)) {
        return '';
    }
    $char = ord($str[0]);
    if ($char >= ord('A') && $char <= ord('z')) return strtoupper($str[0]);
    $s1 = iconv('UTF-8', 'gb2312', $str);
    $s2 = iconv('gb2312', 'UTF-8', $s1);
    $s = $s2 == $str ? $s1 : $str;
    $asc = ord($s[0]) * 256 + ord($s[1]) - 65536;
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

/**
 * 字符串填补
 * @param $str 原字符串
 * @param $len 新字符串长度
 * @param $s   填补字符
 * @param string $type 前/后补(1 前补, 0 后补)
 * @return string
 */
function strfill($str, $len, $s, $type = '1')
{
    //$length = $len - strlen($str);
    $length = 0;
    if ($length < 1) return $str;
    if ($type == 1) {
        $str = str_repeat($s, $length) . $str;
    } else {
        $str .= str_repeat($s, $length);
    }
    return $str;
}

/**
 * 变量转为int, 空值(不存在/null/"")统一返回null
 */
function intvalue($param)
{
    return is_empty($param) ? null : intval($param);
}

/**
 * 转换下划线数组为驼峰式
 * @param $arr
 * @param false $ucfirst
 * @return array
 */
function ac($arr, $ucfirst = false)
{
    if (!is_array($arr)) {
        return $arr;
    }
    $temp = [];
    foreach ($arr as $key => $value) {
        $key = ucwords(str_replace('_', ' ', $key));
        $key = str_replace(' ', '', lcfirst($key));
        $key1 = $ucfirst ? ucfirst($key) : $key;
        $value1 = ac($value);
        $temp[$key1] = $value1;
    }
    return $temp;
}

/**
 * 转换驼峰数组为下划线式
 * @param $arr
 * @return array|string
 */
function au($arr)
{
    if (!is_array($arr)) {
        return $arr;
    }
    $temp = [];
    foreach ($arr as $key => $value) {
        $key1 = strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . '_' . "$2", $key));
        $value1 = au($value);
        $temp[$key1] = $value1;
    }
    return $temp;
}

function sc(string $str, bool $ucfirst = false): string
{
    if ($ucfirst) {
        return Str::studly($str);
    } else {
        return Str::camel($str);
    }
}

function su(string $str): string
{
    return (string)Str::snake($str);
}

/**
 * 数组排序
 *
 * @param $data
 * @param $field
 * @param $sort
 * @return mixed
 */
function array_sort($data, $field, $sort): mixed
{
    $sortField = array_column($data, $field);

    array_multisort($sortField, $sort == 'desc' ? SORT_DESC : SORT_ASC, $data);

    return $data;
}

//获取一年中每个月开始结束时间
function getMonthStart($year)
{
    $i = 1;
    for ($i = 1; $i < 13; $i++) {
        $time = $year . '-' . $i . '-01';
        $data[$i] = strtotime($time);
    }
    $newyear = $year + 1;
    $data[13] = strtotime($newyear . '-01-01');
    return $data;
}

//根据时间段获取所包含的年份
function getYearByTime($start_time, $end_time): array
{
    $yearArr = [];
    $monthArr = monthList($start_time, $end_time);
    foreach ($monthArr as $v) {
        $yearArr[date('Y', $v)] = date('Y', $v);
    }
    return $yearArr;
}

//根据时间段获取所包含的月份
function getMonthByTime($start_time, $end_time): array
{
    $monthList = [];
    $monthArr = monthList($start_time, $end_time);
    foreach ($monthArr as $v) {
        $monthList[date('Y', $v)][] = date('m', $v);
    }
    return $monthList;
}

/**
 * 金额展示规则,超过1万时以万为单位，低于1万时以千为单位，低于1千时以元为单位
 * @param string $money 金额
 * @return string
 * @author Michael_xu
 */
function money_view($money)
{
    $data = '0元';
    if (($money / 10000) > 1) {
        $data = is_int($money / 10000) ? ($money / 10000) . '万' : rand(($money / 10000), 2) . '万';
    } elseif (($money / 1000) > 1) {
        $data = is_int($money / 1000) ? ($money / 1000) . '千' : rand(($money / 1000), 2) . '千';
    } else {
        $data = $money . '元';
    }
    return $data;
}

/**
 * 等于（时间段）数据处理
 *
 * @param $data
 * @return array
 * @since 2021-06-11
 * @author fanqi
 */
function advancedQueryHandleDate($data)
{
    // 本年度
    if ($data['value'][0] == 'year') {
        $arrTime = DataTime::year();
        $data['value'][0] = date('Y-m-d 00:00:00', $arrTime[0]);
        $data['value'][1] = date('Y-m-d 23:59:59', $arrTime[1]);
    }

    // 上一年度
    if ($data['value'][0] == 'lastYear') {
        $data['value'][0] = date('Y-m-d 00:00:00', strtotime(date('Y-m-d') . '-1 year'));
        $data['value'][1] = date('Y-m-d 23:59:59', strtotime(date('Y-m-d') . '-1 year'));
    }

    // 下一年度
    if ($data['value'][0] == 'nextYear') {
        $data['value'][0] = date('Y-m-d 00:00:00', strtotime(date('Y-m-d') . '+1 year'));
        $data['value'][1] = date('Y-m-d 23:59:59', strtotime(date('Y-m-d') . '+1 year'));
    }

    // 上半年
    if ($data['value'][0] == 'firstHalfYear') {
        $data['value'][0] = date('Y-01-01 00:00:00');
        $data['value'][1] = date('Y-06-30 23:59:59');
    }

    // 下半年
    if ($data['value'][0] == 'nextHalfYear') {
        $data['value'][0] = date('Y-07-01 00:00:00');
        $data['value'][1] = date('Y-12-31 23:59:59');
    }

    // 本季度
    if ($data['value'][0] == 'quarter') {
        $season = ceil((date('n')) / 3);
        $data['value'][0] = date('Y-m-d H:i:s', mktime(0, 0, 0, $season * 3 - 3 + 1, 1, date('Y')));
        $data['value'][1] = date('Y-m-d H:i:s', mktime(23, 59, 59, $season * 3, date('t', mktime(0, 0, 0, $season * 3, 1, date("Y"))), date('Y')));
    }

    // 上一季度
    if ($data['value'][0] == 'lastQuarter') {
        $season = ceil((date('n')) / 3) - 1;
        $data['value'][0] = date('Y-m-d H:i:s', mktime(0, 0, 0, $season * 3 - 3 + 1, 1, date('Y')));
        $data['value'][1] = date('Y-m-d H:i:s', mktime(23, 59, 59, $season * 3, date('t', mktime(0, 0, 0, $season * 3, 1, date("Y"))), date('Y')));
    }

    // 下一季度
    if ($data['value'][0] == 'nextQuarter') {
        $season = ceil((date('n')) / 3);
        $data['value'][0] = date('Y-m-d H:i:s', mktime(0, 0, 0, $season * 3 + 1, 1, date('Y')));
        $data['value'][1] = date('Y-m-d H:i:s', mktime(23, 59, 59, $season * 3 + 3, date('t', mktime(0, 0, 0, $season * 3, 1, date("Y"))), date('Y')));
    }

    // 本月
    if ($data['value'][0] == 'month') {
        $data['value'][0] = date('Y-m-01 00:00:00');
        $data['value'][1] = date('Y-m-31 23:59:59');
    }

    // 上月
    if ($data['value'][0] == 'lastMonth') {
        $data['value'][0] = date('Y-m-01 00:00:00', strtotime(date('Y-m-d') . '-1 month'));
        $data['value'][1] = date('Y-m-31 23:59:59', strtotime(date('Y-m-d') . '-1 month'));
    }

    // 下月
    if ($data['value'][0] == 'nextMonth') {
        $data['value'][0] = date('Y-m-01 00:00:00', strtotime(date('Y-m-d') . '+1 month'));
        $data['value'][1] = date('Y-m-31 23:59:59', strtotime(date('Y-m-d') . '+1 month'));
    }

    // 本周
    if ($data['value'][0] == 'week') {
        $data['value'][0] = date('Y-m-d 00:00:00', mktime(0, 0, 0, date('m'), date('d') - date('w') + 1, date('Y')));
        $data['value'][1] = date('Y-m-d 23:59:59', mktime(23, 59, 59, date('m'), date('d') - date('w') + 7, date('Y')));
    }

    // 上周
    if ($data['value'][0] == 'lastWeek') {
        $date = date("Y-m-d");
        $w = date("w", strtotime($date));
        $d = $w ? $w - 1 : 6;
        $start = date("Y-m-d", strtotime($date . " - " . $d . " days"));
        $data['value'][0] = date('Y-m-d', strtotime($start . " - 7 days"));
        $data['value'][1] = date('Y-m-d', strtotime($start . " - 1 days"));
    }

    // 下周
    if ($data['value'][0] == 'nextWeek') {
        $date = date("Y-m-d");
        $w = date("w", strtotime($date));
        $d = $w ? $w - 1 : 6;
        $start = date("Y-m-d", strtotime($date . " - " . $d . " days"));
        $data['value'][0] = date('Y-m-d', strtotime($start . " + 7 days"));
        $data['value'][1] = date('Y-m-d', strtotime($start . " + 13 days"));
    }

    // 今天
    if ($data['value'][0] == 'today') {
        $data['value'][0] = date('Y-m-d 00:00:00');
        $data['value'][1] = date('Y-m-d 23:59:59');
    }

    // 昨天
    if ($data['value'][0] == 'yesterday') {
        $data['value'][0] = date('Y-m-d 00:00:00', strtotime(date('Y-m-d') . '-1 day'));
        $data['value'][1] = date('Y-m-d 23:59:59', strtotime(date('Y-m-d') . '-1 day'));
    }

    // 明天
    if ($data['value'][0] == 'tomorrow') {
        $data['value'][0] = date('Y-m-d 00:00:00', strtotime(date('Y-m-d') . '+1 day'));
        $data['value'][1] = date('Y-m-d 23:59:59', strtotime(date('Y-m-d') . '+1 day'));
    }

    // 过去7天
    if ($data['value'][0] == 'previous7day') {
        $data['value'][0] = date('Y-m-d 00:00:00', strtotime(date('Y-m-d') . '-7 day'));
        $data['value'][1] = date('Y-m-d 23:59:59', strtotime(date('Y-m-d') . '-1 day'));
    }

    // 过去30天
    if ($data['value'][0] == 'previous30day') {
        $data['value'][0] = date('Y-m-d 00:00:00', strtotime(date('Y-m-d') . '-30 day'));
        $data['value'][1] = date('Y-m-d 23:59:59', strtotime(date('Y-m-d') . '-1 day'));
    }

    // 未来7天
    if ($data['value'][0] == 'future7day') {
        $data['value'][0] = date('Y-m-d 00:00:00', strtotime(date('Y-m-d') . '+1 day'));
        $data['value'][1] = date('Y-m-d 23:59:59', strtotime(date('Y-m-d') . '+7 day'));
    }

    // 未来30天
    if ($data['value'][0] == 'future30day') {
        $data['value'][0] = date('Y-m-d 00:00:00', strtotime(date('Y-m-d') . '+1 day'));
        $data['value'][1] = date('Y-m-d 23:59:59', strtotime(date('Y-m-d') . '+30 day'));
    }

    return $data;
}

/**
 * 根据搜索生成where条件
 *
 * @param string $search 搜索内容
 * @param $condition 搜索条件
 * @param $k 搜索字段
 * @return array|Closure|string[]
 */
function field($search, $condition = '', $k = '')
{
    switch (trim($condition)) {
        case "is" :
            $where = ['in', $search];
            break;
        case "isNot" :
            $where = ['notin', $search];
            break;
        case "contains" :
            $containsWhere = [];
            foreach ((array)$search as $key1 => $value1) $containsWhere[] = '%' . $value1 . '%';
            $where = ['like', $containsWhere, 'OR'];
            break;
        case "notContains" :
            $containsWhere = [];
            foreach ((array)$search as $key1 => $value1) $containsWhere[] = '%' . $value1 . '%';
            $where = ['notlike', $containsWhere, 'AND'];
            break;
        case "startWith" :
            $startWithWhere = [];
            foreach ((array)$search as $key1 => $value1) $startWithWhere[] = $value1 . '%';
            $where = ['like', $startWithWhere, 'OR'];
            break;
        case "endWith" :
            $endWithWhere = [];
            foreach ((array)$search as $key1 => $value1) $endWithWhere[] = '%' . $value1;
            $where = ['like', $endWithWhere, 'OR'];
            break;
        case "isNull" :
            $where = ['eq', ''];
            break;
        case "isNotNull" :
            $where = ['neq', ''];
            break;
        case "eq" :
            $where = function ($query) use ($search, $k) {
                foreach ((array)$search as $key1 => $value1) {
                    $query->whereOr($k, $value1);
                }
            };
            break;
        case "neq" :
            $where = function ($query) use ($search, $k) {
                foreach ((array)$search as $key1 => $value1) {
                    $query->whereOr($k, '<>', $value1);
                }
            };
            break;
        case "gt" :
            $where = function ($query) use ($search, $k) {
                foreach ((array)$search as $key1 => $value1) {
                    $query->whereOr($k, '>', $value1);
                }
            };
            break;
        case "egt" :
            $where = function ($query) use ($search, $k) {
                foreach ((array)$search as $key1 => $value1) {
                    $query->whereOr($k, '>=', $value1);
                }
            };
            break;
        case "lt" :
            $where = function ($query) use ($search, $k) {
                foreach ((array)$search as $key1 => $value1) {
                    $query->whereOr($k, '<', $value1);
                }
            };
            break;
        case "elt" :
            $where = function ($query) use ($search, $k) {
                foreach ((array)$search as $key1 => $value1) {
                    $query->whereOr($k, '<=', $value1);
                }
            };
            break;
        case "in" :
            $where = ['in', $search];
            break;
        default :
            $where = ['eq', $search];
            break;
    }
    return $where;
}

/**
 * 将秒数转换为时间 (年、天、小时、分、秒）
 * @param
 */
function getTimeBySec($time)
{
    if (is_numeric($time)) {
        $value = array(
            "years" => 0, "days" => 0, "hours" => 0,
            "minutes" => 0, "seconds" => 0,
        );
        if ($time >= 31556926) {
            $value["years"] = floor($time / 31556926);
            $time = ($time % 31556926);
            $t = null;
            $t .= $value["years"] . "年";
        }
        if ($time >= 86400) {
            $value["days"] = floor($time / 86400);
            $time = ($time % 86400);
            $t .= $value["days"] . "天";
        }
        if ($time >= 3600) {
            $value["hours"] = floor($time / 3600);
            $time = ($time % 3600);
            $t .= $value["hours"] . "小时";
        }
        if ($time >= 60) {
            $value["minutes"] = floor($time / 60);
            $time = ($time % 60);
            $t .= $value["minutes"] . "分钟";
        }
        if ($time < 60) {
            $value["seconds"] = floor($time);
            $t .= $value["seconds"] . "秒";
        }
        return $t;
    } else {
        return (bool)FALSE;
    }
}

/**
 * 生成编号
 * @param prefix 前缀
 * @return
 * @author Michael_xu
 */
function prefixNumber($prefix, $number_id = 0, $str = 5)
{
    return $prefixNumber = $prefix . str_pad($number_id, $str, 0, STR_PAD_LEFT); //填充字符串的左侧（将字符串填充为新的长度）
}

/**
 * 根据IP获取地址
 */
function getAddress($ip)
{
    $res = file_get_contents("http://ip.360.cn/IPQuery/ipquery?ip=" . $ip);
    $res = json_decode($res, 1);
    if ($res && $res['errno'] == 0) {
        return explode("\t", $res['data'])[0];
    } else {
        return '';
    }
}

/**
 * 临时目录生成文件名，并返回绝对路径
 *
 * @param string $ext 文件类型后缀
 * @param string $path 临时文件目录 默认 ./public/temp/
 * @return  string  $file_path 文件名称绝对路径
 * @author ymob
 */
function tempFileName($ext = '')
{
    // 临时目录
    $path = TEMP_DIR . date('Ymd') . DS;
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }

    $ext = trim($ext, '.');
    do {
        $temp_file = md5(time() . rand(1000, 9999));
        $file_path = $path . $temp_file . '.' . $ext;
    } while (file_exists($file_path));
    return $file_path;
}

/**
 * 获取客户端系统
 */
function getOS()
{
    $agent = $_SERVER['HTTP_USER_AGENT'];
    if (preg_match('/win/i', $agent)) {
        if (preg_match('/nt 6.1/i', $agent)) {
            $OS = 'Windows 7';
        } else if (preg_match('/nt 6.2/i', $agent)) {
            $OS = 'Windows 8';
        } else if (preg_match('/nt 10.0/i', $agent)) {
            $OS = 'Windows 10';
        } else {
            $OS = 'Windows';
        }
    } elseif (preg_match('/mac/i', $agent)) {
        $OS = 'MAC';
    } elseif (preg_match('/linux/i', $agent)) {
        $OS = 'Linux';
    } elseif (preg_match('/unix/i', $agent)) {
        $OS = 'Unix';
    } elseif (preg_match('/bsd/i', $agent)) {
        $OS = 'BSD';
    } else {
        $OS = 'Other';
    }
    return $OS;
}

/*
 *根据年月计算有几天
 */
function getmonthByYM($param)
{
    $month = $param['month'] ? $param['month'] : date('m', time());
    $year = $param['year'] ? $param['year'] : date('Y', time());
    if (in_array($month, array('1', '3', '5', '7', '8', '01', '03', '05', '07', '08', '10', '12'))) {
        $days = '31';
    } elseif ($month == 2) {
        if ($year % 400 == 0 || ($year % 4 == 0 && $year % 100 !== 0)) {
            //判断是否是闰年
            $days = '29';
        } else {
            $days = '28';
        }
    } else {
        $days = '30';
    }
    return $days;
}

/**
 * 根据时间戳计算当月天数
 * @param
 */
function getmonthdays($time)
{
    $month = date('m', $time);
    $year = date('Y', $time);
    if (in_array($month, array('1', '3', '5', '7', '8', '01', '03', '05', '07', '08', '10', '12'))) {
        $days = '31';
    } elseif ($month == 2) {
        if ($year % 400 == 0 || ($year % 4 == 0 && $year % 100 !== 0)) {
            //判断是否是闰年
            $days = '29';
        } else {
            $days = '28';
        }
    } else {
        $days = '30';
    }
    return $days;
}

/**
 * 生成从开始时间到结束时间的日期数组
 * @param type，默认时间戳格式
 * @param type = 1 时，date格式
 * @param type = 2 时，获取每日开始、结束时间
 */
function dateList($start, $end, $type = 0)
{
    if (!is_numeric($start) || !is_numeric($end) || ($end <= $start)) return '';
    $i = 0;
    //从开始日期到结束日期的每日时间戳数组
    $d = array();
    if ($type == 1) {
        while ($start <= $end) {
            $d[$i] = date('Y-m-d', $start);
            $start = $start + 86400;
            $i++;
        }
    } else {
        while ($start <= $end) {
            $d[$i] = $start;
            $start = $start + 86400;
            $i++;
        }
    }
    if ($type == 2) {
        $list = array();
        foreach ($d as $k => $v) {
            $list[$k] = getDateRange($v);
        }
        return $list;
    } else {
        return $d;
    }
}

/**
 * 获取指定日期开始时间与结束时间
 */
function getDateRange($timestamp)
{
    $ret = array();
    $ret['sdate'] = strtotime(date('Y-m-d', $timestamp));
    $ret['edate'] = strtotime(date('Y-m-d', $timestamp)) + 86400;
    return $ret;
}

/**
 * 生成从开始月份到结束月份的月份数组
 * @param int $start 开始时间戳
 * @param int $end 结束时间戳
 */
function monthList($start, $end)
{
    if (!is_numeric($start) || !is_numeric($end) || ($end <= $start)) return '';
    $start = date('Y-m', $start);
    $end = date('Y-m', $end);
    //转为时间戳
    $start = strtotime($start . '-01');
    $end = strtotime($end . '-01');
    $i = 0;
    $d = array();
    while ($start <= $end) {
        //这里累加每个月的的总秒数 计算公式：上一月1号的时间戳秒数减去当前月的时间戳秒数
        $d[$i] = $start;
        $start += strtotime('+1 month', $start) - $start;
        $i++;
    }
    return $d;
}

/**
 * 转换为sql字符串
 * @param $sqlFlag
 * @param array $condition
 */
function conditionToSql($sqlFlag, array $condition)
{
    $setting = Business::CONDITION_SQL;
    if (isset($setting, $sqlFlag)) {
        return sprintf($setting[$sqlFlag][0], ...$condition);
    } else {
        return '';
    }
}

/**
 * 获取格式化的当前时间
 * @return string
 */
function times(): string
{
    return date('Y-m-d H:i:s');
}

function random($length = 6, $code = null)
{
    $code = $code ? (is_array($code) ? $code : str_split($code)) : array_merge(array_merge(range('A', 'Z'), range('a', 'z'), range(0, 9)));
    shuffle($code);
    return implode(array_slice($code, 0, $length));
}

function is_empty($value): bool
{
    if (!isset($value)) return true;
    if ($value === null) return true;
    if (is_string($value) && trim($value) === "") return true;
    return false;
}

/**
 * 获取微秒时间戳
 * @return string
 */
function microtimes(): string
{
    $time = microtime();
    return substr($time, 11, 10) . str_pad(substr($time, 0, 8) * 1000000, 6, "0", STR_PAD_LEFT);
}

/**
 * 经典函数
 */

function strcut($string, $length, $dot = ' ...')
{
    if (strlen($string) <= $length) {
        return $string;
    }

    $pre = chr(1);
    $end = chr(1);
    $string = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array($pre . '&' . $end, $pre . '"' . $end, $pre . '<' . $end, $pre . '>' . $end), $string);

    $strcut = '';
    if (strtolower(CHARSET) == 'utf-8') {

        $n = $tn = $noc = 0;
        while ($n < strlen($string)) {

            $t = ord($string[$n]);
            if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                $tn = 1;
                $n++;
                $noc++;
            } elseif (194 <= $t && $t <= 223) {
                $tn = 2;
                $n += 2;
                $noc += 2;
            } elseif (224 <= $t && $t <= 239) {
                $tn = 3;
                $n += 3;
                $noc += 2;
            } elseif (240 <= $t && $t <= 247) {
                $tn = 4;
                $n += 4;
                $noc += 2;
            } elseif (248 <= $t && $t <= 251) {
                $tn = 5;
                $n += 5;
                $noc += 2;
            } elseif ($t == 252 || $t == 253) {
                $tn = 6;
                $n += 6;
                $noc += 2;
            } else {
                $n++;
            }

            if ($noc >= $length) {
                break;
            }

        }
        if ($noc > $length) {
            $n -= $tn;
        }

        $strcut = substr($string, 0, $n);

    } else {
        $_length = $length - 1;
        for ($i = 0; $i < $length; $i++) {
            if (ord($string[$i]) <= 127) {
                $strcut .= $string[$i];
            } else if ($i < $_length) {
                $strcut .= $string[$i] . $string[++$i];
            }
        }
    }

    $strcut = str_replace(array($pre . '&' . $end, $pre . '"' . $end, $pre . '<' . $end, $pre . '>' . $end), array('&amp;', '&quot;', '&lt;', '&gt;'), $strcut);

    $pos = strrpos($strcut, chr(1));
    if ($pos !== false) {
        $strcut = substr($strcut, 0, $pos);
    }
    return $strcut . $dot;
}

function strrand($randlength = 6, $addtime = 0, $includenumber = 0)
{
    if ($includenumber) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ123456789';
    } else {
        $chars = 'abcdefghijklmnopqrstuvwxyz';
    }
    $len = strlen($chars);
    $randStr = '';
    for ($i = 0; $i < $randlength; $i++) {
        $randStr .= $chars[mt_rand(0, $len - 1)];
    }
    $tokenvalue = $randStr;
    if ($addtime) {
        $tokenvalue = $randStr . time();
    }
    return $tokenvalue;
}

function urlencodeX($url)
{
    static $fix = array('%21', '%2A', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D');
    static $replacements = array('!', '*', ';', ":", "@", "&", "=", "+", "$", ",", "/", "?", "%", "#", "[", "]");
    return str_replace($fix, $replacements, urlencode($url));
}

function addslashesX($string, $force = 1)
{
    if (is_array($string)) {
        $keys = array_keys($string);
        foreach ($keys as $key) {
            $val = $string[$key];
            unset($string[$key]);
            $string[addslashes($key)] = addslashesX($val, $force);
        }
    } else {
        $string = addslashes($string);
    }
    return $string;
}

function htmlspecialcharsX($string, $flags = null)
{
    if (is_array($string)) {
        foreach ($string as $key => $val) {
            $string[$key] = htmlspecialcharsX($val, $flags);
        }
    } else {
        if ($flags === null) {
            $string = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string);
        } else {
            if (PHP_VERSION < '5.4.0') {
                $string = htmlspecialchars($string, $flags);
            } else {
                if (strtolower(CHARSET) == 'utf-8') {
                    $charset = 'UTF-8';
                } else {
                    $charset = 'ISO-8859-1';
                }
                $string = htmlspecialchars($string, $flags, $charset);
            }
        }
    }
    return $string;
}

function strposX($string, $arr, $returnvalue = false)
{
    if (empty($string)) return false;
    foreach ((array)$arr as $v) {
        if (strpos($string, $v) !== false) {
            $return = $returnvalue ? $v : true;
            return $return;
        }
    }
    return false;
}

function strlenX($str)
{
    if (strtolower(CHARSET) != 'utf-8') {
        return strlen($str);
    }
    $count = 0;
    for ($i = 0; $i < strlen($str); $i++) {
        $value = ord($str[$i]);
        if ($value > 127) {
            $count++;
            if ($value >= 192 && $value <= 223) $i++;
            elseif ($value >= 224 && $value <= 239) $i = $i + 2;
            elseif ($value >= 240 && $value <= 247) $i = $i + 3;
        }
        $count++;
    }
    return $count;
}

function mktimeX($date)
{
    if (strpos($date, '-')) {
        $time = explode('-', $date);
        return mktime(0, 0, 0, $time[1], $time[2], $time[0]);
    }
    return 0;
}

function implodeX($array)
{
    if (!empty($array)) {
        $array = array_map('addslashes', $array);
        return "'" . implode("','", is_array($array) ? $array : array($array)) . "'";
    } else {
        return 0;
    }
}

function stripslashesX($string)
{
    if (empty($string)) return $string;
    if (is_array($string)) {
        foreach ($string as $key => $val) {
            $string[$key] = stripslashesX($val);
        }
    } else {
        $string = stripslashes($string);
    }
    return $string;
}

function mkdirX($dir, $mode = 0777, $makeindex = TRUE)
{
    if (!is_dir($dir)) {
        mkdirX(dirname($dir), $mode, $makeindex);
        @mkdir($dir, $mode);
        if (!empty($makeindex)) {
            @touch($dir . '/index.html');
            @chmod($dir . '/index.html', 0777);
        }
    }
    return true;
}

function fileSizeCount($size): string
{
    if ($size >= 1073741824) {
        $size = round($size / 1073741824 * 100) / 100 . ' GB';
    } elseif ($size >= 1048576) {
        $size = round($size / 1048576 * 100) / 100 . ' MB';
    } elseif ($size >= 1024) {
        $size = round($size / 1024 * 100) / 100 . ' KB';
    } else {
        $size = intval($size) . ' Bytes';
    }
    return $size;
}

function fileExtension($filename): string
{
    return addslashes(strtolower(substr(strrchr($filename, '.'), 1, 10)));
}

function browserVersion($type)
{
    static $return = array();
    static $types = array('ie' => 'msie', 'firefox' => '', 'chrome' => '', 'opera' => '', 'safari' => '', 'mozilla' => '', 'webkit' => '', 'maxthon' => '', 'qq' => 'qqbrowser');
    if (!$return) {
        $useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $other = 1;
        foreach ($types as $i => $v) {
            $v = $v ? $v : $i;
            if (strpos($useragent, $v) !== false) {
                preg_match('/' . $v . '(\/|\s)([\d\.]+)/i', $useragent, $matches);
                $ver = $matches[2];
                $other = $ver !== 0 && $v != 'mozilla' ? 0 : $other;
            } else {
                $ver = 0;
            }
            $return[$i] = $ver;
        }
        $return['other'] = $other;
    }
    return $return[$type];
}

function checkRobot($useragent = '')
{
    static $kw_spiders = array('bot', 'crawl', 'spider', 'slurp', 'sohu-search', 'lycos', 'robozilla');
    static $kw_browsers = array('msie', 'netscape', 'opera', 'konqueror', 'mozilla');

    $useragent = strtolower(empty($useragent) ? $_SERVER['HTTP_USER_AGENT'] : $useragent);
    if (strposX($useragent, $kw_spiders)) return true;
    if (strpos($useragent, 'http://') === false && strposX($useragent, $kw_browsers)) return false;
    return false;
}

function strexists($string, $find)
{
    return !(strpos($string, $find) === FALSE);
}

/**
 * 字符串加解密 (每次生成的密文是一样的)
 * @param $string
 * @param string $operation
 * @param string $key
 * @return false|string
 */
function hashStr($string, string $operation = 'DECODE', string $key = '')
{
    $key = md5($key != '' ? $key : 'pnz87pN3xE64KYsysGpkbP96fMjQsLhP');
    if ($operation == 'DECODE') {
        $hashcode = gzuncompress(base64_decode($string));
        $string = substr($hashcode, 0, -16);
        $hash = substr($hashcode, -16);
        unset($hashcode);
    }

    $vkey = substr(md5($string . substr($key, 0, 16)), 4, 8) . substr(md5($string . substr($key, 16, 16)), 18, 8);

    if ($operation == 'DECODE') {
        return $hash == $vkey ? $string : '';
    }

    return base64_encode(gzcompress($string . $vkey));
}

/**
 * 字符串加解密 (每次生成的密文是不相同的, 且加密级别更高)
 * @param $string
 * @param string $operation
 * @param string $key
 * @param int $expiry
 * @return false|string
 */
function hashStrHigher($string, string $operation = 'DECODE', string $key = '', int $expiry = 0)
{
    $ckey_length = 4;
    $key = md5($key != '' ? $key : 'fhkSzH5JsPlumU0PcEeOPpFMtjt2Tqal');
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

    $cryptkey = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);

    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
    $string_length = strlen($string);

    $result = '';
    $box = range(0, 255);

    $rndkey = array();
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