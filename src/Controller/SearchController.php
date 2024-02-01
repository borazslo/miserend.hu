<?php

namespace App\Controller;

use App\Form\Types\ChurchMassSearchType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SearchController extends AbstractController
{
    public function search(Request $request): Response
    {
        $form = $this->createForm(ChurchMassSearchType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            exit;
        }

        return $this->render('search/church_mass.html.twig', ['form' => $form->createView()]);
    }
}
