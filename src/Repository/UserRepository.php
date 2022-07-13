<?php
namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, User::class);
	}
	
	public function findByAuthKey(string $authKey) : array {
		$entMan = $this->getEntityManager();
		// Documentation suggests DQL requires a path to a scope;
		// It actually 
		$query = $entMan->createQuery(
			'SELECT u FROM user_entity u WHERE u.authKey = :authKey '
		);
		$query->setParameter('authKey', $authKey);
		// An exception occurred while executing a query: SQLSTATE[HY000]: General error: 1 no such table: user
		return $query->getResult();
	}

	// isUnique() if it is unique == Invalid key/or this key can be inserted
	public function isUnique(string $authKey): bool {
		if($this->findByAuthKey($authKey)->count() != 0) {
			return false;
		}
		return true;
	}


}