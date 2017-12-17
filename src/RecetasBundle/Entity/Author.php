<?php
//src/RecetasBundle/Entity/Author.php

namespace RecetasBundle\Entity;

class Author
{
    private $id;
    //protected $name;
    protected $surname;

    #tema 9 - Traducionedo mensajes de validación
    public $name;

    #constructores para una configuración más amplia y añadir métodos para añadir ingredientes
    public function __construct($name, $surname)
    {
        $this->name = $name;
        $this->surname = $surname;
        $this->recetas = new ArrayCollection;
    }
    #para que author contenga una referencia a sus recetas
    public function getRecipes()
    {
        return $this->recetas;
    }
}



?>