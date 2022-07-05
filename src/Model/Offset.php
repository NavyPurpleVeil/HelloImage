<?php
namespace App\Model; // Entitty?

class Offset {
	private $offset;

	public function getOffset() {
		return $this->offset;
	}
	public function setOffset($offset) {
		$this->offset = $offset;
		return $this;
	}
	
}