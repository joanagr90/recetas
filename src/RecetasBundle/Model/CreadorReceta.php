<?php
//TEMA-7--Eventos
// src/RecetasBundle/Model/CreadorReceta.php 

namespace RecetasBundle\Model;

use Doctrine\Common\Persistence\ObjectManager;
use RecetasBundle\Entity\Receta;

//Servicio encargado de crear una nueva receta
class CreadorReceta
{
    private $om;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function create(Receta $receta)
    {   
        //Guardar la receta en la bbdd
        $this->om->persist($receta);
        $this->om->flush();

        //Enviar un aviso por mail al administrador de la web cada vez que se publica la receta.
        $this->systemMailer->sendRecetaInfo($receta);

        //Registrar la operación en un log
        $this->systemLogger->log('ifno', \sprintf('Nueva receta creada con nombre %s', $receta->getName()));
    }
}

?>