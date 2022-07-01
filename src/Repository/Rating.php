<?php
namespace App\Repository;

use App\Entity\RatingEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RatingRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, RatingEntity::class);
	}
	
	public function findByAuthKey(string $authKey, int $id) : array {
		$entityManager = $this->getEntityManager();

		$query = $entityManager->createQuery(
			'SELECT u FROM App\Entity\RatingEntity u WHERE u.authKey = :authKey '
		)->setParameter('authKey', $authKey);
		return $query->getResult();
	}

	// isUnique() if it is unique == Invalid key/or this key can be inserted
	public function isUnique(string $authKey): bool {
		if($this->findByAuthKey(authKey)->count() != 0) {
			return false;
		}
		return true;
	}


}