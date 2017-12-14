<?php
// TEMA - 8
namespace RecetasBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class SecurityController extends Controller
{
    public function loginAction(Request $request)
    {
        $authenticaionUtils = $this->get('security.authentication_utils');
        $error = $authenticaionUtils->getLastAuthenticationError();
        $lastUsername = $authenticaionUtils->getLastUsername();

        return array(
            'last_username' => $lastUsername,
            'error' => $error
        );
    }
}