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
	private $authKey;

/**
	*	@ORM\Column(type="integer")
	*/
	private $imgId;

	public function getUid(): ?int {
			
		return $this->uid;
	}
	public function getAuthKey(): ?string {
			 
		return $this->authKey;
	}
	public function setAuthKey(string $authKey): self {
		$this->authKey = $authKey;
		return $this;
	}
	public function setImgId(int $imgId): self {
		$this->imgId = $imgId;
		return $this;
	}

	public function __construct(int $id) {
		// Use this constructor only to set $id value when dealing with an sql array on custom findBy functions
		$this->id = $id;
	}

}
