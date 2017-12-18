<?php
namespace RecetasBundle\Tests\Utility;

use RecetasBundle\Utility\calcularingredientes;

class CalcularIngredientesTest extends \PHPUnit_Framework_TestCase
{
    public function testCalcularIngredientesPersonas()
    {
        $calc = new CalcularIngredientes();
        $result = $calc->testCalcularIngredientesPersonas(3, 40);

        //Se comprueba que la calculadora de ingredientes lo hace correctamente
        $this->assertEquals(120, $result);
    }
}


