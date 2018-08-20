<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class HomeController extends Controller
{
    /**
     * @Route("/home", name="newhome")
     */
    public function indexAction()
    {
        return $this->render('home/index.html.twig', array(
            "api_url" => $this->generateUrl("base_api_v1", array(), UrlGeneratorInterface::ABSOLUTE_URL)
        ));
    }

}
