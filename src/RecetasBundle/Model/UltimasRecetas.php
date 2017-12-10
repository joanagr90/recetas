<?php

namespace RecetasBundle\Model;

use Doctrine\Common\Persistence\ObjectManager;

class UltimasRecetas
{
    private $repository;

    public function __contruct(ObjectManager $om)
    {
        $this->repository = $om->getRepository('RecetasBundle:Receta');
    }

    public function findFrom(\DateTime $from_date)
    {
        return $this->repository->findPublishedAfter($from_date);
    }
}




?>