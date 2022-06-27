<?php
namespace App\Model; // Entitty?

class Image {
	private $imageFilename;

	public function getImageFilename() {
		return $this->imageFilename;
	}
	public function setImageFilename($filename) {
		$this->imageFilename = $filename;
		return $this;
	}
	
}