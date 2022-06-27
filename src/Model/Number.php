<?php 

namespace App\Model;

class Number {
	private $number;
	function __construct()  {
		$this->number = random_int(0, 100);
	}
	public function getNumber() {
		return $this->number;
	}
}