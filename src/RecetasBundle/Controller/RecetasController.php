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
#para controlar la protecciÃ³n CSRF, generar id Ãºnico en cada formulario
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
        $receta = $repository->find($id);
        #recuperar el autor de una receta:
        $author = $receta->getAuthor();

        #el camino inverso:
        $repository = $this->getDoctrine()->getRepository('RecetasBundle:Author');
        $author = $repository->find($id);
        $receta = $author->getRecipes();

        #para recuperar todos los elementos y todas las instancias de Receta
        $repository->findAll();

        #especificar algunos criterios de filtrado por dificultar y ordenaciÃ³n:
        $repository->findBy(array('difficulty' => 'easy'));
        $repository->findBy(array(), array('name' => 'DESC'));

        #metaprogramaciÃ³n para permitir algunos mÃ©todos mÃ¡s legibles:
        $repository->findByDifficulty('easy'); 
        $repository->findOneByName('Pollo al pil-pil');

        #actualizar entnidades. Se invoca al mÃ©todo flush()
        $receta = $repository->findOneByName('Pollo al pil-pil');
        $receta->setName('Pollo al chilindrÃ³n');
        $this->getDoctrine()->getManager()->flush();

        #eliminar entididades. Se invoca al mÃ©todo remove()
        $receta = $repository->find($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($receta);
        $em->flush();
        

        //carga la colecciÃ³n completa la primera vez que sea accedida con la carga Lazy en una relaciÃ³n Author a Receta
         $author->getRecipes();

         //un acceso a DQL a travÃ©s del mÃ©todo createQuery().
         $em = $this->getDoctrine()->getManager();
         $query = $em->createQuery(
             'SELECT a
             FROM RecetasBundle:Author a
             JOIN a.recetas r
             WHERE r.difficulty = :difficulty
             ORDER BY a.surname DESC'
         )->setParameter('difficulty', 'difÃ­cil');

         $hardcore_authors = $query->getResult();

         //Realizar consulta a travÃ©s del QueryBuilder
         $em = $this->getDoctrine()->getManager();
         $repository = $em->getRepository('RecetasBundle:author');
         $query = $repository->createQueryBuilder('a')
                ->innerJoin('a.recetas', 'r')
                ->where('r.difficulty = :difficulty')
                ->orderBy('a.surname', 'DESC')
                ->setParameter('difficulty', 'difÃ­cil')
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

        //OptimizaciÃ³n bÃ¡sica de las consultas facilitando la informaciÃ³n al hydrator, que se encarga de construir los objectos cargados
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
        $receta = new Receta();
        $receta->setname('Pollo al pil-pil');
        $receta->setdifficulty('fÃ¡cil');
        $receta->setdescription('Una receta de pollo');

        $em = $this->getDoctrine->getManager();
        $em = persist($receta);
        $em = flush();

        return new Response('Creada receta con id: ' . $receta->getId());
      
        #para crear una receta completa:
        $em = $this->getDoctrine()->getEntityManager();

        $author = new Author('Karlos', 'ArguiÃ±ano');
        $ingredient = new Ingredient('Pollo');
        $receta = new Receta($author, 'Pollo al pil-pil', 'Deliciosa y econÃ³mica receta.', 'fÃ¡cil');
        $receta->add($ingredient);
        
        $this->persistAndFlush($receta);

        return $this->redirect($this->generateUrl('mis_recetas_show', array('id' => $receta->getID())));

        //tema 7- Eventos
        //Creación nueva receta
        $receta = new Receta();
        $form = $this->createForm(new RecipeType, $receta);
        $form->handleRequest($request);

        if($form->isValid())
        {
            $this->persistAndFlush($receta);
            return $this->redirect($this->generateUrl('mis_recetas_show', array('id' => $receta->getID())));

            /*El controlador es el encargado de disparar el evento, ya que tiene acceso al contenedor de inyección
            de dependencias y que en el contenedor esta registrado el propio dispatcher.*/
            $this->get('event_dispatcher')->dispatch('receta.create', new eventoReceta($receta));
            return $this->redirect($this->generateUrl('mis_recetas_recipe_show', array('id' => $receta->getId())));
        }

        return array('form' => $form->createView());
    }

    private function persistAndFlush(Receta $receta)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($receta);
        $em->flush();
    }

    //Nuevo mÃ©todo implementado para que Doctrine sepa que se usa mi repositorio
    public function topChefsAction()
    {
        $repository = $this->getDoctrine()->getRepository('RecetasBundle:Author');
        $chefs = $repository->findTopChefs();
        return array('ultimas_recetas' => $this.getUltimasRecetaS(), 'chefs' => $chefs);

    }

    //TEMA 4
    #Tema 6-Inyecciones
    #crear servicios para que permita mostrar las Ãºltimas recetas
    private function getUltimasRecetas()
    {
        $date = new \DateTime('-10 days');

        $repository = $this->getDoctrine()->getRepository('RecetasBundle:Receta');
        
        $recetas = $repository->findPublishedAfter($date);
        #se modifica el controlador para permitir mostras las ultimas recetas con una inyeccion de dependencias
        return array('recetas' => $this->get('mis_recetas.ultimas_recetas')->findFrom($date),);
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

    #tema 6 -- Inyecciones
    #se inyecta una dependencia directamente para realizar invocaciones estÃ¡ticas o utilizar variables globales
    function save_account($account_data, $save_callback)
    {
        $success = $save_callback('accounts', $account_data);
        if (!$success)
        {
            $message = sprintf('Ha ocurrido un error guardando la cuenta para %s', $account_data['name']);
            throw new Exception ($message);
        }
    }
    

}

  #tema 5
class AuthorController extends Controller
{
    #se aÃ±ade la lÃ³gica necesaria para que el submit funcione
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

        $author = new Author('IÃ±aki', '');
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
    #propagar la validaciÃ³n al formulario embebido
    public function configureOptions(OptionsResolver $resolver)
    {
        //configuraciÃ³n avanzada
        $resolver->setDefaults(array(
            'data_class' => 'RecetasBundle\Entity\Receta',
            'cascade_validation' => true
        ));
    }
}

class Account
{
    public function save(DatabaseInterface $db)
    {
        $success = $db->insertRow('accounts', $this->toArray());
        if(!$success)
        {
            $message = sprintf('Ha ocurrido un error mientras se guardaba la cuenta para %s', $this->name);
            throw new Exception($message);
        }
    }
}