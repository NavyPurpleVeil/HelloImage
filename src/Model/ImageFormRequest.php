<?php
namespace App\Model;

use App\Repository\ProductRepository;

class ImageFormRequest {
		private $filename;

		public function getFilename(): ?string {
			return $this->id;
		}

		public function setFilename(string $uid): self {
			$this->uid = $uid;
			return $this;
		}

}