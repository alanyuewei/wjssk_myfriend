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
$row        = [];
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $query = $db->select()->from('table.wjssk_myfriends')->where('id=?', $_GET['id']);
    $row   = $db->fetchRow($query);
}

?>
<link rel="stylesheet"
      href="https://cdn.jsdelivr.net/gh/alanyuewei/static@latest/bootstrap/3.4.1/css/bootstrap.min.css">
<div class="main">
    <div class="body container">
        <?php include 'page-title.php'; ?>
        <div class="row typecho-page-main" role="main">
            <form class="form-horizontal">
                <div class="form-group">
                    <label for="url" class="col-sm-2 control-label">好友站点</label>
                    <div class="col-md-8 col-sm-10">
                        <div class="input-group">
                            <input type="text" class="form-control" autocomplete="off" required id="url"
                                   placeholder="好友站点。请注意站点请填写网站：http://或https://"
                                   value="<?php echo $row['url'] ?: ''; ?>">
                            <div class="input-group-addon" id="getInfo" style="cursor: pointer;">获取信息</div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="title" class="col-sm-2 control-label">好友站点标题</label>
                    <div class="col-md-8 col-sm-10">
                        <input type="text" class="form-control" autocomplete="off" required id="title"
                               placeholder="好友站点标题" value="<?php echo $row['title'] ?: ''; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="description" class="col-sm-2 control-label">好友站点描述</label>
                    <div class="col-md-8 col-sm-10">
                        <textarea class="form-control" autocomplete="off" name="description" id="description"
                                  placeholder="好友站点描述" rows="3"><?php echo $row['description'] ?: ''; ?></textarea>
                    </div>
                </div>
                <div class="form-group">
                    <label for="title" class="col-sm-2 control-label">好友站点icon</label>
                    <div class="col-md-8 col-sm-10">
                        <input type="text" class="form-control" autocomplete="off" required id="icon"
                               placeholder="好友站点标题" value="<?php echo $row['icon'] ?: ''; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="type" class="col-sm-2 control-label">站点类别</label>
                    <div class="col-md-8 col-sm-10">
                        <select id="type">
                            <?php
                            $_has = $row['type'] ?: '';
                            foreach ($friendType as $k => $val) {
                                echo '<option value="' . $k . '" ' . ($k == $_has ? 'selected' : '') . '>' . $val . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="email" class="col-sm-2 control-label">好友邮箱</label>
                    <div class="col-md-8 col-sm-10">
                        <input type="text" autocomplete="off" class="form-control" id="email"
                               value="<?php echo $row['email'] ?: ''; ?>" placeholder="好友站长邮箱">
                    </div>
                </div>
                <div class="form-group">
                    <label for="email" class="col-sm-2 control-label">友联地址</label>
                    <div class="col-md-8 col-sm-10">
                        <input type="text" autocomplete="off" class="form-control" id="check_friend_url"
                               value="<?php echo $row['check_friend_url'] ?: ''; ?>"
                               placeholder="友联不在首页,请填写友联页地址，不用域名(例如：/friend)">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <input type="hidden" id="form_id" value="<?php echo $row['id'] ?: ''; ?>">
                        <button type="button" class="btn btn-primary" id="save">保存</button>
                        <a href="<?php echo Helper::options()->adminUrl(); ?>extending.php?panel=myfriend/templates/manage.php"
                           class="btn btn-default" id="back">取消</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/gh/alanyuewei/static@latest/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/gh/alanyuewei/static@latest/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/gh/alanyuewei/static@latest/layer/3.5.1/layer.js"></script>
<script>
    var baseUrl = '<?php echo Helper::options()->index(); ?>';
    $('#getInfo').on('click', function () {
        var url = $('#url').val();
        if (url === '') {
            layer.alert('请填写好友站点网址！', {icon: 5});
            return false;
        }
        var rx = /^https?:\/\//i;
        if (!rx.test(url)) {
            layer.alert('好友站点网址填写不完整！', {icon: 5});
            return false;
        }
        var load = layer.load(2);
        $.get(baseUrl + 'action/myfriend_action?do=getUrlInfo', {url: url}, function (res) {
            layer.close(load);
            if (res.code) {
                $('#title').val(res.data.title);
                $('#description').val(res.data.description);
            } else {
                layer.alert(res.msg || '获取站点信息失败！请手动添加！', {icon: 5});
            }
        }, 'json');
    });
    $('#save').on('click', function () {
        var url = $('#url').val();
        if (url === '') {
            layer.alert('请填写好友站点网址！', {icon: 5});
            return false;
        }

        var rx = /^https?:\/\//i;
        if (!rx.test(url)) {
            layer.alert('好友站点网址填写不完整！', {icon: 5});
            return false;
        }

        var title = $('#title').val();
        if (title === '') {
            layer.alert('好友站点标题不能为空！', {icon: 5});
            return false;
        }

        var description = $('#description').val();
        if (description === '') {
            layer.alert('好友站点描述不能为空！', {icon: 5});
            return false;
        }

        var icon = $('#icon').val();
        if (icon === '') {
            layer.alert('好友站点icon不能为空！', {icon: 5});
            return false;
        }

        var type = $('#type').val();
        if (type === '') {
            layer.alert('请选择站点类别！', {icon: 5});
            return false;
        }

        var email = $('#email').val();
        if (email === '') {
            layer.alert('请填写好友邮箱！', {icon: 5});
            return false;
        }

        var load = layer.load(2);
        $.post(baseUrl + 'action/myfriend_action?do=save', {
            url             : url,
            title           : title,
            description     : description,
            icon            : icon,
            email           : email,
            type            : type,
            id              : $('#form_id').val(),
            status          : 2,
            check_friend_url: $('#check_friend_url').val()
        }, function (res) {
            layer.close(load);
            if (res.code) {
                if ($('#form_id').val() !== '') {
                    layer.alert("修改成功！", {icon: 6}, function () {
                        window.location.href = "<?php echo Helper::options()->adminUrl(); ?>extending.php?panel=myfriend/templates/manage.php";
                    });
                }
                layer.confirm('添加成功！是否继续添加？', {
                    btn: ['继续', '不了']
                }, function () {
                    window.location.reload();
                }, function () {
                    window.location.href = "<?php echo Helper::options()->adminUrl(); ?>extending.php?panel=myfriend/templates/manage.php";
                });
            } else {
                layer.alert(res.msg || '保存站点失败！', {icon: 5});
            }
        }, 'json');
    });
</script>
<?php
include 'copyright.php';
include 'common-js.php';
include 'table-js.php';
include 'footer.php';
?>

