<?php
//src/RecetasBundle/DataFixtures/ORM/cargareceta.php
namespace RecetasBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RecetasBundle\Entity\Author;
use RecetasBundle\Entity\Ingredient;
use RecetasBundle\Entity\Receta;

class cargareceta implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        for ($i = 1; $i<=100; $i++)
        {
            $this->createReceta($manager, $i);
        }
    }

    public function createReceta(ObjectManager $manager, $n)
    {
        $post = new Receta();
        $post->setTitle('Recetas ' . $n);
        $post->SetContent('<p>Este post habla sobre las diferentes recetas y los autores de cada una de ellas, echa un vistazo y diviertete cocinando.</p>');

        for($i = 1; $i<=mt_rand(1, 4); $i++)
        {
            $this->setTag($manager, $post);
        }

        $int = mt_rand(1262055681, 1562055681);

        $string = date("Y-m-d H:i:s", $int);
        $date = new \Date($string);

        $post->setCreatedAt($date);

        $manager->persist($post);
        $manager->flush();
    }

    private function setAuthor(ObjectManager $manager, Author $post)
    {
        $rand = mt_rand(1,20);
        $author = $manager->getRepository('RecetasBundle:Author')->findOneByTitle('Author ' . $rand);
        if(null === $author)
        {
            $author = new Author();
            $author->setTitle('Author ' . $rand);
            $manager->persist($author);
            $manager->flush();
        }

        if(null === $post->getAuthors() || null !== $post->getAuthors() && false === $post->getAuthors()->contains($author))
        {
            $post->addAuthor();
        }
    }
}




?>