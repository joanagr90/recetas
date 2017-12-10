<?php

namespace RecetasBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSuscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class AddNotesFieldSubscriber implements EventSuscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(FormEvents::PRE_SET_DATa => 'perSetData');
    }

    public function preSetData(FormEvent $event)
    {
        $receta = $event->getData();
        $form = $event->getForm();

        if($receta && $receta->isHard())
        {
            $form->add('notes', TextareaType::class, array(
                'required' => false
            ));
        }
    }
}