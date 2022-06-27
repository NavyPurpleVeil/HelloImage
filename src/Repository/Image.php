<?php
namespace App\Repository;

use App\Entity\ImageEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ImageRepository extends ServiceEntityRepository
{
		public function __construct(ManagerRegistry $registry)
		{
				parent::__construct($registry, ImageEntity::class);
		}


}