<?php
namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class UserEntity {
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

	public function getId(): ?int {
			
		return $this->id;
	}
	public function getAuthKey(): ?string {
			 
		return $this->authKey;
	}
	public function setAuthKey(string $authKey): self {
		$this->authKey = $authKey;
		return $this;
	}

	public function __construct(int $id) {
		// Use this constructor only to set $id value when dealing with an sql array on custom findBy functions
		$this->id = $id;
	}

}
