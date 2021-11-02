<?php
global $tdb;
$tdb = Typecho_Db::get();
define('MY_TABLE', $tdb->getPrefix() . 'wjssk_myfriends');

function getThemePath()
{
    global $tdb;
    $queryTheme = $tdb->select('value')->from('table.options')->where('name = ?', 'theme');
    $rowTheme   = $tdb->fetchRow($queryTheme);
    $path       = dirname(__FILE__) . '/../../themes/' . $rowTheme['value'];
    if (!is_writable($path)) {
        Typecho_Widget::widget('Widget_Notice')->set(_t('主题目录不可写，请更改目录权限。' . __TYPECHO_THEME_DIR__ . '/' . $rowTheme['value']), 'success');
    }
    return $path;
}

function loadPage()
{
    $path = getThemePath();
    if (!file_exists($path . "/myfriend.php")) {
        $regfile = @fopen(dirname(__FILE__) . "/pages/myfriend.php", "r") or die("不能读取" . dirname(__FILE__) . "myfriend.php文件");
        $regtext = fread($regfile, filesize(dirname(__FILE__) . "/pages/myfriend.php"));
        fclose($regfile);
        $regpage = fopen($path . "/myfriend.php", "w") or die("不能写入myfriend.php文件");
        fwrite($regpage, $regtext);
        fclose($regpage);
    }
}

function uninstallPage()
{
    $path = getThemePath();
    if (file_exists($path . "/myfriend.php")) {
        unlink($path . "/myfriend.php");
    }
}

function hasLogin()
{
    return Typecho_Widget::widget('Widget_User')->hasLogin();
}

function createTable()
{
    global $tdb;

    $charset_collate = '';
    if (!empty($tdb->charset)) {
        $charset_collate = "DEFAULT CHARACTER SET {$tdb->charset}";
    }

    if (!empty($tdb->collate)) {
        $charset_collate .= " COLLATE {$tdb->collate}";
    }

    $db   = Typecho_Db::get();
    $type = explode('_', $db->getAdapterName());
    $type = array_pop($type);
    if ($type == "SQLite") {
        throw new Typecho_Plugin_Exception('目前只支持mysql数据库！');
    } else {
        $sql        = 'SHOW TABLES LIKE "' . MY_TABLE . '"';
        $checkTabel = $db->query($sql);
        $row        = $checkTabel->fetchAll();
        if ('1' == count($row)) {
            // exist
        } else {
            //创建主表
            $sql = "CREATE TABLE " . MY_TABLE . " (
            id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            status tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
            type tinyint(1) NOT NULL DEFAULT '0' COMMENT '类别',
            title varchar(64) NOT NULL COMMENT '标题',
            url varchar(64) NOT NULL COMMENT '站点',
            icon varchar(255) NOT NULL COMMENT '站点icon',
            description varchar(255) COMMENT '描述',
            email varchar(255) COMMENT '站长邮箱',
            rank tinyint(1) NOT NULL DEFAULT '1' COMMENT '星级',
            sort int(11) NOT NULL DEFAULT '1' COMMENT '排序',
            click_no int(11) NOT NULL DEFAULT '0' COMMENT '点击次数',
            visit_no int(11) NOT NULL DEFAULT '0' COMMENT '来访次数',
            last_visit_time datetime COMMENT '最后一次来访时间',
            check_friend_url tinyint(1) NOT NULL DEFAULT '0' COMMENT '检测友链地址',
            check_time datetime NOT NULL COMMENT '检测时间',
            create_time datetime NOT NULL COMMENT '添加时间'
	        ) " . $charset_collate . ";";

            $db->query($sql);
        }
    }
}

function deleteTable()
{
    $db  = Typecho_Db::get();
    $sql = "DROP TABLE if EXISTS `" . MY_TABLE . "`;";
    $db->query($sql);
}

function format_url($url)
{
    if ($url != "") {
        $url_parts = parse_url($url);
        $scheme    = $url_parts['scheme'];
        $host      = $url_parts['host'];
        $path      = $url_parts['path'] ?? '';
        $port      = !empty($url_parts['port']) ? ':' . $url_parts['port'] : '';
        $url       = (!empty($scheme) ? $scheme . '://' . $host : (!empty($host) ? 'http://' . $host : 'http://' . $path)) . $port . '/';

        return $url;
    }
}

function get_url_content($url)
{
    if (empty($url)) {
        return false;
    }
    $timeout = 30;
    $data    = '';
    for ($i = 0; $i < 5 && empty($data); $i++) {
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');

            $data      = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($http_code != '200') {
                return false;
            }
        } elseif (function_exists('fsockopen')) {
            $params = parse_url($url);
            $host   = $params['host'];
            $path   = $params['path'];
            $query  = $params['query'];
            $fp     = @fsockopen($host, 80, $errno, $errstr, $timeout);
            if (!$fp) {
                return false;
            } else {
                $result = '';
                $out    = "GET /" . $path . '?' . $query . " HTTP/1.0\r\n";
                $out    .= "Host: $host\r\n";
                $out    .= "Connection: Close\r\n\r\n";
                @fwrite($fp, $out);
                $http_200 = preg_match('/HTTP.*200/', @fgets($fp, 1024));
                if (!$http_200) {
                    return false;
                }

                while (!@feof($fp)) {
                    if ($get_info) {
                        $data .= @fread($fp, 1024);
                    } else {
                        if (@fgets($fp, 1024) == "\r\n") {
                            $get_info = true;
                        }
                    }
                }
                @fclose($fp);
            }
        } elseif (function_exists('file_get_contents')) {
            if (!get_cfg_var('allow_url_fopen')) {
                return false;
            }
            $context = stream_context_create(
                array('http' => array('timeout' => $timeout))
            );
            $data    = @file_get_contents($url, false, $context);
        } else {
            return false;
        }
    }

    if (!$data) {
        return false;
    } else {
        $encode = mb_detect_encoding($data, array('ascii', 'gb2312', 'utf-8', 'gbk'));
        if ($encode == 'EUC-CN' || $encode == 'CP936') {
            $data = @mb_convert_encoding($data, 'utf-8', 'gb2312');
        }

        return $data;
    }
}

/** 获取META信息 */
function get_sitemeta($url)
{
    $url  = format_url($url);
    $data = get_url_content($url);

    $meta = array();
    // $meta['data'] = $data;
    if (!empty($data)) {
        #Title
        preg_match('/<title>([\w\W]*?)<\/title>/si', $data, $matches);
        if (!empty($matches[1])) {
            $meta['title'] = $matches[1];
        }

        #Keywords
        preg_match('/<meta\s+name="keywords"\s+content="([\w\W]*?)"/si', $data, $matches);
        if (empty($matches[1])) {
            preg_match("/<meta\s+name='keywords'\s+content='([\w\W]*?)'/si", $data, $matches);
        }
        if (empty($matches[1])) {
            preg_match('/<meta\s+content="([\w\W]*?)"\s+name="keywords"/si', $data, $matches);
        }
        if (empty($matches[1])) {
            preg_match('/<meta\s+http-equiv="keywords"\s+content="([\w\W]*?)"/si', $data, $matches);
        }
        if (!empty($matches[1])) {
            $meta['keywords'] = $matches[1];
        }

        #Description
        preg_match('/<meta\s+name="description"\s+content="([\w\W]*?)"/si', $data, $matches);
        if (empty($matches[1])) {
            preg_match("/<meta\s+name='description'\s+content='([\w\W]*?)'/si", $data, $matches);
        }
        if (empty($matches[1])) {
            preg_match('/<meta\s+content="([\w\W]*?)"\s+name="description"/si', $data, $matches);
        }
        if (empty($matches[1])) {
            preg_match('/<meta\s+http-equiv="description"\s+content="([\w\W]*?)"/si', $data, $matches);
        }
        if (!empty($matches[1])) {
            $meta['description'] = $matches[1];
        }
    }

    return $meta;
}

function http_code($url)
{
    $ch      = curl_init();
    $timeout = 3;
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $httpCode;
}

function my_file_get_contents($url, $timeout = 10)
{
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $file_contents = curl_exec($ch);
        curl_close($ch);
    } else if (ini_get('allow_url_fopen') == 1 || strtolower(ini_get('allow_url_fopen')) == 'on') {
        $file_contents = @file_get_contents($url);
    } else {
        $file_contents = '';
    }
    return $file_contents;
}

function isExistsContentUrl($url, $mydomain = "")
{
    if (!isset($url) || empty($url)) {
        // $retMsg = "填写的URL为空";
        return false;
    }
    if (!isset($mydomain) || empty($mydomain)) {
        $mydomain = $_SERVER['SERVER_NAME'];
    }
    $resultContent = my_file_get_contents($url);
    if (trim($resultContent) == '') {
        // $retMsg = "未获得URL相关数据，请重试！";
        return false;
    }
    if (strripos($resultContent, $mydomain)) {
        // $retMsg = "检测已经通过！";
        return true;
    } else {
        // $retMsg = "请确认您已经添加本站的连接";
        return false;
    }
}
