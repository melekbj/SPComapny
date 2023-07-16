<?php

namespace App\Controller;

use App\Entity\Pays;
use App\Entity\Banques;
use App\Entity\Commande;
use App\Entity\Materiels;
use App\Form\BanquesType;
use App\Form\CommandeType;
use App\Form\MaterielType;
use App\Form\PaysFormType;
use App\Form\EditCommandType;
use App\Entity\CommandeMateriels;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;


#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin')]
    public function index(): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();
        
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
            'image' => $image
        ]);
    }

    #[Route('/pays', name: 'app_country')]
    public function pays(PersistenceManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();

        $em = $doctrine->getManager();
        $pays = $em->getRepository(Pays::class)->findAll();

        return $this->render('admin/pays.html.twig', [
            'controller_name' => 'AdminController',
            'image' => $image,
            'etats' => $pays,
            
        ]);
    }


    #[Route('/list_country', name: 'app_list_country')]
    public function listPays(PersistenceManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();

        $em = $doctrine->getManager();
        $pays = $em->getRepository(Pays::class)->findAll();

        $etats = new Pays();
        $form = $this->createForm(PaysFormType::class, $etats);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $entityManager = $doctrine->getManager();
            $entityManager->persist($etats);
            $entityManager->flush();
            $this->addFlash('success', 'Etat ajouté avec succès');
            return $this->redirectToRoute('app_country');
        }

        return $this->render('admin/pays/listPays.html.twig', [
            'controller_name' => 'AdminController',
            'image' => $image,
            'etats' => $pays,
            'etatForm' =>$form->createView(),
        ]);
    }

    #[Route('/delete_country/{id}', name: 'app_delete_country')]
    public function deletePays(PersistenceManagerRegistry $doctrine, Request $request, $id): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();

        $em = $doctrine->getManager();
        $pays = $em->getRepository(Pays::class)->find($id);

        $em->remove($pays);
        $em->flush();
        $this->addFlash('success', 'Etat supprimé avec succès');
        return $this->redirectToRoute('app_list_country');
    }

    #[Route('/edit_country/{id}', name: 'app_edit_country')]
    public function editPays(PersistenceManagerRegistry $doctrine, Request $request, $id): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();

        $em = $doctrine->getManager();
        $pays = $em->getRepository(Pays::class)->find($id);

        $form = $this->createForm(PaysFormType::class, $pays);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $entityManager = $doctrine->getManager();
            $entityManager->persist($pays);
            $entityManager->flush();
            $this->addFlash('success', 'Etat modifié avec succès');
            return $this->redirectToRoute('app_list_country');
        }

        return $this->render('admin/pays/editPays.html.twig', [
            'controller_name' => 'AdminController',
            'image' => $image,
            'editForm' =>$form->createView(),
        ]);
    }


    #[Route('/banques', name: 'app_banks')]
    public function Banques(PersistenceManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();

        $em = $doctrine->getManager();
        $banks = $em->getRepository(Banques::class)->findAll();

        $banques = new Banques();
        $form = $this->createForm(BanquesType::class, $banques);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $entityManager = $doctrine->getManager();
            $entityManager->persist($banques);
            $entityManager->flush();
            $this->addFlash('success', 'Bank ajouté avec succès');
            return $this->redirectToRoute('app_banks');
        }

        return $this->render('admin/banques/banques.html.twig', [
            'controller_name' => 'AdminController',
            'image' => $image,
            'banks' => $banks,
            'bankForm' =>$form->createView(),
        ]);
    }
    

    #[Route('/banks_by_country/{id}', name: 'app_banks_by_country')]
    public function banksByCountry(PersistenceManagerRegistry $doctrine, Request $request, $id): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();

        $em = $doctrine->getManager();
        $banks = $em->getRepository(Banques::class)->findBy(['pays' => $id]);

        return $this->render('admin/banksByCountry.html.twig', [
            'controller_name' => 'AdminController',
            'image' => $image,
            'banks' => $banks,
        ]);
    }


    #[Route('/list_materials', name: 'app_materials')]
    public function materials(PersistenceManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();

        $em = $doctrine->getManager();
        $materials = $em->getRepository(Materiels::class)->findall();

        $banques = new Materiels();
        $form = $this->createForm(MaterielType::class, $banques);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $entityManager = $doctrine->getManager();
            $entityManager->persist($banques);
            $entityManager->flush();
            $this->addFlash('success', 'Materiel ajouté avec succès');
            return $this->redirectToRoute('app_materials');
        }

        return $this->render('admin/materiels/materials.html.twig', [
            'controller_name' => 'AdminController',
            'image' => $image,
            'materials' => $materials,
            'materialForm' =>$form->createView(),
        ]);
    }

    #[Route('/delete_material/{id}', name: 'app_delete_material')]
    public function deleteMaterial(PersistenceManagerRegistry $doctrine, Request $request, $id): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();

        $em = $doctrine->getManager();
        $materiels = $em->getRepository(Materiels::class)->find($id);

        $em->remove($materiels);
        $em->flush();
        $this->addFlash('success', 'Materiel supprimé avec succès');
        return $this->redirectToRoute('app_materials');
    }

    #[Route('/edit_material/{id}', name: 'app_edit_material')]
    public function editMaterial(PersistenceManagerRegistry $doctrine, Request $request, $id): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();

        $em = $doctrine->getManager();
        $pays = $em->getRepository(Materiels::class)->find($id);

        $form = $this->createForm(MaterielType::class, $pays);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $entityManager = $doctrine->getManager();
            $entityManager->persist($pays);
            $entityManager->flush();
            $this->addFlash('success', 'Materiel modifié avec succès');
            return $this->redirectToRoute('app_materials');
        }

        return $this->render('admin/materiels/editMaterial.html.twig', [
            'controller_name' => 'AdminController',
            'image' => $image,
            'editForm' =>$form->createView(),
        ]);
    }



    #[Route('/delete_banque/{id}', name: 'app_delete_banque')]
    public function deleteBanque(PersistenceManagerRegistry $doctrine, Request $request, $id): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();

        $em = $doctrine->getManager();
        $banques = $em->getRepository(Banques::class)->find($id);

        $em->remove($banques);
        $em->flush();
        $this->addFlash('success', 'Banque supprimé avec succès');
        return $this->redirectToRoute('app_banks');
    }

    #[Route('/edit_banque/{id}', name: 'app_edit_banque')]
    public function editBanque(PersistenceManagerRegistry $doctrine, Request $request, $id): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();

        $em = $doctrine->getManager();
        $banques = $em->getRepository(Banques::class)->find($id);

        $form = $this->createForm(BanquesType::class, $banques);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $entityManager = $doctrine->getManager();
            $entityManager->persist($banques);
            $entityManager->flush();
            $this->addFlash('success', 'Banque modifié avec succès');
            return $this->redirectToRoute('app_banks');
        }

        return $this->render('admin/banques/editBanque.html.twig', [
            'controller_name' => 'AdminController',
            'image' => $image,
            'editForm' =>$form->createView(),
        ]);
    }

    


    //gestion des commandes

    // #[Route('/commandes', name: 'app_commandes')]
    // public function addCommandes(PersistenceManagerRegistry $doctrine, Request $request): Response
    // {
    //     $user = $this->getUser();
    //     $image = $user->getImage();

    //     $em = $doctrine->getManager();
    //     $commande = $em->getRepository(Commande::class)->findAll();

    //     $commandes = new Commande();
    //     $form = $this->createForm(CommandeType::class, $commandes);
    //     $form->handleRequest($request);

    //     if($form->isSubmitted() && $form->isValid()){
    //         $entityManager = $doctrine->getManager();
    //         $entityManager->persist($commandes);
    //         $entityManager->flush();
    //         $this->addFlash('success', 'Commande ajouté avec succès');
    //         return $this->redirectToRoute('app_commandes');
    //     }

    //     return $this->render('admin/commandes/commande.html.twig', [
    //         'controller_name' => 'AdminController',
    //         'image' => $image,
    //         'commandes' => $commande,
    //         'commandForm' =>$form->createView(),
    //     ]);
    // }


    #[Route('/commandes', name: 'app_commandes')]
    public function addCommandes(PersistenceManagerRegistry $doctrine, Request $request): Response
    {
        // Get the user and image information
        $user = $this->getUser();
        $image = $user->getImage();

        // Get the Doctrine entity manager
        $em = $doctrine->getManager();

        $commande = $em->getRepository(Commande::class)->findall();

        // Retrieve the list of banques from the database
        $banques = $em->getRepository(Banques::class)->findAll();
        
        // Retrieve the list of materiels from the database
        $materiels = $em->getRepository(Materiels::class)->findAll();
        

        if ($request->isMethod('POST')) {
            // Get the submitted data
            $description = $request->request->get('description');
            $tauxtva = $request->request->get('tauxtva');
            $avance = $request->request->get('avance');
            $ref = $request->request->get('ref');
            $dateString = $request->request->get('date');
            $date = \DateTime::createFromFormat('Y-m-d', $dateString); // Assuming the date format is "YYYY-MM-DD"
            $banqueId = $request->request->get('banque');
            $materielIds = $request->request->all('materiel');
            


            // Retrieve the selected Banque entity
            $banque = $em->getRepository(Banques::class)->find($banqueId);
        

            // Create a new Commande instance
            $commande = new Commande();
            $commande->setDescription($description);
            $commande->setTauxTVA($tauxtva);
            $commande->setAvance($avance);
            $commande->setBanque($banque);
            $commande->setDate($date);  
            $commande->setRef($ref);


            // Iterate over the selected Materiel IDs and create CommandMaterial entities
            foreach ($materielIds as $materielId) {
                $materiel = $em->getRepository(Materiels::class)->find($materielId);
                $quantity = $request->request->get('textarea'.$materielId);
                // Create a new CommandMaterial instance
                $commandMaterial = new CommandeMateriels();
                $commandMaterial->setCommande($commande);
                $commandMaterial->setMateriel($materiel);
                $commandMaterial->setQuantite($quantity);

                // Persist the CommandMaterial entity
                $em->persist($commandMaterial);
            }

            // Persist the Commande entity
            $em->persist($commande);
            $em->flush();

            // Redirect
            $this->addFlash('success', 'Commande ajouté avec succès');
            return $this->redirectToRoute('app_commandes');

            
        }

        return $this->render('admin/commandes/commande.html.twig', [
            'controller_name' => 'AdminController',
            'image' => $image,
            'materiels' => $materiels,
            'banques' => $banques,
            'commandes' => $commande,
        ]);
    }







    #[Route('/edit_commande/{id}', name: 'app_edit_commande')]
    public function editCommande(int $id, PersistenceManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
            $image = $user->getImage();

        // Get the Doctrine entity manager
        $em = $doctrine->getManager();

        // Retrieve the Commande entity by ID
        $commande = $em->getRepository(Commande::class)->find($id);

        // Check if the Commande exists
        if (!$commande) {
            throw $this->createNotFoundException('Commande not found');
        }

        // Retrieve the list of banques from the database
        $banques = $em->getRepository(Banques::class)->findAll();

        // Retrieve the list of materiels from the database
        $materiels = $em->getRepository(Materiels::class)->findAll();

        if ($request->isMethod('POST')) {
            // Get the submitted data
            $description = $request->request->get('description');
            $tauxtva = $request->request->get('tauxtva');
            $avance = $request->request->get('avance');
            $ref = $request->request->get('ref');
            $dateString = $request->request->get('date');
            $date = \DateTime::createFromFormat('Y-m-d', $dateString); // Assuming the date format is "YYYY-MM-DD"
            $banqueId = $request->request->get('banque');
            $materielIds = $request->request->all('materiel');

            // Retrieve the selected Banque entity
            $banque = $em->getRepository(Banques::class)->find($banqueId);

            // Update the Commande entity with the new values
            $commande->setDescription($description);
            $commande->setTauxTVA($tauxtva);
            $commande->setAvance($avance);
            $commande->setBanque($banque);
            $commande->setDate($date);
            $commande->setRef($ref);

            // Clear existing CommandeMateriels associations
            $commande->getCommandeMateriels()->clear();

            // Iterate over the selected Materiel IDs and create CommandMaterial entities
            foreach ($materielIds as $materielId) {
                $materiel = $em->getRepository(Materiels::class)->find($materielId);
                $quantity = $request->request->get('textarea' . $materielId);

                // Create a new CommandMaterial instance
                $commandMaterial = new CommandeMateriels();
                $commandMaterial->setCommande($commande);
                $commandMaterial->setMateriel($materiel);
                $commandMaterial->setQuantite($quantity);

                // Persist the CommandMaterial entity
                $em->persist($commandMaterial);
            }

            // Flush the changes to the database
            $em->flush();

            // Redirect
            $this->addFlash('success', 'Commande mise à jour avec succès');
            return $this->redirectToRoute('app_commandes');
        }

        return $this->render('admin/commandes/editCommande.html.twig', [
            'controller_name' => 'AdminController',
            'commande' => $commande,
            'materiels' => $materiels,
            'banques' => $banques,
            'image' => $image,
        ]);
    }
    // public function editCommande(PersistenceManagerRegistry $doctrine, Request $request, $id): Response
    // {
    //     $user = $this->getUser();
    //     $image = $user->getImage();

    //     $em = $doctrine->getManager();
    //     $commands = $em->getRepository(Commande::class)->find($id);

    //     $form = $this->createForm(EditCommandType::class, $commands);
    //     $form->handleRequest($request);

    //     if($form->isSubmitted() && $form->isValid()){
    //         $entityManager = $doctrine->getManager();
    //         $entityManager->persist($commands);
    //         $entityManager->flush();
    //         $this->addFlash('success', 'Commande modifié avec succès');
    //         return $this->redirectToRoute('app_commandes');
    //     }

    //     return $this->render('admin/commandes/editCommande.html.twig', [
    //         'controller_name' => 'AdminController',
    //         'image' => $image,
    //         'editForm' =>$form->createView(),
    //     ]);
    // }


    #[Route('/delete_commande/{id}', name: 'app_delete_commande')]
    public function deleteCommande(PersistenceManagerRegistry $doctrine, Request $request, $id): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();

        $em = $doctrine->getManager();
        $Commandes = $em->getRepository(Commande::class)->find($id);

        $em->remove($Commandes);
        $em->flush();
        $this->addFlash('success', 'Commande supprimé avec succès');
        return $this->redirectToRoute('app_commandes');
    }


    // add a route app_commands_by_bank
    #[Route('/commandes_by_bank/{id}', name: 'app_commandes_by_bank')]
    public function CommandesByBank(PersistenceManagerRegistry $doctrine, Request $request, $id): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();

        $em = $doctrine->getManager();

        $etat = $request->query->get('etat'); // Retrieve the "etat" value from the query parameters

        $commandeRepository = $em->getRepository(Commande::class);
        
        // Perform the filtering based on the selected "etat" value and bank ID
        if ($etat) {
            $commande = $commandeRepository->createQueryBuilder('c')
                ->where('c.banque = :id')
                ->andWhere('c.etat = :etat')
                ->setParameters(['id' => $id, 'etat' => $etat])
                ->getQuery()
                ->getResult();
        } else {
            $commande = $commandeRepository->findBy(['banque' => $id]);
        }

       

        return $this->render('admin/commandByBank.html.twig', [
            'controller_name' => 'AdminController',
            'image' => $image,
            'commandes' => $commande,
            'Id' => $id,
        ]);
    }


    #[Route("/set-etat/{id}/{etat}", name: 'app_set_etat')]
    public function setEtat($id, $etat,PersistenceManagerRegistry $doctrine)
    {
        // Handle the logic to set the "etat" value for the given command ID
        
        // Assuming you have an Entity class for the command, you can fetch the entity
        // and update its "etat" property based on the provided value.
        $entityManager = $doctrine->getManager();
        $command = $entityManager->getRepository(Commande::class)->find($id);
        
        if (!$command) {
            throw $this->createNotFoundException('Command not found.');
        }
        
        // Set the new "etat" value
        $command->setEtat($etat);
        
        // Persist the changes to the database
        $entityManager->flush();
        
        // Optionally, you can add a flash message to indicate successful update
        
        // Redirect the user to a relevant page (e.g., the command details page)
        return $this->redirectToRoute('app_commandes');
    }

    // #[Route("/filter", name: 'app_filter')]
    // public function filterCommandes(Request $request,PersistenceManagerRegistry $doctrine): Response
    // {
    //     $user = $this->getUser();
    //     $image = $user->getImage();
    //     $etat = $request->query->get('etat');
        
    //     // Perform the filtering based on the selected "etat" value
    //     $entityManager = $doctrine->getManager();
    //     $commandes = $entityManager->getRepository(Commande::class)->findByEtat($etat);
        
    //     // Pass the filtered results to the template for rendering
    //     return $this->render('admin/commandByBank.html.twig', [
    //         'commandes' => $commandes,
    //         'image' => $image,
    //     ]);
    // }

    #[Route('/commande/{id}', name: 'app_show_commande')]

     public function showCommande($id, PersistenceManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();
        // Retrieve the command details based on the provided ID
        $commandeMateriels = $doctrine->getRepository(CommandeMateriels::class)->find($id);

        if (!$commandeMateriels) {
            throw $this->createNotFoundException('CommandeMateriels not found');
        }

        // Render the template and pass the command details
        return $this->render('admin/detailCommande.html.twig', [
            'commandeMateriels' => $commandeMateriels,
            'image' => $image,
        ]);
    }







}
