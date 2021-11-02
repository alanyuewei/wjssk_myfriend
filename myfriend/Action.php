<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

include "common.php";

class myfriend_Action extends Typecho_Widget implements Widget_Interface_Do
{
    /** @var  数据操作对象 */
    private $_db;
    /** @var  插件配置信息 */
    private $_cfg;
    /** @var  系统配置信息 */
    private $_options;
    /**
     * @var string
     */
    private $_dir;

    public function __construct($request, $response, $params = NULL)
    {
        parent::__construct($request, $response, $params);
        $this->_db      = Typecho_Db::get();
        $this->_options = $this->widget('Widget_Options');
        $this->_cfg     = Helper::options()->plugin('myfriend');
        $this->_dir     = Helper::options()->pluginDir('myfriend') . '/myfriend';
    }

    /**
     * @throws \Typecho_Db_Exception
     * @throws \Typecho_Exception
     * @throws \Typecho_Plugin_Exception
     */
    public function init()
    {
        $this->_db      = Typecho_Db::get();
        $this->_options = $this->widget('Widget_Options');
        $this->_cfg     = Helper::options()->plugin('myfriend');
    }

    /**
     * action 入口
     */
    public function action()
    {
        // $this->init();
        if ($this->request->isPost()) {
            $this->on($this->request->is('do=import'))->wjssk_importFriend_callback();
            $this->on($this->request->is('do=check'))->wjssk_statusFriend_callback('check');
            $this->on($this->request->is('do=out'))->wjssk_statusFriend_callback('out');
            $this->on($this->request->is('do=rest'))->wjssk_statusFriend_callback('rest');
            $this->on($this->request->is('do=del'))->wjssk_delFriend_callback();
            $this->on($this->request->is('do=save'))->wjssk_saveFriend_callback();
            $this->on($this->request->is('do=apply'))->wjssk_applyFriend_callback();
        } elseif ($this->request->isGet()) {
            $this->on($this->request->is('do=export'))->wjssk_exportFriend_callback();
            $this->on($this->request->is('do=import'))->wjssk_importFriend_callback();
            $this->on($this->request->is('do=getUrlInfo'))->wjssk_urlInfo_callback();
            $this->on($this->request->is('do=checkUrl'))->wjssk_checkUrl_callback();

        }
    }

    /**
     * 审核好友
     */
    public function wjssk_statusFriend_callback($doAction)
    {
        if (!hasLogin()) {
            throw new Typecho_Plugin_Exception('我感觉你脑子可能不太好哦');
        }
        if ($doAction == 'check') {
            $msg    = '审核';
            $status = 2;
        } elseif ($doAction == 'out') {
            $msg    = '移入黑名单';
            $status = 0;
        } elseif ($doAction == 'rest') {
            $msg    = '移出黑名单';
            $status = 2;
        }

        $ids = $this->request->filter('int')->getArray('id');
        $ing = 0;
        foreach ($ids as $id) {
            $update = $this->_db->update('table.wjssk_myfriends')->rows(['status' => $status, 'check_time' => date('Y-m-d H:i:s')])->where('id=?', $id);
            $res    = $this->_db->query($update);
            $ing++;
        }
        if ($this->request->isAjax()) {
            $this->response->throwJson($ing > 0 ? array('code' => 1, 'message' => _t($msg . '成功！'))
                : array('code' => 0, 'message' => _t($msg . '失败！')));
        } else {
            /** 设置提示信息 */
            $this->widget('Widget_Notice')->set($ing > 0 ? _t($msg . '成功') : _t($msg . '失败'),
                $ing > 0 ? 'success' : 'notice');

            /** 返回原网页 */
            $this->response->goBack();
        }
    }

    /**
     * 将好友移动到黑名单
     */
    public function wjssk_outFriend_callback()
    {
        if (!hasLogin()) {
            throw new Typecho_Plugin_Exception('我感觉你脑子可能不太好哦');
        }
        $ids = $this->request->filter('int')->getArray('id');
        $ing = 0;
        foreach ($ids as $id) {
            $update = $this->_db->update('table.wjssk_myfriends')->rows(['status' => 0])->where('id=?', $id);
            $res    = $this->_db->query($update);
            $ing++;
        }
        if ($this->request->isAjax()) {
            $this->response->throwJson($ing > 0 ? array('code' => 1, 'message' => _t('操作成功！'))
                : array('code' => 0, 'message' => _t('操作失败！')));
        } else {
            /** 设置提示信息 */
            $this->widget('Widget_Notice')->set($ing > 0 ? _t('操作成功') : _t('操作失败'),
                $ing > 0 ? 'success' : 'notice');

            /** 返回原网页 */
            $this->response->goBack();
        }
    }

    /**
     * 将好友移出黑名单
     */
    public function wjssk_restFriend_callback()
    {
        if (!hasLogin()) {
            throw new Typecho_Plugin_Exception('我感觉你脑子可能不太好哦');
        }
        $ids = $this->request->filter('int')->getArray('id');
        $ing = 0;
        foreach ($ids as $id) {
            $update = $this->_db->update('table.wjssk_myfriends')->rows(['status' => 2])->where('id=?', $id);
            $res    = $this->_db->query($update);
            $ing++;
        }
        if ($this->request->isAjax()) {
            $this->response->throwJson($ing > 0 ? array('code' => 1, 'message' => _t('操作成功！'))
                : array('code' => 0, 'message' => _t('操作失败！')));
        } else {
            /** 设置提示信息 */
            $this->widget('Widget_Notice')->set($ing > 0 ? _t('操作成功') : _t('操作失败'),
                $ing > 0 ? 'success' : 'notice');

            /** 返回原网页 */
            $this->response->goBack();
        }
    }

    /**
     * 删除好友
     */
    public function wjssk_delFriend_callback()
    {
        if (!hasLogin()) {
            throw new Typecho_Plugin_Exception('我感觉你脑子可能不太好哦');
        }
        $del_ids     = $this->request->filter('int')->getArray('id');
        $deleteCount = 0;
        foreach ($del_ids as $id) {
            $delete = $this->_db->delete('table.wjssk_myfriends')
                ->where('id = ?', $id);
            $this->_db->query($delete);
            $deleteCount++;
        }
        if ($this->request->isAjax()) {
            $this->response->throwJson($deleteCount > 0 ? array('code' => 1, 'message' => _t('删除成功！'))
                : array('code' => 0, 'message' => _t('删除失败！')));
        } else {
            /** 设置提示信息 */
            $this->widget('Widget_Notice')->set($deleteCount > 0 ? _t('删除成功') : _t('删除失败'),
                $deleteCount > 0 ? 'success' : 'notice');

            /** 返回原网页 */
            $this->response->goBack();
        }
    }

    /**
     * 导出好友数据
     */
    public function wjssk_exportFriend_callback()
    {
        if (!hasLogin()) {
            throw new Typecho_Plugin_Exception('我感觉你脑子可能不太好哦');
        }
        $data = $this->_db->fetchAll($this->_db->select()->from('table.wjssk_myfriends'));

        $file = 'myfriends.json';
        file_put_contents($file, json_encode($data));

        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($file));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            ob_clean();
            flush();
            readfile($file);
            unlink($file);
            exit;
        }
    }


    /**
     * 导入好友数据
     * @throws \Typecho_Plugin_Exception
     */
    public function wjssk_importFriend_callback()
    {
        if (!hasLogin()) {
            throw new Typecho_Plugin_Exception('我感觉你脑子可能不太好哦');
        }
        $is_upload = $this->request->get('is_upload');
        if ($is_upload == 1) {
            $file    = 'myfriends.json';
            $tmpName = $_FILES['file']['tmp_name'];
            copy($tmpName, $file);
            unlink($tmpName);
            $datas = file_get_contents($file);
            unlink($file);
            $datas  = json_decode($datas, true);
            $all    = count($datas);
            $addLng = 0;
            $hasLng = 0;
            if ($all > 0) {
                foreach ($datas as $data) {
                    $query = $this->_db->select()->from('table.wjssk_myfriends')->where('url = ?', $data['url']);
                    $has   = $this->_db->fetchAll($query);
                    if (count($has) > 0) {
                        $hasLng++;
                    } else {
                        $insert   = $this->_db->insert('table.wjssk_myfriends')->rows($data);
                        $insertId = $this->_db->query($insert);
                        if ($insertId) {
                            $addLng++;
                        }
                    }
                }
                echo '<script>';
                echo 'alert("数据导入成功！共 ' . $all . ' 条数据，成功导入 ' . $addLng . ' 条数据；过滤重复 ' . $hasLng . ' 条数据。");';
                echo 'self.opener=null;self.close();';
                echo '</script>';
            } else {
                /** 设置提示信息 */
                $this->widget('Widget_Notice')->set(_t('无数据或数据格式不对！'), 'error');

                /** 返回原网页 */
                $this->response->goBack();
            }
            exit();
        }
        ?>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>myfriend 导入数据</title>

            <link rel="stylesheet"
                  href="https://static.myhosts.ga/bootstrap/3.4.1/css/bootstrap.min.css">
            <style>
                html {
                    padding: 50px 10px;
                    font-size: 16px;
                    line-height: 1.4;
                    color: #666;
                    background: #F6F6F3;
                    -webkit-text-size-adjust: 100%;
                    -ms-text-size-adjust: 100%;
                }

                html,
                input {
                    font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
                }

                body {
                    padding: 0;
                    margin: 0;
                    background-color: unset;
                }

                .wjssk-container {
                    padding: 10px 0;
                    margin: auto 500px;
                    background-color: #fff;
                    text-align: center;
                }

                .form-horizontal input[type=file] {
                    padding-top: 10px;
                    margin: auto;
                }

                @media screen and (max-width: 400px) {
                    .wjssk-container {
                        width: 100%;
                    }
                }
            </style>
        </head>
        <body>
        <div class="wjssk-container">
            <h2>myfriend 导入数据</h2>
            <br/>
            <form class="form-horizontal" action="/action/myfriend_action?do=import" method="post"
                  enctype="multipart/form-data">
                <label for="file" class="control-label">选择JSON数据包</label>
                <input type="file" name="file" id="file"/>
                <br/>
                <input type="hidden" name="is_upload" value="1">
                <button type="submit" class="btn btn-primary">导入</button>
                <button type="button" class="btn btn-default" onclick="self.opener=null;self.close();">取消</button>
            </form>
            <br/>
            <p>注意：导入的数据会通过网址[url]字段过滤，重复的将不会导入！</p>
        </div>
        </body>
        </html>
        <?php
    }

    /**
     * 获取站点信息
     */
    public function wjssk_urlInfo_callback()
    {
        if (!hasLogin()) {
            throw new Typecho_Plugin_Exception('我感觉你脑子可能不太好哦');
        }
        $request = Typecho_Request::getInstance();
        $res     = $this->checkRepeat($request->get('url'));
        if ($res['code']) {
            $url  = $res['url'];
            $mate = get_sitemeta($url);
            if ($mate) {
                echo json_encode(['code' => 1, 'data' => $mate]);
            } else {
                echo json_encode(['code' => 0]);
            }
        } else {
            echo json_encode($res);
        }
    }

    public function wjssk_saveFriend_callback()
    {
        $request = Typecho_Request::getInstance();

        $url = parse_url(trim($request->get('url')));
        if (!empty($url['scheme']) && !empty($url['host'])) {
            $siteUrl = $url['scheme'] . '://' . $url['host'];
        }
        if (empty($request->get('id'))) {
            $res = $this->checkRepeat($siteUrl);
            if (!$res['code']) {
                echo json_encode($res);
                exit();
            }
        }

        $list['url']              = trim($siteUrl);
        $list['title']            = trim($request->get('title'));
        $list['description']      = trim($request->get('description'));
        $list['email']            = trim($request->get('email'));
        $list['type']             = trim($request->get('type'));
        $list['icon']             = trim($request->get('icon'));
        $list['check_friend_url'] = trim($request->get('check_friend_url'));
        $list['create_time']      = date('Y-m-d H:i:s');
        $list['check_time']       = date('Y-m-d H:i:s');
        if ($this->_cfg->friendCheckType == '3' || $request->get('status') == '2') {
            $list['status'] = 2;
        }
        if (empty($request->get('id'))) {
            $save = $this->_db->insert('table.wjssk_myfriends')->rows($list);
        } else {
            $save = $this->_db->update('table.wjssk_myfriends')->rows($list)->where('id = ?', $request->get('id'));
        }
        $id = $this->_db->query($save);
        if ($id) {
            $msg = '提交成功了！';
            if ($this->_cfg->friendCheckType != '3') {
                $msg .= '站长马上就来审核了！很快的。';
                if ($this->_cfg->friendCheckType == '2') {
                    $msg .= '<br />您也可以自助审核哦！这样更快。。。';
                }
            }
            echo json_encode(['code' => 1, 'data' => $id, 'msg' => $msg]);
        } else {
            echo json_encode(['code' => 0]);
        }
    }

    private function checkRepeat($url)
    {
        $url = parse_url($url);
        if (!empty($url['scheme']) && !empty($url['host'])) {
            $finalUrl = $url['scheme'] . '://' . $url['host'];

            $query  = $this->_db->select()->from('table.wjssk_myfriends')->where('url= ?', $finalUrl);
            $result = $this->_db->fetchRow($query);
            if ($result) {
                if ($result['status'] == 0) {
                    return ['code' => 0, 'msg' => '站点在黑名单中！有问题可以联系我！'];
                } else {
                    return ['code' => 0, 'msg' => '站点重复'];
                }
            } else {
                return ['code' => 1, 'url' => $finalUrl];
            }
        } else {
            return ['code' => 0, 'msg' => '站点网站不正确！'];
        }
    }

    public function gotofriend()
    {
        if ($this->request->get('url')) {
            $url  = $this->request->get('url');
            $type = 0;
            if (substr($url, 0, 4) == 'yyds') {
                $type     = 1;
                $url      = explode('_', $url);
                $showType = $url[1];
                $table    = $this->_db->getPrefix() . 'wjssk_myfriends';
                $query    = "SELECT * FROM `" . $table . "` AS t1 JOIN (SELECT ROUND(RAND() * ((SELECT MAX(id) FROM `" . $table . "` where type='" . $showType . "')-(SELECT MIN(id) FROM `" . $table . "` where type='" . $showType . "'))+(SELECT MIN(id) FROM `" . $table . "` where type='" . $showType . "')) AS id) AS t2 WHERE t1.id >= t2.id and type='" . $showType . "' ORDER BY t1.id LIMIT 1;";
            } else {
                $query = $this->_db->select()->from('table.wjssk_myfriends')->where('url=?', $url);
            }
            $res = $this->_db->fetchRow($query);
            if ($res) {
                if ($url != Helper::options()->index && !$type) {
                    $click = $res['click_no'] + 1;
                    $query = $this->_db->update('table.wjssk_myfriends')->rows(['click_no' => $click])->where('id=?', $res['id']);
                    $this->_db->query($query);
                }
                $jumpPage = file_get_contents($this->_dir . '/pages/jump.html');
                $jumpPage = str_replace('[REPLACE]', $res['url'], $jumpPage);
                $jumpPage = str_replace('[REPLACETITLE]', $res['title'], $jumpPage);
                $jumpPage = str_replace('[REPLACEDESC]', $res['description'], $jumpPage);
                echo $jumpPage;
                exit();
            }
        }
        $errorPage = file_get_contents($this->_dir . '/pages/404.html');
        $errorPage = str_replace('[REPLACE]', Helper::options()->index, $errorPage);
        echo $errorPage;
        exit();
    }

    public function wjssk_checkUrl_callback()
    {
        $id    = $this->request->get('id');
        $query = $this->_db->select()->from('table.wjssk_myfriends')->where('id=?', $id);
        $res   = $this->_db->fetchRow($query);
        if ($res) {
            $urlStatus    = http_code($res['url']);
            $friendStatus = 2;
            if ($urlStatus === 200 || $urlStatus === 302) {
                $mydomain     = parse_url(Helper::options()->index);
                $mydomain     = $mydomain['host'];
                $friendStatus = isExistsContentUrl($res['url'] . ($res['check_friend_url'] ?: ''), $mydomain);
            }
            echo json_encode(['code' => 1, 'data' => ['urlStatus' => $urlStatus, 'friendStatus' => $friendStatus]]);
        } else {
            echo json_encode(['code' => 0]);
        }
    }
}
