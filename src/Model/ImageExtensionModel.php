<?php
namespace App\Model; // Entitty?

class ImageExtensionModel {
	private $extension;

	public function getExtension() {
		return $this->extension;
	}
	public function setExtension(string $filename) {
		$this->extension = $filename;
		return $this;
	}
	
}