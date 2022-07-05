<?php
namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity(repositoryClass=RatingRepository::class)
 */
class RatingEntity {
/**
	* @ORM\Id
	* @ORM\GeneratedValue
	* @ORM\Column(type="integer")
	*/
	private $id;
		
/**
	* @ORM\Column(type="string", length=255)
	*/
	private $uid;

/**
	*	@ORM\Column(type="integer")
	*/
	private $imgId;

	public function getid(): ?int {
			
		return $this->id;
	}
	public function getUid(): ?string {
			 
		return $this->uid;
	}
	public function setUid(string $uid): self {
		$this->uid = $uid;
		return $this;
	}
	public function setImgId(int $imgId): self {
		$this->imgId = $imgId;
		return $this;
	}

}
