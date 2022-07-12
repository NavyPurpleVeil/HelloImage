<?php
namespace App\Repository;

use App\Entity\Rating;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RatingRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, Image::class);
	}
	
	public function findByUid(int $uid, int $id) : array {
		$entityManager = $this->getEntityManager();

		$query = $entityManager->createQuery(
			'SELECT u FROM App\Entity\Rating u WHERE u.uid = :uid && u.imgId = :id'
		);
		$query->setParameter('uid', $uid);
		$query->setParameter('id', $id);
		return $query->getResult();
	}

	public function removeByUid(int $uid, int $id) : array {
		$entityManager = $this->getEntityManager();

		$query = $entityManager->createQuery(
			'DELETE u FROM App\Entity\Rating r WHERE r.uid = :uid && i.id = :id'
		);
		$query->setParameter('uid', $uid);
		$query->setParameter('id', $id);
		return $query->getResult();
	}


}