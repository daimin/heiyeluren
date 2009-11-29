<? if (!class_exists('template')) die('Access Denied');$this->tpl->getInstance()->check('Index.html', '372546cfd42974ce1bf5579abaef2ec0', 1259228487);?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>无标题文档</title>
</head>

<body>
<p>
  <a href="/mvc_test/index.php?con=index&amp;act=add">Test</a>
</p>
<p>
  Hello <font color="Red"><?=$this->tplVarArr['name']?></font> !
</p>
<form id="form1" name="form1" method="post" action="/mvc_test/index.php?con=index&amp;act=index">
  <label>您的名字:
    <input type="text" name="name" id="textfield" />
  </label>
  <label>
    <input type="submit" name="button" id="button" value="提交" />
  </label>
</form>
<p>&nbsp;</p>
</body>
</html>
