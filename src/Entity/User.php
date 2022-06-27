<?php
namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class UserEntity {
    
     #[ORM\Id]
     #[ORM\GeneratedValue]
     #[ORM\Column(type: "integer")]
    private $uid;
    
    $[ORM\Column(type: "string", length: 255)]
    private $authKey;

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

}
