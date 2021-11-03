<?php
if (!defined('__TYPECHO_ADMIN__')) {
    exit;
}
include 'header.php';
include 'menu.php';

$options    = Typecho_Widget::widget('Widget_Options');
$option     = $options->plugin('myfriend');
$friendType = explode("||", $option->friendType);
$db         = Typecho_Db::get();
$showStatus = 'checked';
$status     = 2;
$type       = '';
echo Typecho_Cookie::get('__wjssk_friend_status');
if ('2' == $request->get('__wjssk_friend_status') || '2' == Typecho_Cookie::get('__wjssk_friend_status')) {
    $showStatus = 'checked';
    $status     = 2;
}
if ('1' == $request->get('__wjssk_friend_status') || '1' == Typecho_Cookie::get('__wjssk_friend_status')) {
    $showStatus = 'checking';
    $status     = 1;
}
if ('0' == $request->get('__wjssk_friend_status') || '0' == Typecho_Cookie::get('__wjssk_friend_status')) {
    $showStatus = 'out';
    $status     = 0;
}
$choFriendType = $request->get('__wjssk_friend_type') ?: Typecho_Cookie::get('__wjssk_friend_type');
?>
<link href="https://cdn.bootcdn.net/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" rel="stylesheet">
<style>
    button.btn-small {
        height: 25px;
        padding: 0 8px;
    }

    button.btn-danger {
        background-color: #e1ca00 !important;
        border: 1px solid #e1ca00 !important;
    }

    span.chk-success {
        background-color: #00a0d1;
        color: #ffffff;
        padding: 2px 5px;
        border-radius: 2px;
        font-size: 12px;
    }

    span.chk-error {
        background-color: red;
        color: #ffffff;
        padding: 2px 5px;
        border-radius: 2px;
        font-size: 12px;
    }
</style>
<div class="main">
    <div class="body container">
        <?php include 'page-title.php'; ?>
        <div class="row typecho-page-main" role="main">
            <div class="col-mb-12 typecho-list">
                <div class="clearfix">
                    <!-- 分类 -->
                    <ul class="typecho-option-tabs right">
                        <li class="<?php if ($choFriendType == 'all') {
                            echo 'current';
                        } ?>">
                            <a href="<?php echo $request->makeUriByRequest('__wjssk_friend_type=all'); ?>"><?php _e('所有'); ?></a>
                        </li>
                        <?php foreach ($friendType

                        as $k => $val) { ?>
                        <li class="<?php if ($choFriendType == 'type_' . $k) {
                            $type = $k;
                            echo 'current';
                        } ?>">
                            <a href="<?php echo $request->makeUriByRequest('__wjssk_friend_type=type_' . $k); ?>"><?php _e($val); ?></a>
                        <li>
                            <?php } ?>
                    </ul>
                    <!-- 状态 -->
                    <ul class="typecho-option-tabs">
                        <li class="<?php if ($showStatus == 'checked') {
                            echo 'current';
                        } ?>">
                            <a href="<?php echo $request->makeUriByRequest('__wjssk_friend_status=2'); ?>"><?php _e('已审核'); ?></a>
                        </li>
                        <li class="<?php if ($showStatus == 'checking') {
                            echo 'current';
                        } ?>">
                            <a href="<?php echo $request->makeUriByRequest('__wjssk_friend_status=1'); ?>"><?php _e('待审核'); ?></a>
                        </li>
                        <li class="<?php if ($showStatus == 'out') {
                            echo 'current';
                        } ?>">
                            <a href="<?php echo $request->makeUriByRequest('__wjssk_friend_status=0'); ?>"><?php _e('黑名单'); ?></a>
                        </li>
                    </ul>
                </div>
                <div class="typecho-list-operate clearfix">
                    <form method="get">
                        <div class="operate">
                            <label><i class="sr-only"><?php _e('全选'); ?></i><input type="checkbox"
                                                                                   class="typecho-table-select-all"/></label>
                            <div class="btn-group btn-drop">
                                <button class="btn dropdown-toggle btn-s" type="button"><i
                                            class="sr-only"><?php _e('操作'); ?></i><?php _e('选中项'); ?> <i
                                            class="i-caret-down"></i></button>
                                <ul class="dropdown-menu">
                                    <?php if ($status == 1) : ?>
                                        <li><a lang="<?php _e('你确认要审核选中的内容吗?'); ?>"
                                               href="<?php $security->index('/action/myfriend_action?do=check'); ?>"><?php _e('审核'); ?></a>
                                        </li>
                                    <?php endif; ?>
                                    <?php if ($status == 1 || $status == 2) : ?>
                                        <li><a lang="<?php _e('你确认要选中的内容移动到黑名单吗?'); ?>"
                                               href="<?php $security->index('/action/myfriend_action?do=out'); ?>"><?php _e('移入黑名单'); ?></a>
                                        </li>
                                    <?php endif; ?>
                                    <?php if ($status == 0) : ?>
                                        <li><a lang="<?php _e('你确认要选中的内容移动到黑名单吗?'); ?>"
                                               href="<?php $security->index('/action/myfriend_action?do=rest'); ?>"><?php _e('移出黑名单'); ?></a>
                                        </li>
                                    <?php endif; ?>
                                    <li><a lang="<?php _e('你确认要删除选中的内容吗?'); ?>"
                                           href="<?php $security->index('/action/myfriend_action?do=del'); ?>"><?php _e('删除'); ?></a>
                                    </li>
                                    <?php if ($status == 2) : ?>
                                        <li>
                                            <a href="javascript:;" class="checkFriend"><?php _e('检测'); ?></a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                        <div class="search" role="search">
                            <input type="text" class="text-s" placeholder="<?php _e('请输入关键字'); ?>"
                                   value="<?php echo htmlspecialchars($request->keywords); ?>" name="keywords"/>
                            <button type="submit" class="btn btn-s"><?php _e('筛选'); ?></button>
                        </div>
                    </form>
                </div>
                <form method="post" name="manage_posts" class="operate-form">
                    <div class="typecho-table-wrap">
                        <table class="typecho-list-table">
                            <thead>
                            <tr>
                                <!-- checkbox -->
                                <th style="width: 20px"></th>
                                <th style="width: 155px"></th>
                                <!-- 来访次数 -->
                                <th style="width: 100px"><?php _e('来访'); ?></th>
                                <!-- 点击次数 -->
                                <th style="width: 100px"><?php _e('点击'); ?></th>
                                <th style="width: 150px"><?php _e('类别'); ?></th>
                                <th style="width: 20%"><?php _e('网址'); ?></th>
                                <th style="width: 15%"><?php _e('标题'); ?></th>
                                <th style="width: 15%"><?php _e('管理员'); ?></th>
                                <th style="width: 100px"><?php _e('排序'); ?></th>
                                <th style="width: 200px"><?php _e('操作'); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $query = $db->select()->from('table.wjssk_myfriends')->where('status = ?', $status);
                            if ($type !== '') {
                                $query = $query->where('type = ?', $type);
                            }
                            $res = $db->fetchAll($query);
                            foreach ($res

                                     as $k => $val) {
                                ?>
                                <tr title="<?php _e($val['description']); ?>" id="">
                                    <td><input type="checkbox" value="<?php echo $val['id']; ?>" name="id[]"/></td>
                                    <td class="showCheckStatus-<?php echo $val['id']; ?>"><?php echo $_COOKIE['urlStatus_' . $val['id']]; ?></td>
                                    <td><?php _e($val['visit_no']); ?></td>
                                    <td><?php _e($val['click_no']); ?></td>
                                    <td><?php _e($friendType[$val['type']]); ?></td>
                                    <td><a href="<?php $security->index('/gofriend?url=' . $val['url']); ?>"
                                           target="_blank" title="点击访问"><?php _e($val['url']); ?></a>
                                    </td>
                                    <td><?php _e($val['title']); ?></td>
                                    <td><?php _e($val['email']); ?></td>
                                    <td><?php _e($val['sort']); ?></td>
                                    <td>
                                        <?php if ($val['status'] == 0) { ?>
                                            <button type="button" class="btn btn-small btn-danger rest"
                                                    data-id="<?php echo $val['id']; ?>">恢复
                                            </button>
                                        <?php } ?>
                                        <?php if ($val['status'] == 1) { ?>
                                            <button type="button" class="btn btn-small btn-danger check"
                                                    data-id="<?php echo $val['id']; ?>">审核
                                            </button>
                                        <?php } ?>
                                        <button type="button" class="btn btn-small primary edit"
                                                data-id="<?php echo $val['id']; ?>">修改
                                        </button>
                                        <button type="button" class="btn btn-small del"
                                                data-id="<?php echo $val['id']; ?>">删除
                                        </button>
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </form>
                <div class="typecho-list-operate clearfix">
                    <form method="get">
                        <div class="operate">
                            <label><i class="sr-only"><?php _e('全选'); ?></i><input type="checkbox"
                                                                                   class="typecho-table-select-all"/></label>
                            <div class="btn-group btn-drop">
                                <button class="btn dropdown-toggle btn-s" type="button"><i
                                            class="sr-only"><?php _e('操作'); ?></i><?php _e('选中项'); ?> <i
                                            class="i-caret-down"></i></button>
                                <ul class="dropdown-menu">
                                    <?php if ($status == 1) : ?>
                                        <li><a lang="<?php _e('你确认要审核选中的内容吗?'); ?>"
                                               href="<?php $security->index('/action/myfriend_action?do=check'); ?>"><?php _e('审核'); ?></a>
                                        </li>
                                    <?php endif; ?>
                                    <?php if ($status == 1 || $status == 2) : ?>
                                        <li><a lang="<?php _e('你确认要选中的内容移动到黑名单吗?'); ?>"
                                               href="<?php $security->index('/action/myfriend_action?do=out'); ?>"><?php _e('移入黑名单'); ?></a>
                                        </li>
                                    <?php endif; ?>
                                    <?php if ($status == 0) : ?>
                                        <li><a lang="<?php _e('你确认要选中的内容移动到黑名单吗?'); ?>"
                                               href="<?php $security->index('/action/myfriend_action?do=rest'); ?>"><?php _e('移出黑名单'); ?></a>
                                        </li>
                                    <?php endif; ?>
                                    <li><a lang="<?php _e('你确认要删除选中的内容吗?'); ?>"
                                           href="<?php $security->index('/action/myfriend_action?do=del'); ?>"><?php _e('删除'); ?></a>
                                    </li>
                                    <?php if ($status == 2) : ?>
                                        <li>
                                            <a lang="<?php _e('检测时间可能会很长！<br /> 主要是检测站点状态，以及友链状态'); ?>"
                                               href="javascript:;" class="checkFriend"><?php _e('检测'); ?></a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/gh/alanyuewei/static@latest/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/gh/alanyuewei/static@latest/layer/3.5.1/layer.js"></script>
<script>
    $('.edit').on('click', function () {
        window.location.href = 'extending.php?panel=myfriend/templates/addFriend.php&id=' + $(this).data('id');
    });
    $('.del').on('click', function () {
        do_action($(this), '确定要删除选中的内容吗？', '<?php $security->index('/action/myfriend_action?do=del'); ?>');
    });
    $('.rest').on('click', function () {
        do_action($(this), '确定要恢复选中的内容吗？', '<?php $security->index('/action/myfriend_action?do=rest'); ?>');
    });
    $('.check').on('click', function () {
        do_action($(this), '确定要审核选中的内容吗？', '<?php $security->index('/action/myfriend_action?do=check'); ?>');
    });

    function do_action(_this, msg, action) {
        if (confirm(msg)) {
            var field = {};
            field['id[]'] = _this.data('id');
            $.post(action, field, function (res) {
                layer.alert(res.message);
                if (res.code) {
                    _this.parents('tr').remove();
                }
            });
        }
    }

    let reqCheck, needCheck = [], loadCheck;
    $('.checkFriend').on('click', function () {
        if (confirm('检测时间可能会有点长！主要检测站点是否可以正常访问，以及友链是否存在。')) {
            loadCheck = layer.open({
                title     : false,
                closeBtn  : false,
                btn       : ['stop'],
                btnAlign  : 'c',
                offset    : 't',
                content   : '<div style="text-align:center"><i class="fa fa-spinner fa-spin"></i> 检测中，请稍候。。。</div>',
                function(index, layero) {
                    layer.close(index);
                }, success: function (l, i) {
                    $('.typecho-list-table').find('tbody').find('input[type="checkbox"]').each(function () {
                        if ($(this).is(':checked')) {
                            needCheck.push($(this).val());
                        }
                    });
                    if (needCheck.length) {
                        req_check_fun(0);
                    } else {
                        $('.typecho-list-table').find('tbody').find('input[type="checkbox"]').each(function () {
                            needCheck.push($(this).val());
                        });
                        if (needCheck.length) {
                            req_check_fun(0);
                        }
                    }
                }, end    : function () {
                    reqCheck.abort();
                }
            })
        }
    });

    function req_check_fun(i) {
        if (!needCheck[i]) {
            layer.close(loadCheck);
            layer.alert('检测完成', {icon: 6});
            return false;
        }
        reqCheck = $.get("<?php $security->index('/action/myfriend_action?do=checkUrl'); ?>", {id: needCheck[i]}, function (res) {
            if (res.code) {
                let str = '';
                if (res.data.urlStatus === 200 || res.data.urlStatus === 302) {
                    str += '<span class="chk-success">正常访问</span>';
                } else {
                    str += '<span class="chk-error">访问失败</span>';
                }

                if (res.data.friendStatus === true) {
                    str += '<span class="chk-success">友链正常</span>';
                } else if (res.data.friendStatus === false) {
                    str += '<span class="chk-error">没有友链</span>';
                }
                $.cookie('urlStatus_' + needCheck[i], str);
                $('.showCheckStatus-' + needCheck[i]).html(str);
                i++;
                req_check_fun(i);
            } else {
                layer.alert('检测功能出错！', {icon: 5});
            }
        }, 'json');
    }
</script>
<?php
include 'copyright.php';
include 'common-js.php';
include 'table-js.php';
include 'footer.php';
?>
