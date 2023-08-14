<?php

namespace App\Controller;

use DateTime;
use DateInterval;
use App\Entity\Pays;
use App\Entity\User;
use App\Entity\Banques;
use App\Entity\Commande;
use App\Entity\Tresorie;
use App\Entity\Materiels;
use App\Form\BanquesType;
use App\Form\AddAdminType;
use App\Form\BankInfoType;
use App\Form\CommandeType;
use App\Form\EditPaysType;
use App\Form\EditUserType;
use App\Form\MaterielType;
use App\Form\PaysFormType;
use App\Form\PaysInfoType;
use App\Form\TresorieType;
use App\Form\EditBanqueType;
use App\Form\AddMaterialType;
use App\Form\EditCommandType;
use App\Form\EditTresorieType;
use App\Entity\TresorieHistory;
use App\Service\SendMailService;
use App\Entity\CategorieMateriel;
use App\Entity\CommandeMateriels;
use App\Repository\UserRepository;
use App\Form\CategorieMaterielType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\CategorieMaterielRepository;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/dashboard')]
class MainController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(PersistenceManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();
        
        $tresorieRepo = $doctrine->getRepository(Tresorie::class);
        $userRepository = $doctrine->getRepository(User::class);
        $commandRepository = $doctrine->getRepository(Commande::class);

        // Get total number of commands
        $totalCommands = count($commandRepository->findAll());

        $solderSum = $tresorieRepo->createQueryBuilder('t')
            ->select('SUM(t.solde_r) as totalSolder')
            ->getQuery()
            ->getSingleScalarResult();

        $finishedCommands = $commandRepository->findBy(['etat' => 'livrepaye']);
        $allFinishedCommands = count($finishedCommands);

        // $totalFinishedCommandsPercentage = ( $allFinishedCommands / $totalCommands) * 100;
        

        $pendingUsers = $userRepository->findBy(['etat' => 'pending']);
        $allUsers = $userRepository->findAll();
        
        $totalAllUsers = count($allUsers);
        // Calculate the total number of users and the percentage of pending users
        $totalUsers = count($pendingUsers);
        $totalUsersPercentage = ($totalUsers / count($userRepository->findAll())) * 100;

        // Calculate the percentage of commands
        $totalCommandsPercentage = ($totalCommands / $totalAllUsers) * 100;
        
        // command status to be displayed in chart
        $pendingC = $commandRepository->count(['etat' => 'pending']);
        $livrepaye = $commandRepository->count(['etat' => 'livrepaye']);
        $livrenonpaye = $commandRepository->count(['etat' => 'livrenonpaye']);
        $nonlivre = $commandRepository->count(['etat' => 'nonlivre']);


        // users status to be displayed in chart
        $pendingU = $userRepository->count(['etat' => 'pending']);
        $approved = $userRepository->count(['etat' => 'approved']);
        $blocked = $userRepository->count(['etat' => 'blocked']);
        $deblocked = $userRepository->count(['etat' => 'debloqué']);

       

        
        return $this->render('main/index/index.html.twig', [
            'controller_name' => 'MainController',
            'image' => $image,
            'pendingUsersCount' => $totalUsers,
            'pendingUsersPercentage' => $totalUsersPercentage,
            'totalCommandsCount' => $totalCommands,
            'totalCommandsPercentage' => $totalCommandsPercentage,
            'solderSum' => $solderSum,
            'allFinishedCommands' => $allFinishedCommands,
            // 'percentageFinishedC' => $totalFinishedCommandsPercentage,
            'pendingC' => $pendingC,
            'livrepaye' => $livrepaye,
            'livrenonpaye' => $livrenonpaye,
            'nonlivre' => $nonlivre,
            'pendingU' => $pendingU,
            'approved' => $approved,
            'blocked' => $blocked,
            'deblocked' => $deblocked,
           

        ]);
    }


    #[Route('/pays', name: 'app_country')]
    public function pays(PersistenceManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();

        $em = $doctrine->getManager();
        $pays = $em->getRepository(Pays::class)->findAll();

        return $this->render('main/pays/pays.html.twig', [
            'controller_name' => 'MainController',
            'image' => $image,
            'etats' => $pays,
            
        ]);
    }

// *************************************Gestion des pays********************************************************
    
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

        return $this->render('main/pays/listPays.html.twig', [
            'controller_name' => 'MainController',
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

        return $this->render('main/pays/editPays.html.twig', [
            'controller_name' => 'MainController',
            'image' => $image,
            'editForm' =>$form->createView(),
        ]);
    }

// *************************************Gestion des banques********************************************************

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

        return $this->render('main/banques/banques.html.twig', [
            'controller_name' => 'MainController',
            'image' => $image,
            'banks' => $banks,
            'bankForm' =>$form->createView(),
        ]);
    }

    #[Route('/add_banque', name: 'add_banque_route', methods: ['POST', 'GET'])]
    public function ajoutBanques(EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();

        $banque = new Banques();
        $form = $this->createForm(BanquesType::class, $banque);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
           
            $entityManager->persist($banque);
            $entityManager->flush();
            $this->addFlash('success', 'Banque ajouté avec succès');
            return $this->redirectToRoute('app_ajout_bon_commande');
        }

        return $this->render('main/banques/ajoutBanque.html.twig', [
            'controller_name' => 'MainController',
            'image' => $image,
            'ajoutBanqueForm' =>$form->createView(),
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

        return $this->render('main/banques/editBanque.html.twig', [
            'controller_name' => 'MainController',
            'image' => $image,
            'editForm' =>$form->createView(),
        ]);
    }
    

    #[Route('/banks_by_country/{id}', name: 'app_banks_by_country')]
    public function banksByCountry(Pays $pays, PersistenceManagerRegistry $doctrine, Request $request, $id): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();

        $em = $doctrine->getManager();
        $banks = $em->getRepository(Banques::class)->createQueryBuilder('b')
            ->where('b.pays = :paysId')
            ->andwhere('b.nom NOT LIKE :pattern')
            ->setParameter('paysId', $id)
            ->setParameter('pattern', 'caisse%')
            ->getQuery()
            ->getResult();
        //editpays
        $pays = $em->getRepository(Pays::class)->find($id);
        $form = $this->createForm(PaysInfoType::class, $pays);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $em->persist($pays);
            $em->flush();
            $this->addFlash('success', 'Pays modifié avec succès');
            return $this->redirectToRoute('app_banks_by_country', ['id' => $id]);
        }
        
        return $this->render('main/banques/banksByCountry.html.twig', [
            'controller_name' => 'MainController',
            'image' => $image,
            'banks' => $banks,
            'pays' => $pays,
            'editForm' =>$form->createView(),
        ]);
    }


// *************************************Gestion des materiels********************************************************

    #[Route('/add_material', name: 'add_material_route', methods: ['POST', 'GET'])]
    public function ajoutMaterials(EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();

        $materiel = new Materiels();
        $form = $this->createForm(AddMaterialType::class, $materiel);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
           
            $entityManager->persist($materiel);
            $entityManager->flush();
            $this->addFlash('success', 'Materiel ajouté avec succès');
            return $this->redirectToRoute('app_ajout_bon_commande');
        }

        return $this->render('main/materiels/ajoutMateriel.html.twig', [
            'controller_name' => 'MainController',
            'image' => $image,
            'ajoutMaterialForm' =>$form->createView(),
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

        return $this->render('main/materiels/materialsByCategory.html.twig', [
            'controller_name' => 'MainController',
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
        return $this->redirectToRoute('app_materials_category');
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
            return $this->redirectToRoute('app_materials_category');
        }

        return $this->render('main/materiels/editMaterial.html.twig', [
            'controller_name' => 'MainController',
            'image' => $image,
            'editForm' =>$form->createView(),
        ]);
    }

// *************************************Gestion des categories materiels********************************************************

    #[Route('/list_categorie_materials', name: 'app_materials_category')]
    public function Categorymaterials(PersistenceManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();

        $em = $doctrine->getManager();
        $categories = $em->getRepository(CategorieMateriel::class)->findall();

        $categorie = new CategorieMateriel();
        $form = $this->createForm(CategorieMaterielType::class, $categorie);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $entityManager = $doctrine->getManager();
            $entityManager->persist($categorie);
            $entityManager->flush();
            $this->addFlash('success', 'Categorie ajouté avec succès');
            return $this->redirectToRoute('app_materials_category');
        }

        return $this->render('main/categorieMateriel/listeCategorie.html.twig', [
            'controller_name' => 'MainController',
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

        return $this->render('main/categorieMateriel/editCategoryMaterial.html.twig', [
            'controller_name' => 'MainController',
            'image' => $image,
            'editForm' =>$form->createView(),
        ]);
    }

// *************************************Gestion des commandes********************************************************

    #[Route('/commandes', name: 'app_commandes')]
    public function ListeCommandes(PersistenceManagerRegistry $doctrine, Request $request): Response
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
        

        

        return $this->render('main/commandes/commande.html.twig', [
            'controller_name' => 'MainController',
            'image' => $image,
            'materiels' => $materiels,
            'banques' => $banques,
            'commandes' => $commande,
        ]);
    }

    #[Route('/ajout_commande', name: 'app_ajout_bon_commande')]
    public function ajoutCommandes( PersistenceManagerRegistry $doctrine, Request $request): Response
    {
        // Get the user and image information
        $user = $this->getUser();
        $image = $user->getImage();

        // Get the Doctrine entity manager
        $em = $doctrine->getManager();

        $commande = $em->getRepository(Commande::class)->findall();

        // Retrieve the list of banques from the database
        $banques = $em->getRepository(Banques::class)->createQueryBuilder('b')
            ->where('b.nom NOT LIKE :pattern')
            ->setParameter('pattern', 'caisse%')
            ->getQuery()
            ->getResult();
        
        // Retrieve the list of materiels from the database
        $materiels = $em->getRepository(Materiels::class)->findAll();

        $catMateriels = $em->getRepository(CategorieMateriel::class)->findAll();

        $devises = ['USD', 'EUR', 'GBP', 'JPY'];
        

        if ($request->isMethod('POST')) {
            // Get the submitted data
            $formData = $request->request->all();
            // dd($formData);
            $ref = $formData['ref'];
            $devise = $formData['devise'];
            $description = $formData['description'];
            $banqueId = $formData['banque'];
            $tauxtva = $formData['tauxtva'];
            $avance = $formData['avance'];
            $dateString = $formData['date'];
            $date = \DateTime::createFromFormat('Y-m-d', $dateString); // Assuming the date format is "YYYY-MM-DD"

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
            $commande->setDevise($devise);
            $commande->setUser($user);

            $em->persist($commande);

             // Get the selected materials data
             $selectedMaterials = $formData['materialSelect'];
             $quantities = $formData['quantite'];
             $prices = $formData['price'];


            // Iterate over the selected Materiel IDs and create CommandMaterial entities
            foreach ($selectedMaterials as $index => $materielId) {
                // Retrieve the selected Materiel entity
                $materiel = $em->getRepository(Materiels::class)->find($materielId);

                // Get the quantity and price for the current Materiel
                $quantity = $quantities[$index];
                $price = $prices[$index];

                // Create a new CommandMaterial instance
                $commandMaterial = new CommandeMateriels();
                $commandMaterial->setCommande($commande);
                $commandMaterial->setMateriel($materiel);
                $commandMaterial->setQuantite($quantity);
                $commandMaterial->setPrixV($price);

                // Persist the CommandMaterial entity
                $em->persist($commandMaterial);
            }
            // Persist the Commande entity
            $em->flush();

            // Redirect
            $this->addFlash('success', 'Commande ajouté avec succès');
            return $this->redirectToRoute('app_commandes');

            
        }



        return $this->render('main/commandes/ajoutCommande.html.twig', [
            'controller_name' => 'MainController',
            'image' => $image,
            'materiels' => $materiels,
            'categories' => $catMateriels,
            'banques' => $banques,
            'commandes' => $commande,
            'devises' => $devises,
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

        $devises = ['USD', 'EUR', 'GBP', 'JPY'];

        // Check if the command exists
        if (!$commande) {
            throw $this->createNotFoundException('Commande not found');
        }

        if ($request->isMethod('POST')) {
            // Get the submitted data
            $description = $request->request->get('description');
            $devise = $request->request->get('devise');
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
            $commande->setDevise($devise);
            $commande->setRef($ref);

            // Retrieve the current materials of the command
            $currentMaterials = $commande->getCommandeMateriels()->toArray();
            // $prices = $formData['price'];
            // Iterate over the current materials and remove them from the command
            foreach ($currentMaterials as $currentMaterial) {
                $commande->removeCommandeMateriel($currentMaterial);
                $em->remove($currentMaterial);
            }

            // Iterate over the selected Materiel IDs and create new CommandMaterial entities
            foreach ($materielIds as $materielId) {
                $materiel = $em->getRepository(Materiels::class)->find($materielId);
                $quantity = $request->request->get('textarea' . $materielId);
                $price = $request->request->get('price' . $materielId);

                // Create a new CommandMaterial instance
                $commandMaterial = new CommandeMateriels();
                $commandMaterial->setCommande($commande);
                $commandMaterial->setMateriel($materiel);
                $commandMaterial->setQuantite($quantity);
                $commandMaterial->setPrixV($price);

                // Persist the CommandMaterial entity
                $em->persist($commandMaterial);
            }

            // Flush the changes to the database
            $em->flush();

            // Redirect
            $this->addFlash('success', 'Commande mis à jour avec succès');
            return $this->redirectToRoute('app_commandes');
        }

        return $this->render('main/commandes/editCommande.html.twig', [
            'controller_name' => 'MainController',
            'commande' => $commande,
            'image' => $image,
            'banques' => $banques,
            'devises' => $devises,
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

        //edit bank
        $banque = $em->getRepository(Banques::class)->find($id);
        $form = $this->createForm(BankInfoType::class, $banque);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $em->persist($banque);
            $em->flush();
            $this->addFlash('success', 'Banque informations modifiés avec succès');
            return $this->redirectToRoute('app_commandes_by_bank', ['id' => $id]);
        }

       

        return $this->render('main/commandes/commandByBank.html.twig', [
            'controller_name' => 'MainController',
            'image' => $image,
            'commandes' => $commande,
            'Id' => $id,
            'banques' => $banque,
            'editForm' =>$form->createView(),
        ]);
    }

    #[Route('/commande_materiel/{id}', name: 'app_show_commande')]
    public function showDetailCommande($id, PersistenceManagerRegistry $doctrine): Response
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

        return $this->render('main/commandes/detailCommande.html.twig', [
            'controller_name' => 'MainController',
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
        

        

        return $this->render('main/commandes/commandeLP.html.twig', [
            'controller_name' => 'MainController',
            'image' => $image,
            'materiels' => $materiels,
            'banques' => $banques,
            'commandeslp' => $commandelp,
        ]);
    }


    #[Route('/commandes_by_user/{id}', name: 'app_commandes_by_user')]
    public function CommandesByUser(PersistenceManagerRegistry $doctrine, Request $request, $id): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();
        // On récupère la liste des commandes de l'utilisateur En cours...
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

        return $this->render('main/commandes/commandByUser.html.twig', [
            'controller_name' => 'MainController',
            'image' => $image,
            'commandes' => $commande,
            'Id' => $id,
        ]);
    }

// *************************************Gestion etat du commande********************************************************

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

    #[Route('/commandes_expired', name: 'app_commandes_expiration')]
    public function CommandesExpired(PersistenceManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();

        $em = $doctrine->getManager();
        $commandeRepository = $em->getRepository(Commande::class);

        // Get commands older than 30 days and with 'pending' state
        $thirtyDaysAgo = new DateTime();
        $thirtyDaysAgo->sub(new DateInterval('P30D'));

        $commandes = $commandeRepository->createQueryBuilder('c')
            ->where('c.date < :thirtyDaysAgo')
            ->andWhere('c.etat = :etat')
            ->setParameter('thirtyDaysAgo', $thirtyDaysAgo)
            ->setParameter('etat', 'pending')
            ->getQuery()
            ->getResult();

        return $this->render('main/commandes/commandeExpired.html.twig', [
            'controller_name' => 'MainController',
            'image' => $image,
            'commandes' => $commandes,
        ]);
    }


// *************************************Gestion des utilisateurs********************************************************

    
    #[Route('/liste_users', name: 'app_users')]
    public function ListeUsers(PersistenceManagerRegistry $doctrine, Request $request, UserPasswordHasherInterface $passwordHashed): Response
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

        //generate add user form
        $newUser = new User();
        $form = $this->createForm(AddAdminType::class, $newUser);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $newUser->setRoles('ROLE_ADMIN');
            $newUser->setPassword($passwordHashed->hashPassword($newUser, $newUser->getPassword()));
            $newUser->setVerified(1);


            $publicDir = $this->getParameter('kernel.project_dir') . '/public';

            // The source path of the image
            $sourceImagePath = $publicDir . '/assetsA/images/favicon.png';

            // Replace 'YourTargetDirectory' with the actual path to store user photos
            // (e.g., 'public/uploads/user_photos' or any directory you prefer)
            $targetDirectory = $publicDir . '/uploads/user_photos';

            // Create the target directory if it doesn't exist
            $filesystem = new Filesystem();
            $filesystem->mkdir($targetDirectory);

            // Generate a unique filename for the user's photo (to avoid conflicts)
            $filename = uniqid() . '.png';

            // The target path for the user's photo
            $targetImagePath = $targetDirectory . '/' . $filename;

            // Copy the image from the public folder to the user's photo location
            $filesystem->copy($sourceImagePath, $targetImagePath);

            // Set the user's photo path in the entity (assuming you have a 'photo' property)
            // Make sure to store the relative path to the photo (e.g., 'uploads/user_photos/filename.png')
            $newUser->setImage('uploads/user_photos/' . $filename);







            $em->persist($newUser);
            $em->flush();
            $this->addFlash('success', 'User ajouté avec succès');
            return $this->redirectToRoute('app_users');
        }





        return $this->render('main/users/ListeUsers.html.twig', [
            'controller_name' => 'MainController',
            'image' => $image,
            'users' => $users,
            'allUsers' => $allUsers,
            'addUser' =>$form->createView(),

        ]);
    }

    #[Route('/edit_user/{id}', name: 'app_edit_user')]
    public function EditUserAction(Request $request, PersistenceManagerRegistry $doctrine): Response{
        $user = $this->getUser();
        $image = $user->getImage();
        $em = $doctrine->getManager();
        $user = $em->getRepository(User::class)->find($request->get('id'));
        $form = $this->createForm(EditUserType::class, $user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'User modifié avec succès');
            return $this->redirectToRoute('app_users');
        }
        return $this->render('main/users/editUser.html.twig', [
            'controller_name' => 'MainController',
            'image' => $image,
            'editForm' =>$form->createView(),
        ]);
    }


    #[Route('/bloquer/{id}', name: 'app_block_user')]
    public function BlockU($id, UserRepository $rep, PersistenceManagerRegistry $doctrine, SendMailService $mail): Response
    {
        // Get the user to deactivate
        $user = $rep->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        // Set the user's etat to -1
        $user->setEtat('blocked');

        $em = $doctrine->getManager();
        $em->persist($user);
        $em->flush();
        
        // Envoi du mail
        $mail->sendMail(
            'melekbejaoui29@gmail.com', 'Secure Print',
            $user->getEmail(),
            'Account Restriction',
            'block',
            [
                'user' => $user,
            ]
        );

        //flash message
        $this->addFlash('success', 'User blocked successfully!');

        return $this->redirectToRoute('app_users');
    }


    #[Route('/debloquer/{id}', name: 'app_deBlock_user')]
    public function unBlockU($id, UserRepository $rep, PersistenceManagerRegistry $doctrine, SendMailService $mail): Response
    {
        // Get the user to deactivate
        $user = $rep->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        // Set the user's etat to -1
        $user->setEtat('debloqué');



        $em = $doctrine->getManager();
        $em->persist($user);
        $em->flush();
        
        // Envoi du mail
        $mail->sendMail(
            'melekbejaoui29@gmail.com', 'Secure Print',
            $user->getEmail(),
            'Account deblocked',
            'approve',
            [
                'user' => $user,
            ]
        );

        //flash message
        $this->addFlash('success', 'User deblocked successfully!');

        return $this->redirectToRoute('app_users');
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

        return $this->render('main/users/ListePendingUsers.html.twig', [
            'controller_name' => 'MainController',
            'image' => $image,
            'users' => $users,
        ]);
    }

// *************************************Gestion des tresories********************************************************

    #[Route('/tresorie', name: 'app_tresorie')]
    public function tresorie(PersistenceManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();

        $em = $doctrine->getManager();
        $pays = $em->getRepository(Pays::class)->findAll();

        return $this->render('main/tresories/tresorie.html.twig', [
            'controller_name' => 'MainController',
            'image' => $image,
            'etats' => $pays,
            
        ]);
    }

    #[Route('/liste_tresorie_banque/{id}', name: 'app_tresorie_banque')]
    public function TresorieBanque(Pays $pays,PersistenceManagerRegistry $doctrine, Request $request, $id): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();

        $em = $doctrine->getManager();

        $pays = $em->getRepository(Pays::class)->find($id);
        
        $tresoriee = $em->getRepository(Tresorie::class)->findBy(['pays' => $pays]);
        // $tresorieHistory = $em->getRepository(TresorieHistory::class)->findAll();
        // $subquery = $em->createQueryBuilder()
        //     ->select('MAX(thSub.updatedAt)')
        //     ->from(TresorieHistory::class, 'thSub')
        //     ->where('thSub.banque = th.banque')
        //     ->getDQL();

        // $tresorieHistory = $em->getRepository(TresorieHistory::class)->createQueryBuilder('th')
        //     ->where('th.updatedAt IN (' . $subquery . ')')
        //     ->getQuery()
        //     ->getResult();

        $paysId = $id;


        $tresorie = new Tresorie();
        $tresorie->setPays($pays);
        $tresorie->setDate(new \DateTime());
        $form = $this->createForm(TresorieType::class, $tresorie, ['pays_id' => $paysId]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $entityManager = $doctrine->getManager();
            $entityManager->persist($tresorie);
            $entityManager->flush();
            $this->addFlash('success', 'tresorie ajouté avec succès');
            return $this->redirectToRoute('app_tresorie_banque', ['id' => $id]);
        }

        return $this->render('main/tresories/tresorieBanques.html.twig', [
            'controller_name' => 'MainController',
            'image' => $image,
            'tresories' => $tresoriee,
            'tresorieForm' =>$form->createView(),
            // 'tresorieHistory' => $tresorieHistory,
            'pays' => $pays,
        ]);
    }

    #[Route('/edit_tresorie/{id}', name: 'app_edit_tresorie')]
    public function editTresorie(PersistenceManagerRegistry $doctrine, Request $request, $id): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();
        
        $em = $doctrine->getManager();
        $tresorie = $em->getRepository(Tresorie::class)->find($id);
        

        // Store the country ID before modifying the form
        $paysId = $tresorie->getPays()->getId();

        $form = $this->createForm(EditTresorieType::class, $tresorie);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $entityManager = $doctrine->getManager();
            $entityManager->persist($tresorie);

            $tresorieHistory = new TresorieHistory();
            $tresorieHistory->setSoldeR($tresorie->getSoldeR());
            $tresorieHistory->setEntree($tresorie->getEntree());
            $tresorieHistory->setSortie($tresorie->getSortie());
            $tresorieHistory->setBanque($tresorie->getBanque());
            $tresorieHistory->setUpdatedAt(); 
            $tresorieHistory->setUser($user); 
            // $tresorieHistory->setPays($paysId);
            $entityManager->persist($tresorieHistory);

            $entityManager->flush();
            $this->addFlash('success', 'Tresorie modifié avec succès');

            // Redirect to the 'app_tresorie_banque' route with the country ID
            return $this->redirectToRoute('app_tresorie_banque', ['id' => $paysId]);
        }

        return $this->render('main/tresories/editTresorie.html.twig', [
            'controller_name' => 'MainController',
            'image' => $image,
            'editForm' =>$form->createView(),
            
        ]);
    }


    #[Route('/show_bank_tresorie/{id}', name: 'app_show_bank_tresorie')]
    public function historyTresorie(PersistenceManagerRegistry $doctrine, Request $request,$id)
    {
        $em = $doctrine->getManager();
        
        $user = $this->getUser();
        $image = $user->getImage();

        $tresorie = $em->getRepository(Tresorie::class)->find($id);
        
        $bankHistory = $em->getRepository(TresorieHistory::class)->findBy(['banque' => $tresorie->getBanque()]);


        return $this->render('main/tresories/historiqueTresorie.html.twig', [
            'bankHistory' => $bankHistory,
            'tresorie' => $tresorie,
            'image' => $image,
        ]);
    }











}
