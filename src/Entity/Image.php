<?php
namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ImageRepository::class)
 */
class ImageEntity {
/**
	*	@ORM\Id
	*	@ORM\GeneratedValue
	*	@ORM\Column(type="integer")
	*/
		private $id;
/**
	*	@ORM\Column(type="integer")
	*/
		private $uid;
/**
	*	@ORM\Column(type="string", length=255)
	*/
		private $extension;
/**
	*	@ORM\Column(type="integer")
	*/
		private $voteCount;


		public function getId(): ?int {
			return $this->id;
		}
		public function getUid(): ?int {
			return $this->uid;
		}
		public function getExtension(): ?string {
			return $this->extension;
		}
		public function getVoteCount(): ?int {
			return $this->uid;
		}

		public function setUid(int $uid): self {
			$this->uid = $uid;
			return $this;
		}
		public function setExtension(string $extension): self {
			$this->extension = $extension;
			return $this;
		}
		public function setVoteCount(int $voteCount): self {
			$this->coteCount = $voteCount;
			return $this;
		}
	public function __construct(int $id) {
		// Use this constructor only to set $id value when dealing with an sql array on custom findBy functions
		$this->id = $id;
	}

}