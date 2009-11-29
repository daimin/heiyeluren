<?php
class Index extends Action {
	function execute() {
		$name = isset($_POST['name']) ? $_POST['name'] : 'miky';
		$this->assign('name', $name);
		$this->show();
	}
}
class Add extends Action {
	function execute() {
		$this->assign('title', "你好");
		$this->show('Add');
	}
}