<?php

namespace Admin\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ProduitsController extends Controller
{
    public function ajoutPageAction()
    {
        return $this->render('AdminBundle:Produits:ajout.html.twig');
    }

    public function listePageAction()
    {
        return $this->render('AdminBundle:Produits:listeproduits.html.twig');
    }
}
