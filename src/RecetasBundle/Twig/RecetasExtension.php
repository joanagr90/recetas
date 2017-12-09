<!--para encapsular el código en las clases reusables-->$_COOKIE

<?php
namespace RecetasBundle;
use RecetasBundle;

class RecetasExtension extends Extension{
    public function getFilters(){
        return array(new SimpleFilter('cssClass', array($this, 'cssClass')),);
    }

    public function cssClass($receta)
    {
        if($receta->isEasy())
        {
            return 'easy';
        }
        if($receta->isNormal())
        {
            return 'normal';
        }
        if($receta->isHard())
        {
            return 'hard';
        }
        return 'unknown';
    }

    public function getName()
    {
        return 'recetas_extension';
    }

}
?>