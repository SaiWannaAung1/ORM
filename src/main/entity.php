<?php
class Entity {

	function __construct() {

	}
	public function save() {
		$query = new Query();
		$query->save($this);
	}
	public function delete() {
		$query = new Query();
		$query->delete($this);
	}
	public function getProperties() {
		return get_object_vars($this);
	}
}
