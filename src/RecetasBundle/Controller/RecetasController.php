<?php

namespace RecetasBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class RecetasController extends Controller
{

    public function showAction($id)
    {
        return $this->render('recetas/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);

        return $this->render('RecetasBundle:Recetas:show.html.twig', array('recetas' => $recetas
     ));
    }
}
