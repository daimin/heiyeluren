<?php

;;
;; 公共配置
;;
[Common]
UrlReWrite = false			;; 是否开启urlrewrite
CharSet = UTF-8				;; 文档编码
TimeZone = Asia/Chongqing	;; 时区设置
UrlHtml = true				;; 是否开启伪静态
AutoFilter = true			;; 是否进行自动对POST.GET.COOKIE进行过滤

[Framework]
ControllerName = con		;; 控制器变量名
ActionName = act			;; Action 变量名
DefaultController = index	;; 缺省的控制器名
DefaultAction = index		;; 缺省的Action名


;;
;; 数据库配置
;;
[DataBase]
Connection = false			;; 是否连接数据库
host = localhost			;; 数据库主机地址
user = root					;; 数据库连接账户名
passwd = root				;; 数据库连接密码
name = tmphp				;; 数据库名
charset = utf8				;; 数据库字符集


?>