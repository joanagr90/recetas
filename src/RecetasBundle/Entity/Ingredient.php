<?php
// src/RecetasBundle/Entity/Ingredient.php
namespace RecetasBundle\Entity;

class Ingredient
{
    private $id;
    protected $name;

    #constructores para una configuración más amplia y añadir métodos para añadir ingredientes
    public function __construct($name)
    {
        $this->name = $name;
    }
}
?>