<?php

namespace RecetasBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
#tema 3
use RecetasBundle\Entity\Receta;
use Symfony\Component\HttpFoundation\Response;

class RecetasController extends Controller
{

    public function showAction($id)
    {
        return $this->render('recetas/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);

        return $this->render('RecetasBundle:Recetas:show.html.twig', array('recetas' => $recetas
        ));

        #tema 3

        #recuperar entidades
        $repository = $this->getDoctrine()->getRepository('RecetasBundle:Receta');
        $recipe = $repository->find($id);
        #recuperar el autor de una receta:
        $author = $recipe->getAuthor();

        #el camino inverso:
        $repository = $this->getDoctrine()->getRepository('RecetasBundle:Author');
        $author = $repository->find($id);
        $recipes = $author->getRecipes();

        #para recuperar todos los elementos y todas las instancias de Recipe
        $repository->findAll();

        #especificar algunos criterios de filtrado por dificultar y ordenación:
        $repository->findBy(array('difficulty' => 'easy'));
        $repository->findBy(array(), array('name' => 'DESC'));

        #metaprogramación para permitir algunos métodos más legibles:
        $repository->findByDifficulty('easy'); 
        $repository->findOneByName('Pollo al pil-pil');

        #actualizar entnidades. Se invoca al método flush()
        $recipe = $repository->findOneByName('Pollo al pil-pil');
        $recipe->setName('Pollo al chilindrón');
        $this->getDoctrine()->getManager()->flush();

        #eliminar entididades. Se invoca al método remove()
        $recipe = $repository->find($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($recipe);
        $em->flush();
        

        //carga la colección completa la primera vez que sea accedida con la carga Lazy en una relación Author a Recipe
         $author->getRecipes();

         //un acceso a DQL a través del método createQuery().
         $em = $this->getDoctrine()->getManager();
         $query = $em->createQuery(
             'SELECT a
             FROM RecetasBundle:Author a
             JOIN a.recipes r
             WHERE r.difficulty = :difficulty
             ORDER BY a.surname DESC'
         )->setParameter('difficulty', 'difícil');

         $hardcore_authors = $query->getResult();

         //Realizar consulta a través del QueryBuilder
         $em = $this->getDoctrine()->getManager();
         $repository = $em->getRepository('RecetasBundle:author');
         $query = $repository->createQueryBuilder('a')
                ->innerJoin('a.recipes', 'r')
                ->where('r.difficulty = :difficulty')
                ->orderBy('a.surname', 'DESC')
                ->setParameter('difficulty', 'difícil')
                ->getQuery();
        $hardcore_authors = $query->getResult();

        //LIMIT y OFFSET de DQL, consultas
        $query = $em->createQuery(
            'SELECT r
            FROM RecetasBundle:Receta r
            JOIN r.author.a
            JOIN r.ingredients i'
        )->setFirstResult(100)
         ->setMaxResult(10)
         ->getQuery();

        //Recuperar valores escalares en lugar de objetos. DQL
        $query = $em->createQuery(
            'SELECT MAX(a.id)
            FROM RecetasBundle:Author a'
        )->getQuery();
        $last_id = $query->getSingleScalarResult();

        //Optimización básica de las consultas facilitando la información al hydrator, que se encarga de construir los objectos cargados
        $query = $em->createQuery(
            'SELECT r, a, i
            FROM RecetasBundle:Receta r
            JOIN r.author a
            JOIN r.ingredients i'
        );
        $full_built_recipes = $query->getResult();

        //Otra forma de optimizar recursos utilizando arrays en lugar de entidades completas
        $query = $em->createQuery(
            'SELECT i.id, i.name
            FROM RecetasBUndle:Ingredient i'
        )->getQuery();
        $ingredients = $query->getArrayResult();


    }

    public function createAction()
    {
        $recipe = new Recipe();
        $recipe->setname('Pollo al pil-pil');
        $recipe->setdifficulty('fácil');
        $recipe->setdescription('Una receta de pollo');

        $em = $this->getDoctrine->getManager();
        $em = persist($recipe);
        $em = flush();

        return new Response('Creada receta con id: ' . $recipe->getId());
    
        
        #para crear una receta completa:
        $em = $this->getDoctrine()->getEntityManager();

        $author = new Author('Karlos', 'Arguiñano');
        $ingredient = new Ingredient('Pollo');
        $recipe = new Recipe($author, 'Pollo al pil-pil', 'Deliciosa y económica receta.', 'fácil');
        $recipe->add($ingredient);
        
        $this->persistAndFlush($recipe);

        return $this->redirect($this->generateUrl('my_recipes_show', array('id' => $recipe->getID())));
    
    }

    private function persistAndFlush(Recipe $recipe)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($recipe);
        $em->flush();
    }

    //Nuevo método implementado para que Doctrine sepa que se usa mi repositorio
    public function topChefsAction()
    {
        $repository = $this->getDoctrine()->getRepository('RecetasBundle:Author');
        $chefs = $repository->findTopChefs();
        return array('chefs' => $chefs);
    }

}
