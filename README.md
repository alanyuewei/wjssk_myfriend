# wjssk_myfriend
突发奇想的灵感，想搞个友情链接。(其实就是想做个导航程序，但是不会写typeche的主题，所以就搞在插件里，先练练手)。

# 这是一个typecho插件
目前只支持显示在`JOE`主题下使用！其他主题尚未开发！

# 特别说明
很大部分参考了新版Joe主题(感谢78.AL)。
如果有Bug或者您有好的想法，可以告诉我，我会看着改。

# 错误情况
-2021-11-03 23:12:15
*静态资源cdn掉了( （╯‵□′）╯︵┴─┴ )
需要将`https://static.myhosts.ga/`替换成`https://cdn.jsdelivr.net/gh/alanyuewei/static@latest/`

------

-2021-11-03 10:10:10
*新建数据表的字段类型错了，需要修改一下
```
ALTER TABLE typecho_wjssk_myfriends MODIFY check_friend_url varchar(255) COMMENT '检测友链地址';
```
也可以重新下载

# 图片展示
![首页展示](https://screenshotting.site/i/8e631b.jpg)
