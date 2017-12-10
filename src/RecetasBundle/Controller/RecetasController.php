<?php

namespace RecetasBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
#tema 3
use RecetasBundle\Entity\Receta;
use Symfony\Component\HttpFoundation\Response;
#tema 5
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use RecetasBundle\Entity\Author;
#para controlar la protección CSRF, generar id único en cada formulario
use Symfony\Component\OptionsResolver\OptionsResolver;
#FieldTypes
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use RecetasBundle\Model\Difficulties;
#EventSubscriber
use RecetasBundle\Form\EventListener\AddNotesFieldSubscriber;

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
        return array('ultimas_recetas' => $this.getUltimasRecetaS(), 'chefs' => $chefs);

    }

    //TEMA 4
    private function getUltimasRecetas()
    {
        $date = new \DateTime('-10 days');

        $repository = $this->getDoctrine()->getRepository('RecetasBundle:Receta');
        
        $recetas = $repository->findPublishedAfter($date);
    
        return array('recetas' => $recetas);
    }

    #tema 5
    public function editAction(Receta $receta, Request $request)
    {
        $form = $this->createForm(RecipeType::class, $receta);

        $form->handleRequest($request);

        if($form->isSubmitted() and $form->isValid())
        {
            $this->getDoctrine()->getManager()->flush();
            $this->redirectToRoute('mis_recetas_show', array(
                'id' => $receta->getId()
            ));
        }

        return array(
            'form' => $form->createView(),
            'receta' => $receta
        );
    }

}

  #tema 5
class AuthorController extends Controller
{
    #se añade la lógica necesaria para que el submit funcione
    public function createAction(Request $request)
    {
        $author = new Author();
        $form = $this->CreateForm(AuthorType::class, $author);
        
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid())
        {
            $author = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($author);
            $em->flush();

            return $this->redirectToRoute('mis_recetas_author_show', array('id' => $author->getId()));
        }
        return $this->render('RecetasBundle:Author:create.html.twig', array('form => $form->createView()'));
    }
}

class AuthorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('surname', TextType::class)        
            #por defecto el metodo es POST, pero se puede modificar:
            ->setMethod('PUT')
            ->add('difficulty', ChoiceType::class, array(
                'choices' => Difficulties::toArray())
            );

    }

    public function setName()
    {
        return 'receta';
    }
    public function getName()
    {
        return 'author';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'RecetasBundle\Entity\Author',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'author'
        ));

        $author = new Author('Iñaki', '');
        $validator = $this->get('validator');
        $errors = $validator->validate($author);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'RecetasBundle\Entity\Author',
        ));
    }
}

class DifficultyType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => Difficulties::toArray()
        ));
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}

class RecipeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('difficulty', DifficultyType::class)
            ->add('author', AuthorType::class)
            ->add('enviar', SubmitType::class);

        $builder->addEventSubscriber(new AddNotesFieldSubscriber());
    }

    public function setName()
    {
        return 'receta';
    }
    #propagar la validación al formulario embebido
    public function configureOptions(OptionsResolver $resolver)
    {
        //configuración avanzada
        $resolver->setDefaults(array(
            'data_class' => 'RecetasBundle\Entity\Receta',
            'cascade_validation' => true
        ));
    }
}