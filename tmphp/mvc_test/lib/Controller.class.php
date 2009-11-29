<?php
class Controller{
	var $path;
	var $config;
	var $controllerFile;
	var $controllerClass;
	public function __construct($path, $config) {
		$this->path = $path;
		$this->config = $config;
	}
	/**
	 * 设置程序目录
	 *
	 * @param string $path 模板路径
	 * @return void
	 * @access public
	 */
	public function setPath($path) {
		$this->path = $path;
	}

	/**
	 * 读取全局设置
	 *
	 * @param array $config  设置数组
	 * @return void
	 * @access public
	 */
	public function setConfig($config) {
		$this->config = $config;
	}

	public function run() {
		if (empty($this->path)) {
			$this->throwException("没有设置程序目录");
		}
		if(isset($_GET['con'])){
			$controller = trim($_GET['con']);
		}else{
			$controller = "index";
		}
		if(isset($_GET['act'])){
			$act = trim($_GET['act']);
		}else{
			$act = "index";
		}
		$this->controllerFile = "./Controller/".ucfirst(strtolower($controller)).".php";
		if(!is_file($this->controllerFile)) {
			$this->throwException("错误的请求，找不到Controller文件(".$this->controllerFile.")");
		} else {
			include_once($this->controllerFile);
		}
		$this->controllerClass = ucfirst(strtolower($act));
		if(!class_exists($this->controllerClass)) {
			$this->throwException("错误的请求，找不到Controller类(".$this->controllerClass.")");
		} else {
			$newAction = new $this->controllerClass($this->path);
			$newAction->execute();
		}
	}

	/**
     * 抛出一个错误信息
     *
     * @param string $message 错误信息
     * @return void
     */
	 private function throwException($message) {
		throw new Exception($message);
	 }
}
?>