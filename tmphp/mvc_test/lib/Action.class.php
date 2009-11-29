<?php
abstract class Action{
	protected $tpl;
	protected $tplVarArr;
	public function __construct($path) {
		$options = array(
		'template_dir' => $path.'/Views/', //指定模板文件存放目录
		'cache_dir' => $path.'/Views/cache/', //指定缓存文件存放目录
		'auto_update' => true, //当模板文件有改动时重新生成缓存 [关闭该项会快一些]
		'cache_lifetime' => 0, //缓存生命周期(分钟)，为 0 表示永久 [设置为 0 会快一些]
		'suffix' => '.html', //模板文件后缀
		);
		$this->tpl = Template::getInstance(); //使用单件模式实例化模板类
		$this->tpl->setOptions($options); //设置模板参数
	}
	/**
	 * 设置模板变量
	 *
	 * @param string $key   模板页面变量
	 * @param mixed $value  对应程序中的变量
	 * @access public
	 * @return void
	 */
	public function assign($key, $value) {
		$this->tplVarArr[$key] = $value;
	}
	/**
	 * 显示模板
	 *
	 * @param string $tpl  模板文件名 为空时就和类(Action)名相同\
	 * @access public
	 * @return void
	 */
	public function show($tpl = null) {
		if (empty($tpl)) {
			include($this->tpl->getfile(get_class($this)));
		} else {
			include($this->tpl->getfile($tpl));
		}
	}
	/**
	 * 执行Action.子类必须实现此方法
	 *
	 */
	abstract public function execute();
}
?>