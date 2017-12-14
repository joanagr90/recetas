<?php
/*
El listener RecetasListener recibe el evento y  lo procesa para generar un email. Aunque se pueda utilizar la 
clase gen�rica Symfony\Component\EventDispatcher\Event para algunos casos, siempre que se necesite pasar
informaci�n a los listener se tendr� que crear una propia clase.
*/


namespace RecetasBundle\Entity\Receta;

class RecetasListener
{
    private $mailer;

    public function __construct(\SwiftMailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function onRecetaCreada(RecetaEvent $event)
    {
        $receta = $event->getReceta();
        $this->notifyToAdmins($receta);
    }

    private function notifyToAdmins(Receta $receta)
    {
        $this->mailer->send($email);

        //eventos en cadena
        $event->getDispatcher->dispatch('email.sent', new EmailEvent($email));
    }

}

?>