<?php

namespace RecetasBundle\Event;

use RecetasBundle\Entity\Receta;

class eventoReceta
{
    private $receta;

    public function __construct(Receta $receta)
    {
        $this->receta = $receta;
    }
}



?>


