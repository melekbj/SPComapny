<?php

namespace App\Controller;

use DateTime;
use DateInterval;
use App\Entity\Pays;
use App\Entity\User;
use App\Entity\Banques;
use App\Entity\Commande;
use App\Entity\Materiels;
use App\Form\BanquesType;
use App\Form\CommandeType;
use App\Form\EditPaysType;
use App\Form\MaterielType;
use App\Form\PaysFormType;
use App\Form\EditBanqueType;
use App\Form\EditCommandType;
use App\Service\SendMailService;
use App\Entity\CategorieMateriel;
use App\Entity\CommandeMateriels;
use App\Repository\UserRepository;
use App\Form\CategorieMaterielType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;

#[Route('/dashboard')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(PersistenceManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();

        $userRepository = $doctrine->getRepository(User::class);
        $commandRepository = $doctrine->getRepository(Commande::class);

        // Get all pending users
        $pendingUsers = $userRepository->findBy(['etat' => 'pending']);
        $allUsers = $userRepository->findAll();
        
        $totalAllUsers = count($allUsers);
        // Calculate the total number of users and the percentage of pending users
        $totalUsers = count($pendingUsers);
        $totalUsersPercentage = ($totalUsers / count($userRepository->findAll())) * 100;

        // Get total number of commands
        $totalCommands = count($commandRepository->findAll());

        // Calculate the percentage of commands
        $totalCommandsPercentage = ($totalCommands / $totalAllUsers) * 100;

        // Get commands older than 30 days
        $thirtyDaysAgo = new DateTime();
        $thirtyDaysAgo->sub(new DateInterval('P30D'));
        $oldCommands = $commandRepository->createQueryBuilder('c')
            ->where('c.date < :thirtyDaysAgo')
            ->setParameter('thirtyDaysAgo', $thirtyDaysAgo)
            ->getQuery()
            ->getResult();

        // Count the number of old commands
        $oldCommandsCount = count($oldCommands);

        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
            'pendingUsersCount' => $totalUsers,
            'pendingUsersPercentage' => $totalUsersPercentage,
            'totalCommandsCount' => $totalCommands,
            'totalCommandsPercentage' => $totalCommandsPercentage,
            'image' => $image,
            'oldCommandsCount' => $oldCommandsCount,
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

        $form = $this->createForm(EditPaysType::class, $pays);
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

    #[Route('/material_by_category/{id}', name: 'app_material_by_category')]
    public function materialsByCategory(PersistenceManagerRegistry $doctrine, Request $request, $id): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();
        
        $em = $doctrine->getManager();
        $materials = $em->getRepository(Materiels::class)->findBy(['categorie' => $id]);

        $material = new Materiels();
    
        // Assuming you have a `Categorie` entity representing the categories
        $categorie = $em->getRepository(CategorieMateriel::class)->find($id);
    
        // Set the category for the material before creating the form
        $material->setCategorie($categorie);
    
        $form = $this->createForm(MaterielType::class, $material);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();
            $entityManager->persist($material);
            $entityManager->flush();
            $this->addFlash('success', 'Materiel ajouté avec succès');
            return $this->redirectToRoute('app_material_by_category', ['id' => $id]);
        }

        return $this->render('admin/materialsByCategory.html.twig', [
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
        return $this->redirectToRoute('app_material_by_category', ['id' => $id]);
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
            return $this->redirectToRoute('app_material_by_category', ['id' => $id]);
        }

        return $this->render('admin/materiels/editMaterial.html.twig', [
            'controller_name' => 'AdminController',
            'image' => $image,
            'editForm' =>$form->createView(),
        ]);
    }


    #[Route('/list_categorie_materials', name: 'app_materials_category')]
    public function Categorymaterials(PersistenceManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();

        $em = $doctrine->getManager();
        $categories = $em->getRepository(CategorieMateriel::class)->findall();

        $banques = new CategorieMateriel();
        $form = $this->createForm(CategorieMaterielType::class, $banques);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $entityManager = $doctrine->getManager();
            $entityManager->persist($banques);
            $entityManager->flush();
            $this->addFlash('success', 'Categorie ajouté avec succès');
            return $this->redirectToRoute('app_materials_category');
        }

        return $this->render('admin/categorieMateriel/listeCategorie.html.twig', [
            'controller_name' => 'AdminController',
            'image' => $image,
            'categories' => $categories,
            'CategoryForm' =>$form->createView(),
        ]);
    }

    #[Route('/delete_category_material/{id}', name: 'app_delete_category_material')]
    public function deleteCategorymaterial(PersistenceManagerRegistry $doctrine, Request $request, $id): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();

        $em = $doctrine->getManager();
        $materiels = $em->getRepository(CategorieMateriel::class)->find($id);

        $em->remove($materiels);
        $em->flush();
        $this->addFlash('success', 'Categorie supprimé avec succès');
        return $this->redirectToRoute('app_materials_category');
    }

    #[Route('/edit_category_material/{id}', name: 'app_edit_category_material')]
    public function editCategorymaterial(PersistenceManagerRegistry $doctrine, Request $request, $id): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();

        $em = $doctrine->getManager();
        $pays = $em->getRepository(CategorieMateriel::class)->find($id);

        $form = $this->createForm(CategorieMaterielType::class, $pays);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $entityManager = $doctrine->getManager();
            $entityManager->persist($pays);
            $entityManager->flush();
            $this->addFlash('success', 'Categorie modifié avec succès');
            return $this->redirectToRoute('app_materials_category');
        }

        return $this->render('admin/categorieMateriel/editCategoryMaterial.html.twig', [
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

        $form = $this->createForm(EditBanqueType::class, $banques);
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
            $commande->setUser($user);


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
    public function editCommande($id, PersistenceManagerRegistry $doctrine, Request $request): Response
    {

        $user = $this->getUser();
        $image = $user->getImage();
        // Get the Doctrine entity manager
        $em = $doctrine->getManager();

        
        // Retrieve the list of banques from the database
        $banques = $em->getRepository(Banques::class)->findAll();

        // Retrieve the list of materiels from the database
        $materiels = $em->getRepository(Materiels::class)->findAll();

        // Retrieve the command entity
        $commande = $em->getRepository(Commande::class)->find($id);

        // Check if the command exists
        if (!$commande) {
            throw $this->createNotFoundException('Commande not found');
        }

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

            // Update the command attributes
            $commande->setDescription($description);
            $commande->setTauxTVA($tauxtva);
            $commande->setAvance($avance);
            $commande->setBanque($banque);
            $commande->setDate($date);
            $commande->setRef($ref);

            // Retrieve the current materials of the command
            $currentMaterials = $commande->getCommandeMateriels()->toArray();

            // Iterate over the current materials and remove them from the command
            foreach ($currentMaterials as $currentMaterial) {
                $commande->removeCommandeMateriel($currentMaterial);
                $em->remove($currentMaterial);
            }

            // Iterate over the selected Materiel IDs and create new CommandMaterial entities
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
            'image' => $image,
            'banques' => $banques,
            'materiels' => $materiels,
        ]);
    }


    #[Route('/delete_commande/{id}', name: 'app_delete_commande')]
    public function deleteCommande(PersistenceManagerRegistry $doctrine, Request $request, $id): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();

        $em = $doctrine->getManager();
        $commande = $em->getRepository(Commande::class)->find($id);

        // Remove the associated CommandeMateriels records
        $commandeMateriels = $commande->getCommandeMateriels();
        foreach ($commandeMateriels as $commandeMateriel) {
            $em->remove($commandeMateriel);
        }

        $em->remove($commande);
        $em->flush();

        $this->addFlash('success', 'Commande supprimée avec succès');
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



    #[Route('/commande_materiel/{id}', name: 'app_show_commande')]
    public function showCommandeMateriel($id, PersistenceManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();
        // Get the Doctrine entity manager
        $em = $doctrine->getManager();

        // Retrieve the command entity
        $commande = $em->getRepository(Commande::class)->find($id);

        // Check if the command exists
        if (!$commande) {
            throw $this->createNotFoundException('Commande not found');
        }

        return $this->render('admin/detailCommande.html.twig', [
            'controller_name' => 'AdminController',
            'commande' => $commande,
            'image' => $image,
        ]);
    }


    #[Route('/commandesLP', name: 'app_show_commande_lp')]
    public function ListeCommandesLP(PersistenceManagerRegistry $doctrine, Request $request): Response
    {
        // Get the user and image information
        $user = $this->getUser();
        $image = $user->getImage();

        // Get the Doctrine entity manager
        $em = $doctrine->getManager();

        $commandelp = $em->getRepository(Commande::class)->findBy(['etat' => 'livrepaye']);

        // Retrieve the list of banques from the database
        $banques = $em->getRepository(Banques::class)->findAll();
        
        // Retrieve the list of materiels from the database
        $materiels = $em->getRepository(Materiels::class)->findAll();
        

        

        return $this->render('admin/commandes/commandeLP.html.twig', [
            'controller_name' => 'AdminController',
            'image' => $image,
            'materiels' => $materiels,
            'banques' => $banques,
            'commandeslp' => $commandelp,
        ]);
    }


    #[Route('/liste_users', name: 'app_users')]
    public function ListeUsers(PersistenceManagerRegistry $doctrine, Request $request): Response
    {
        // Get the user and image information
        $user = $this->getUser();
        $image = $user->getImage();

        // Get the Doctrine entity manager
        $em = $doctrine->getManager();

        $users = $em->getRepository(User::class)->findBy(['roles' => 'ROLE_SUPER_USER']);

        $allUsers = $em->getRepository(User::class)->createQueryBuilder('u')
        ->where('u <> :user')
        ->setParameter('user', $user)
        ->getQuery()
        ->getResult();



        return $this->render('admin/ListeUsers.html.twig', [
            'controller_name' => 'AdminController',
            'image' => $image,
            'users' => $users,
            'allUsers' => $allUsers,
        ]);
    }


    #[Route('/delete_user/{id}', name: 'app_delete_user')]
    public function deleteUser(PersistenceManagerRegistry $doctrine, Request $request, $id): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();

        $em = $doctrine->getManager();
        $users = $em->getRepository(User::class)->find($id);

        $em->remove($users);
        $em->flush();
        $this->addFlash('success', 'User supprimé avec succès');
        return $this->redirectToRoute('app_users');
    }


    #[Route('/commandes_by_user/{id}', name: 'app_commandes_by_user')]
    public function CommandesByUser(PersistenceManagerRegistry $doctrine, Request $request, $id): Response
{
    $user = $this->getUser();
    $image = $user->getImage();
    // On récupère la liste des commandes de l'utilisateur en cours
    $em = $doctrine->getManager();

    $etat = $request->query->get('etat'); // Retrieve the "etat" value from the query parameters

    $commandeRepository = $em->getRepository(Commande::class);

    // Perform the filtering based on the selected "etat" value and user ID
    if ($etat) {
        $commande = $commandeRepository->createQueryBuilder('c')
            ->where('c.user = :id')
            ->andWhere('c.etat = :etat')
            ->setParameters(['id' => $id, 'etat' => $etat])
            ->getQuery()
            ->getResult();
    } else {
        $commande = $commandeRepository->findBy(['user' => $id]);
    }

    return $this->render('admin/commandByUser.html.twig', [
        'controller_name' => 'AdminController',
        'image' => $image,
        'commandes' => $commande,
        'Id' => $id,
    ]);
}


  

    #[Route('/approveU/{id}', name: 'app_approveU')]
    public function approveU($id, UserRepository $rep, PersistenceManagerRegistry $doctrine, SendMailService $mail): Response
    {
        // Get the user to deactivate
        $user = $rep->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        // Set the user's etat to -1
        $user->setEtat('approved');



        $em = $doctrine->getManager();
        $em->persist($user);
        $em->flush();
        
        // Envoi du mail
        $mail->sendMail(
            'melekbejaoui29@gmail.com', 'Secure Print',
            $user->getEmail(),
            'Account Approval Confirmation',
            'approve',
            [
                'user' => $user,
            ]
        );

        //flash message
        $this->addFlash('success', 'User approved successfully!');

        return $this->redirectToRoute('app_pending_users');
    }
    


    #[Route('/liste_pending_users', name: 'app_pending_users')]
    public function ListePendingUsers(PersistenceManagerRegistry $doctrine, Request $request): Response
    {
        // Get the user and image information
        $user = $this->getUser();
        $image = $user->getImage();

        // Get the Doctrine entity manager
        $em = $doctrine->getManager();

        $users = $em->getRepository(User::class)->findBy(['etat' => 'pending']);

        return $this->render('admin/ListePendingUsers.html.twig', [
            'controller_name' => 'AdminController',
            'image' => $image,
            'users' => $users,
        ]);
    }



    #[Route('/commandes_expired', name: 'app_commandes_expiration')]
    public function CommandesExpired(PersistenceManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();

        $em = $doctrine->getManager();


        $commandeRepository = $em->getRepository(Commande::class);

        // Get commands older than 30 days
        $thirtyDaysAgo = new DateTime();
        $thirtyDaysAgo->sub(new DateInterval('P30D'));
        $commande = $commandeRepository->createQueryBuilder('c')
            ->where('c.date < :thirtyDaysAgo')
            ->setParameter('thirtyDaysAgo', $thirtyDaysAgo)
            ->getQuery()
            ->getResult();

        


        

       

        return $this->render('admin/commandes/commandeExpired.html.twig', [
            'controller_name' => 'AdminController',
            'image' => $image,
            'commandes' => $commande,
        ]);
    }




}
