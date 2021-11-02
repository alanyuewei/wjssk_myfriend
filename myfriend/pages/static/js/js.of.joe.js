$(function () {
    function test_show_width() {
        $('.test-show a.wjssk_myfriend__item-detail').css('width', $('a.wjssk_myfriend__item-detail:eq(0)').css('width'));
    }

    test_show_width();
    $(window).resize(function () {
        test_show_width();
    });

    $('#title').on('input', function () {
        $('.test-show a.wjssk_myfriend__item-detail').find('.title').attr('title', $(this).val()).html($(this).val());
    });

    $('#url').on('input', function () {
        $('.test-show a.wjssk_myfriend__item-detail').attr('href', $(this).val());
    });

    $('#icon').on('input', function () {
        $('.test-show a.wjssk_myfriend__item-detail').find('img').attr('src', $(this).val());
    });

    $('#description').on('input', function () {
        $('.test-show a.wjssk_myfriend__item-detail').find('.desc').attr('title', $(this).val()).html($(this).val());
    });

    $('#save').on('click', function () {
        let check = ['title', 'url', 'icon', 'description', 'email'];
        for (let c of check) {
            if ($('#' + c).val() === '') {
                layer.msg($('#' + c).attr('placeholder'), {icon: 5});
                return false;
            }
        }
        $.post('/action/myfriend_action?do=save', $('.apply-friend-form').serialize(), function (res) {
            if (res.code) {
                layer.msg(res.msg, {icon: 6}, function () {
                    window.location.reload();
                });
            } else {
                layer.msg(res.msg, {icon: 5})
            }
        }, 'json');
    });
})
