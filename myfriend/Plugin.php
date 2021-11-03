<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * myfriend，我的朋友【导航插件】 >><a href="https://www.wjssk.com/" target="_blank" style="color:red;">查看更新</a>
 *
 * @package myfriend
 * @author alanyuewei
 * @version 1.0
 * @link https://www.wjssk.com/
 *
 */
include 'common.php';
const ACTION = 'myfriend_action';
define("PLUGIN_URL", Helper::options()->pluginUrl . '/myfriend/');

class myfriend_Plugin implements Typecho_Plugin_Interface
{
/**
 * @var string 导航管理，检测状态以及查看相关信息
 */
public static $panel = 'myfriend/templates/manage.php';
public static $add_panel = 'myfriend/templates/addFriend.php';

/**
 * 激活插件方法,如果激活失败,直接抛出异常
 *
 * @access public
 * @return void
 * @throws Typecho_Plugin_Exception
 */
public static function activate()
{
    Helper::addPanel(3, self::$panel, '我的朋友们', '我的朋友们(导航管理)', 'administrator', false, 'extending.php?panel=' . self::$add_panel);
    Helper::addPanel(3, self::$add_panel, '新增我的朋友们', '新增我的朋友们(导航管理)', 'administrator', true);
    // 绑定动作
    Helper::addAction(ACTION, 'myfriend_Action');
    // 加载页面
    loadPage();
    // 创建数据库
    createTable();
    // 添加访客事件
    Typecho_Plugin::factory('Widget_Archive')->footer = array(__CLASS__, 'checkRefUrl');
    // 绑定路由，美化页面跳转
    Helper::addRoute("gotofriend", "/gofriend", "myfriend_Action", "gotofriend");

    return _t('插件已激活,请设置相关信息');
}

/**
 * 禁用插件方法,如果禁用失败,直接抛出异常
 *
 * @static
 * @access public
 * @return void
 * @throws Typecho_Plugin_Exception
 */
public static function deactivate()
{
    Helper::removeAction(ACTION);
    Helper::removePanel(3, self::$panel);
    Helper::removePanel(3, self::$add_panel);
    Helper::removeRoute('gotofriend');
    uninstallPage();
    $option = Helper::options()->plugin('myfriend');
    if ($option->friendIsClear) {
        deleteTable();
    }
    return _t('插件已被禁用');
}

/**
 * 获取插件配置面板
 *
 * @access public
 * @param Typecho_Widget_Helper_Form $form 配置面板
 * @return void
 */
public static function config(Typecho_Widget_Helper_Form $form)
{
?>
<link rel="stylesheet" href="<?php echo PLUGIN_URL . 'assets/css.css'; ?>">
<div class="joe_config">
    <div class="joe_config__aside">
        <div class="logo">myfriend 配置</div>
        <ul class="tabs">
            <li class="item" data-current="wjssk-notice">插件介绍</li>
            <li class="item" data-current="wjssk-config">基本配置</li>
            <li class="item click"><a href="/action/<?php echo ACTION; ?>?do=import">导入数据</a>
            <li class="item click"><a
                        href="<?php echo Helper::options()->index ?>/action/<?php echo ACTION; ?>?do=export"
                        download="myfriends.json">导出数据</a>
            </li>
        </ul>
    </div>
    <div class="wjssk-notice">
        <p class="title">WJSSK myfriend 我的好友(导航插件)</p>
        <p>突发奇想的灵感，想搞个友情链接。(其实就是想做个导航程序，但是不会写typeche的主题，所以就搞在插件里，先练练手)。</p>
        <p>很大部分参考了新版Joe主题(感谢<a href="https://78.al" target="_blank">78.AL</a>)。</p>
        <p>如果有Bug或者您有好的想法，可以<a href="https://wjssk.com" target="_blank">告诉我</a>，我会看着改。</p>
        <hr>
        <p>使用说明：</p>
        <ol>
            <li>首先添加独立页面，选择自定义模板->[朋友们(导航)]</li>
            <li>然后就可以去前台页面查看显示情况</li>
            <li>最后可以去[管理]菜单，然后进入[我的朋友们]里面添加友联导航</li>
            <li><span style="background-color: red;color:#ffffff;">!!! 目前只支持JOE主题 !!!</span> 请慎用！其他主题因为我没有，所以我还没弄！</li>
        </ol>
        <hr>
        <p>最后感谢您使用本插件！！！</p>
    </div>
    <script src="<?php echo PLUGIN_URL . 'assets/js.js'; ?>"></script>
    <?php
    $friendType = new Typecho_Widget_Helper_Form_Element_Textarea(
        'friendType',
        NULL,
        _t('博客论坛||影音娱乐||实用工具||API接口'),
        _t('好友(导航)分类'),
        _t('介绍：用于自定义导航格式 <br />例如：博客论坛||影音娱乐||实用工具||API接口<br>之后会自动按照顺序开始排列，例如上面：0=博客论坛，1=影音娱乐，2=实用工具，3=API接口')
    );
    $friendType->setAttribute('class', 'joe_content wjssk-config');
    $form->addInput($friendType);

    $friendNotice = new Typecho_Widget_Helper_Form_Element_Textarea(
        'friendNotice',
        NULL,
        _t('<a href="#apply_friend"><img src="https://inews.gtimg.com/newsapp_ls/0/14128187891/0"></a>||茫茫人海初相见，不是欠债就是缘||-||wjssk - 我就试试看 - wjssk - 我就试试看 - wjssk - 我就试试看 - wjssk - 我就试试看 - wjssk - 我就试试看'),
        _t('好友(导航)公告'),
        _t('介绍：用于自定义导航公告，在每个导航类别之前添加公告！<br>允许html代码，公告中除了包含图片(&lt;img&gt;)会显示图片，其他都是文字滚动格式；<br />例如：' . htmlentities('<a href="#apply_friend"><img src="https://inews.gtimg.com/newsapp_ls/0/14128187891/0"></a>') . '||茫茫人海初相见，不是欠债就是缘||-||wjssk - 我就试试看 - wjssk - 我就试试看 - wjssk - 我就试试看 - wjssk - 我就试试看 - wjssk - 我就试试看；<br>之后会自动按照顺序开始显示。')
    );
    $friendNotice->setAttribute('class', 'joe_content wjssk-config');
    $form->addInput($friendNotice);

    $friendAddType = new Typecho_Widget_Helper_Form_Element_Radio(
        'friendAddType',
        array(
            '0' => _t('手动添加'),
            '1' => _t('自主添加'),
        ),
        '1',
        _t('好友(导航)添加方式'),
        _t('手动添加：只能站长手动添加；<br>自主添加：其他站长自己主动申请提交')
    );
    $friendAddType->setAttribute('class', 'joe_content wjssk-config');
    $form->addInput($friendAddType);

    $friendCheckType = new Typecho_Widget_Helper_Form_Element_Select(
        'friendCheckType',
        array(
            '1' => '手动审核',
            '2' => '跳转来源审核',
            '3' => '自动审核'
        ),
        '2',
        _t('选择审核方式'),
        _t('手动审核：只能站长审核才可以显示；<br>跳转来源审核：需要对方添加友联，点击友联跳回自动审核；这个也可以手动审核<br>自动审核：添加后自动审核通过')
    );
    $friendCheckType->setAttribute('class', 'joe_content wjssk-config');
    $form->addInput($friendCheckType->multiMode());

    $showRankNum = new Typecho_Widget_Helper_Form_Element_Select(
        'showRankNum',
        array(
            '10' => _t('10'),
            '15' => _t('15'),
            '20' => _t('20'),
            '25' => _t('25'),
            '30' => _t('30'),
        ),
        '15',
        _t('排行榜显示数量'),
        _t('设定右侧排行榜榜单数量')
    );
    $showRankNum->setAttribute('class', 'joe_content wjssk-config');
    $form->addInput($showRankNum);

    $friendIsClear = new Typecho_Widget_Helper_Form_Element_Radio(
        'friendIsClear',
        array(
            '0' => _t('否'),
            '1' => _t('是'),
        ),
        '0',
        _t('禁用后是否清除数据'),
        _t('是否清除好友(导航)数据')
    );
    $friendIsClear->setAttribute('class', 'joe_content wjssk-config');
    $form->addInput($friendIsClear);

    /*$friendShowStyle = new Typecho_Widget_Helper_Form_Element_Select(
        'friendShowStyle',
        array(
            '1' => '默认',
            '2' => 'Joe'
        ),
        '2',
        _t('选择显示主题风格'),
        _t('根据不同的主题显示不同的风格，目前没接触过啥主题，所以就这俩了；<br>以后我会加添加风格的教程，或着大佬自己开发，或着找我也行。')
    );
    $friendShowStyle->setAttribute('class', 'joe_content wjssk-config');
    $form->addInput($friendShowStyle->multiMode());*/

    }

    /**
     * 个人用户的配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }

    public static function checkRefUrl()
    {
        // 有可能获取不到HTTP_REFERER；
        // 是因为自己的站点是http，而来源站点是https，这样就可能获取不到HTTP_REFERER;
        // 需要来源站的<head>添加 <meta content="always" name="referrer">
        $option = Helper::options()->plugin('myfriend');
        if ($option->friendCheckType == 2) {
            if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
                $url = parse_url($_SERVER['HTTP_REFERER']);
                if (!empty($url['scheme']) && !empty($url['host'])) {
                    $refUrl = $url['scheme'] . '://' . $url['host'];
                    if ($refUrl != Helper::options()->index) {
                        self::reloadUrl($refUrl);
                    }
                }
            }
        }
    }

    public static function reloadUrl($url)
    {
        $db    = Typecho_Db::get();
        $query = $db->select()->from('table.wjssk_myfriends')->where('url=?', $url);
        $res   = $db->fetchRow($query);

        $updateData = ['visit_no' => ($res['visit_no'] + 1), 'last_visit_time' => date('Y-m-d H:i:s')];

        if ($res['status'] == 1) {
            $updateData['status'] = 2;
            $query                = $db->update('table.wjssk_myfriends')->rows($updateData)->where('id=?', $res['id']);
            $db->query($query);
            echo '<script src="https://cdn.jsdelivr.net/gh/alanyuewei/static@latest/layer/3.5.1/layer.js"></script>';
            echo '<script>';
            echo 'layer.alert("欢迎您的加入，<br />【' . $res['title'] . '(' . $res['url'] . ')】",{title:"检测到有新的站点来源"})';
            echo '</script>';
            exit();
        } else {
            $query = $db->update('table.wjssk_myfriends')->rows($updateData)->where('id=?', $res['id']);
            $db->query($query);
        }
    }
    }

    ?>
