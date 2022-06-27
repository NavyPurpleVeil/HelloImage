<?php
namespace App\Repository;

use App\Entity\UserEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserRepository extends ServiceEntityRepository
{
		public function __construct(ManagerRegistry $registry)
		{
			parent::__construct($registry, UserEntity::class);
		}
		
		// isUnique() if it is unique == Invalid key/or this key can be inserted
		public function isUnique(string authKey): bool {
			$entityManager = $this->getEntityManager();

			$query = $entityManager->createQuery(
				'SELECT authKey FROM App\Entity\UserEntity u WHERE u.authKey = :authKey '
			)->setParameter('authKey', $authKey);
			if($query->getResult()->count() != 0) {
				return false;
			}
			return true;
		}
}