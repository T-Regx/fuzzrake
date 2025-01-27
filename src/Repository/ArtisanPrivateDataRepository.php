<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ArtisanPrivateData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ArtisanPrivateData|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArtisanPrivateData|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArtisanPrivateData[]    findAll()
 * @method ArtisanPrivateData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<ArtisanPrivateData>
 */
class ArtisanPrivateDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArtisanPrivateData::class);
    }
}
