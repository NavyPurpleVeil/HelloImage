<?php
namespace App\Repository;

use App\Entity\Image;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ImageRepository extends ServiceEntityRepository {
	public function __construct(ManagerRegistry $registry) {
			parent::__construct($registry, Image::class);
	}

	public function findByAuthKey(string $authKey) : array {
		$entMan = $this->getEntityManager();

		$query = $entMan->createQuery(
			'SELECT u FROM App\Entity\User u RIGHT OUTER JOIN  App\Entity\Image i ON u.uid = i.uid WHERE u.authkey = :authKey;'
		);
		$query->setParameter('authKey', $authKey);
		return $query->getResult();
	}
	public function findByAuthKeyId(int $id, string $authKey) : array {
		$entMan = $this->getEntityManager();

		$query = $entMan->createQuery(
			'SELECT u FROM App\Entity\User u RIGHT OUTER JOIN  App\Entity\Image i ON u.uid = i.uid WHERE u.authkey = :authKey && i.id = :id;'
		);
		$query->setParameter('authKey', $authKey);
		$query->setParameter('id', $id);
		return $query->getResult();
	}
	public function removeByAuthKeyId(int $id, string $authKey) : array {
		$entMan = $this->getEntityManager();

		$query = $entMan->createQuery(
			'DELETE u FROM App\Entity\Image i LEFT OUTER JOIN  App\Entity\User u ON u.uid = i.uid WHERE u.authkey = :authKey && i.id = :id;'
		);
		$query->setParameter('authKey', $authKey);
		$query->setParameter('id', $id);
		return $query->getResult();
	}

	public function getRatingCount(int $id) : array {
		$entMan = $this->getEntityManager();
		$query = $entMan->createQuery(
			'SELECT i FROM App\Entity\Image i where i.id= :id;'
		);
		$query->setParameter('id', $id);
		return $query->getResult();
	}
	public function getAllRatingCounts(string $authKey, int $offset) : array {
		$entMan = $this->getEntityManager();
		$query = $entMan->createQuery(
			'SELECT i FROM App\Entity\Image i where i.id = :id OFFSET :offset LIMIT 50 ;'
		);
		$query->setParameter('id', $id);
		$query->setParameter('offset', $offset);
		return $query->getResult();
	}
	public function incrementVoteCount(int $id) : array {
		$entMan = $this->getEntityManager();
		$query = $entMan->createQuery(
			'UPDATE App\Entity\Image i SET i.voteCount = i.voteCount + 1;'
		);
		$query->setParameter('id', $id);
		return $query->getResult();
	}
	public function decrementVoteCount(int $id) : array {
		$entMan = $this->getEntityManager();
		$query = $entMan->createQuery(
			'UPDATE App\Entity\Image i SET i.voteCount = i.voteCount - 1;'
		);
		$query->setParameter('id', $id);
		return $query->getResult();		
	}

}