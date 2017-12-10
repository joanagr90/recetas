<?php
// src/RecetasBundle/Entity/Receta.php
namespace RecetasBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;

class Receta{
   private $id;
   protected $name;
   protected $difficulty; 
   protected $description;
   
   #se crea una relación muchos a uno desde Receta a Author
   protected $author;

   #para crear una relación muchos a muchos desde Receta a Ingredient
   protected $ingredients;

   /* public function __construct()
   {
       $this->ingredients = new ArrayCollection();
   } */

   #una configuración con un constructor más amplio, además de añadir un método que permita añadir ingredientes
   public function __construct(Author $author, $name, $description, $difficulty)
   {
       $this->author = $author; 
       $this->name = $name;
       $this->description = $description;
       $this->difficulty = $difficulty;
       $this->ingredients = new ArrayCollection();
   }

   public function add(Ingredient $ingredient)
   {
       $this->ingredients[] = $ingredient;
   }

   public function getAuthor()
   {
       return $this->author;
   }
   public function setAuthor(Author $author)
   {
       $this->author = $author;
       return $this;
   }

   
}

?>