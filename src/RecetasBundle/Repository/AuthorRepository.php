<?php
namespace RecetasBundle\Repository;

use Doctrine\ORM\EntityRepository;

class AuthorRepository extends EntityRepository{

    public function findTopChefs(){
        return $this->getEntityManager()
        ->createQuery('SELECT a
                       FROM RecetasBundle:Author a
                       JOIN a.recetas r
                       WHERE r.difficulty = :difficulty')
        ->setParameter('difficulty', 'difícil')
        ->getResult();
    }
}
?>