<?php
namespace App\Repository;

use App\Entity\ImageEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ImageRepository extends ServiceEntityRepository {
	public function __construct(ManagerRegistry $registry) {
			parent::__construct($registry, ImageEntity::class);
	}

	public function findByAuthKey(string $authKey) : array {
		$entMan = $this->getEntityManager();

		$query = $entMan->createQuery(
			'SELECT u FROM App\Entity\UserEntity u RIGHT OUTER JOIN  App\Entity\ImageEntity i ON u.uid = i.uid WHERE u.authkey = :authKey'
		);
		$query->setParameter('authKey', $authKey);
		return $query->getResult();
	}
	public function findByAuthKeyId(int $id, string $authKey) : array {
		$entMan = $this->getEntityManager();

		$query = $entMan->createQuery(
			'SELECT u FROM App\Entity\UserEntity u RIGHT OUTER JOIN  App\Entity\ImageEntity i ON u.uid = i.uid WHERE u.authkey = :authKey && i.id = :id'
		);
		$query->setParameter('authKey', $authKey);
		$query->setParameter('id', $id);
		return $query->getResult();
	}
	public function removeByAuthKeyId(int $id, string $authKey) : array {
		$entMan = $this->getEntityManager();

		$query = $entMan->createQuery(
			'DELETE u FROM App\Entity\ImageEntity i LEFT OUTER JOIN  App\Entity\UserEntity u ON u.uid = i.uid WHERE u.authkey = :authKey && i.id = :id'
		);
		$query->setParameter('authKey', $authKey);
		$query->setParameter('id', $id);
		return $query->getResult();
	}

	public function getRatingCount(int $id) : array {
		$entMan = $this->getEntityManager();
		$query = $entMan->createQuery(
			'SELECT i FROM App\Entity\ImageEntity i where i.id= :id'
		);
		$query->setParameter('id', $id);
		return $query->getResult(); // what is being returned is an array, how to access the data
	}
}