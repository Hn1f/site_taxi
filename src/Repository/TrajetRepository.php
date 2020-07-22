<?php

namespace App\Repository;

use DateTimeZone;
use App\Entity\Trajet;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Trajet|null find($id, $lockMode = null, $lockVersion = null)
 * @method Trajet|null findOneBy(array $criteria, array $orderBy = null)
 * @method Trajet[]    findAll()
 * @method Trajet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrajetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trajet::class);
    }

    // /**
    //  * @return Trajet[] Returns an array of Trajet objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    public function TrajetenCour($id){
        $conn = $this->getEntityManager()->getConnection(); 
        $sql='
            SELECT * FROM trajet
            WHERE TIMEDIFF(hour,NOW()) > "01:00" AND hour > NOW() AND user_id= 25 ';
        $stmt = $conn->prepare($sql); 
        // $stmt=bind_param("i",$id);
        $stmt->execute();
        return $stmt->fetchAll();    
    }

    /*
    public function findOneBySomeField($value): ?Trajet
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
