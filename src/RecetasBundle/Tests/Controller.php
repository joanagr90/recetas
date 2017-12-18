<?php
namespace RecetasBundle\Tests\Controlerr;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
//envia un formulario (y sube un archivo en el propio formulario)
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient(); //devuelve un cliente
        $crawler = $client->request('GET', '/hola/Joana'); // request() devuelve un objeto tipo CRawler, utiliado para extraer elementos de la respuesta, pinchar enlaces y enviar formularios

        $this->assertTrue($crawler->filter('html:contains("Hola Joana")')->count() > 0);


        //para pinchar sobre un enlace, 
        $link = $crawler->filter('a:contains("Saludo")')->eq(1)->link();
        $crawler = $client->click($link);

        //para enviar un formulario. Se selecciona un botón del formulario, reemplaza algunos valores y envía el formulario.
        $form = $crawler->selectButton('enviar')->form();
        //se sustituyes algunos valores:
        $form['nombre'] = 'Joana';
        $form['form_nombre[subject]'] = " Hola!!";
        //se envía el formulario:
        $crawler = $client->submit($form);

        //comprueba el contenido de la página utilizando CSS:
        $this->assertGreaterThan(0, $crawler->filter('h1')->count());

        //probar el contenido original de la respuesta si se desea comprobar que el contenido tiene algún texto o NO es XML o HTML
        $this->assertRegExp(
            '/Hola Joana/',
            $client->getResponse()->getContent()
        );

        //Asegurarse si existe al menos una etiqueta h2 con la clase subtitulo en CSS
        $this->assertGreaterThan(
            0,
            $crawler->filter('h2.subtitle')->count()
        );
        
        //Asegurarse que hay 4 etiquetas h2 en la página
        $this->assertCount(4, $crawler->filter('h2'));
        
        //Asegurarse que la cabecera 'Content-type' es 'application/json'
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
        
        //Asegurarse que el contenido de la respuesta cumple con una expresión regular:
        $this->assertRegExp('/foo/', $client->getResponse()->getContent());

        //El código de estado de la respuesta es 2xx
        $this->assertTrue($client->getResponse()->isSuccessful());

        //El código de estado de la respuesta es 404
        $this->assertTrue($client->getResponse()->isNotFound());

        //El código de estado es exactamente 200
        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode()
        );

        //La respuesta es una redirección a /demo/contacto/
        $this->assertTrue(
            $client->getResponse()->isRedirect('/demo/contacto')
        );

        //La respuesta es una redirección a cualquier URL
        $this->assertTrue($client->getResponse()->isRedirect());

        //PAra pinchar en los enlaces y para enviar formularios:
        $link = $crawler->selectLink('Ir al sitio...')->link();
        $crawler = $client($link);

        $form = $crawler->selectButton('validar')->form();
        $crawler = $client->submit($form, array('nombre' => 'Joana'));

        //Para simular directamente el envío de formularios o para realizar peticiones complejas:
        //envía un formulario directamente, aunque lo fácil es 'crawler'
        $client->request('POST', '/enviar', array('nombre' => 'Joana'));
        //Enviar directamente una cadena de texto en formato JSON
        $client->request(
            'POST',
            '/enviar',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"nombre":"Joana"}'
        );
        $foto = new UploadedFile(
            '/path/to/foto.jpg',
            'foto.jpg',
            'image/jpeg',
            123
        );
        $client->request(
            'POST',
            '/enviar',
            array('nombre' => 'Joana'),
            array('foto' => $foto)
        );
        //Realiza una petición DELETE y establece varias cabeceras HTTP:
        $client->request(
        'DELETE',
        '/post/12',
        array(),
        array(),
        array('PHP_AUTH_USER' => 'username', 'PHP_AUTH_PW' => 'password')
        );

        //Cada petición se puede ejecutar en su propio proceso PHP independiente para evitar efectos secudanrios cuando se trabaja con el varios clientes en el mismo test:
        $client->insulate();

        //El cliente soporta la mayoría de operaciones que se pueden hacer en un navegador real:
        $client->back();
        $client->forward();
        $client->reload();
        //Limpia todas las cookies y el historial
        $client->restart();

        //para acceder al historial o cookies del cliente
        $history = $client->getHistory();
        $cookieJar = $client->getCookieJar();

        //Obtener los objetos relacionados con la última petición
        //obtener la instancia de la petición httpkernel
        $request = $client->getRequest();
        //obtener la instancia de la petición BrowserKit
        $request = $client->getInternalRequest();
        //obtener la instancia de la respuesta HTTPKernel
        $response = $client->getResponse();
        //obtener la instancia de la respuesta BrowserKit
        $response = $client->getInternalResponse();
        $crawler = $client->getCrawler();

        //Cuando las peticiones no son aisladas (no se usa $client->insulate()) se puede acceder al contenedor de inyeccion de dependencias o al Kernel
        $container = $client->getContainer();
        $kernel = $client->getKernel();

        //Para obtener el profiler de la última petición
        //Se activa el profiler para la próxima petición
        $client->enableProfiler();
        $crawler = $client->request('GET', '/profiler');
        //obtiene el profiler
        $profile = $client->getProfile();

        //para forzar la redirección de la respuesta recibida
        $crawler = $client->followRedirect();
        //para que el cliente siga todas las redirecciones automáticamente
        $client->followRedirects();

        //CRAWLER - Recorrer documentos HTML y XML
        //encontrar todos los elementos imput type submit, selecciona el último en la página y su elemento padre:
        $newCrawler = $crawler->filter('input[type=submit]')
            ->last()
            ->parents()
            ->first();
        //para seleccionar nodos encadenando varios elementos
        $crawler
            ->filter('h1')
            ->reduce(function($node, $i)
            {
                if(!$node->getAttribute('class'))
                {
                    return false;
                }
            })
            ->first();
        
        //Extraer información de los nodos:
        //Devuelve el valor del atributo del primer nodo
        $crawler->attr('class');
        //Devuelve el valor del nodo para el primer nodo
        $crawler->text();
        //Extrae un array de atributos de todos los nodos: _text devuelve el valor del nodo, devuelve un array de cada elemento en crawler y cada uno con su valor y href
        $info = $crawler->extract(array('_text', 'href'));
        //Ejecuta una función anónima por cada nodo y devuelve un array de resultados
        $data = $crawler->each(function($node, $id)
        {
            return $node->attr('href');
        });
        //para seleccionar un enlace:
        $crawler->selectLink('Click aquí');
        //acceder a un objeto especial tipo Link que contiene métodos útiles y específicos para enlaces.
        $link = $crawler->selectLink('Click aquí')->link();
        $client->click($link);
        
        // para seleccionar directamente los formularios:
        $buttonCrawlerNode = $crawler->selectButton('enviar');

        //para obtener el formulario que contiene al submit
        $form = $buttonCrawlerNode->form();
        //para reemplazar a los valores originales del formulario
        $form = $buttonCrawlerNode->form(array(
            'nombre' => 'Joana',
            'my_form[subject]' => 'El curso mola!!',
        ));
        //simular un método HTTP específico al formulario
        $form = $buttonCrawlerNode->form(array(), 'DELETE');
        //eNvíar el formulario pasado el objeto FORM al cliente con el que realiza las peticiones
        $client->submit($form);

        //Rellenar el formulario pasando un array de valores
        $client->submit($form, array(
            'nombre' => 'Joana',
            'my_form[subject]' => 'El curso mola!!',
        ));

        //utilizar la instancia de Form como array para establecer el valor de cada campo individualmente
        $form['nombre'] = 'Joana';
        $form['my_form[subject]'] = 'El curso mola';

        //Para manipular los valores de los campos de acuerdo a su tipo
        //Seleccionar una opción or radiobutton
        $form['country']->select('France');
        //Marcar un checkbox
        $fomr['curso_gustar']->tick();
        //Cargar un archivo
        $form['foto']->upload('/ruta/a/foto.jpg');


        //Configuración TESTS
        //Para pasar un entorno diferente al entorno test o modificar el valor de la opción debug pasando cada opción al método:
        $client = static::createClient(array(
            'entorno' => 'my_test_env',
            'debug' => false,
        ));

        //Si la aplicación requiere unas determinadas cabeceras HTTP
        $client = static::createClient(array(), array(
            'HTTP_HOST' =>'en.ejemplo.com',
            'HTTP_USER_AGENT' =>'MiSuperBrowser/1.0',
        ));
        //Reemplazar las cabeceras HTTP de cada petición
        $client->request('GET', '/', array(), array(), array(
            'HTTP_HOST' =>'en.ejemplo.com',
            'HTTP_USER_AGENT' =>'MiSuperBrowser/1.0',
        ));
        


    
    }
}