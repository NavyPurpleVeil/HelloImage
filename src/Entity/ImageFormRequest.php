<?php
namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ImageRepository::class)
 */
class ImageFormRequest {
/**
	*	@ORM\Column(type="string")
	*/
		private $filename;

		public function getFilename(): ?string {
			return $this->id;
		}

		public function setFilename(string $uid): self {
			$this->uid = $uid;
			return $this;
		}

}