<?php

namespace App\Controller;

use App\Entity\Pays;
use App\Entity\Banques;
use App\Entity\Materiels;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;


class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(PersistenceManagerRegistry $doctrine, Request $request): Response
    {
        $em = $doctrine->getManager();

        $banques = $em->getRepository(Banques::class)->findAll();

        $materiels = $em->getRepository(Materiels::class)->findAll();

        $countrys = $em->getRepository(Pays::class)->findAll(); 


        return $this->render('home/accueil.html.twig', [
            'controller_name' => 'HomeController',
            'banques' => $banques,
            'materiels' => $materiels,
            'countrys' => $countrys,

        ]);
    }

    #[Route('/about', name: 'app_about')]
    public function about(PersistenceManagerRegistry $doctrine,): Response
    {
        $em = $doctrine->getManager();
        $banques = $em->getRepository(Banques::class)->findAll();

        return $this->render('home/about.html.twig', [
            'controller_name' => 'HomeController',
            'banques' => $banques,
        ]);
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(): Response
    {
        return $this->render('home/contact.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/securite/cheques', name: 'app_securite_cheques')]
    public function cheques(): Response
    {
        return $this->render('home/securite/cheques.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/securite/document', name: 'app_securite_document')]
    public function document(): Response
    {
        return $this->render('home/securite/document.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }


    #[Route('/travaux/solution', name: 'app_travaux_solution')]
    public function solution(): Response
    {
        return $this->render('home/travaux/solution.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }


    #[Route('/travaux/perso', name: 'app_travaux_perso')]
    public function personn(): Response
    {
        return $this->render('home/travaux/personnalisation.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }






}
