<?php

namespace App\Controller;

use DateTime;
use DateInterval;
use App\Entity\Pays;
use App\Entity\User;
use App\Entity\Compte;
use App\Entity\Devise;
use App\Entity\Banques;
use Twilio\Rest\Client;
use App\Entity\Commande;
use App\Form\CompteType;
use App\Form\DeviseType;
use App\Entity\Materiels;
use App\Entity\Operation;
use App\Form\BanquesType;
use App\Form\AddAdminType;
use App\Form\BankInfoType;
use App\Form\CommandeType;
use App\Form\EditPaysType;
use App\Form\EditUserType;
use App\Form\MaterielType;
use App\Form\PaysFormType;
use App\Form\PaysInfoType;
use App\Form\OperationType;
use App\Form\EditBanqueType;
use App\Form\AddMaterialType;
use App\Form\EditCommandType;
use App\Service\SendMailService;
use App\Entity\CategorieMateriel;
use App\Entity\CommandeMateriels;
use App\Repository\UserRepository;
use App\Form\CategorieMaterielType;
use App\Security\EditCommandeVoter;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
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
        
        $userRepository = $doctrine->getRepository(User::class);
        $commandRepository = $doctrine->getRepository(Commande::class);
        $bankRepository = $doctrine->getRepository(Banques::class);
        $compteRepository =  $doctrine->getRepository(Compte::class);
        $paysRepository = $doctrine->getRepository(Pays::class);
        $paysList = $paysRepository->findAll();
    
        $paysData = [];
        foreach ($paysList as $pays) {
            $soldeSumByPays = 0;
            
            foreach ($pays->getBanques() as $banque) {
                foreach ($banque->getCompte() as $compte) {
                    $soldeSumByPays += $compte->getSolde();
                }
            }
        
            if (!isset($paysData[$pays->getNom()])) {
                $paysData[$pays->getNom()] = 0;
            }
            
            $paysData[$pays->getNom()] += $soldeSumByPays;
        }
        
        $paysDataArray = [];
        foreach ($paysData as $paysName => $soldeSum) {
            $paysDataArray[] = [
                'name' => $paysName,
                'solde' => $soldeSum,
            ];
        }
        

        // Get total number of commands
        $totalCommands = count($commandRepository->findAll());
        $banks = $bankRepository->findAll();

        $totalSoldeDesComptes = $compteRepository->createQueryBuilder('c')
        ->select('SUM(c.solde) as totalSolde')
        ->getQuery()
        ->getSingleScalarResult();

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
            'totalCommandsCount' => $totalCommands,
            'pendingC' => $pendingC,
            'livrepaye' => $livrepaye,
            'livrenonpaye' => $livrenonpaye,
            'nonlivre' => $nonlivre,
            'pendingU' => $pendingU,
            'approved' => $approved,
            'blocked' => $blocked,
            'deblocked' => $deblocked,
            'banks' => $banks,
            'totalSoldeDesComptes' => $totalSoldeDesComptes,
            'paysData' => $paysDataArray, 
           

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
        //editpaysinformation
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
    public function ListeCommandes(PersistenceManagerRegistry $doctrine, Request $request, PaginatorInterface $paginator): Response
    {
        // Get the user and image information
        $user = $this->getUser();
        $image = $user->getImage();

        // Get the Doctrine entity manager
        $em = $doctrine->getManager();

        $commandeRepository = $em->getRepository(Commande::class);

        $etat = $request->query->get('etat');
        $reference = $request->query->get('reference');
        $date = $request->query->get('date');

        // Create the query builder
        $queryBuilder = $commandeRepository->createQueryBuilder('c');

        // Apply filters
        if ($etat) {
            $queryBuilder->andWhere('c.etat = :etat')
                ->setParameter('etat', $etat);
        }
        
        if ($reference) {
            $queryBuilder->orWhere('c.ref LIKE :reference')
                ->setParameter('reference', '%' . $reference . '%');
        }

        if ($date) {
            $queryBuilder->andWhere('c.date = :date') // Adjust this based on your actual date field name
                ->setParameter('date', new \DateTime($date));
        }

        // Get the query
        $query = $queryBuilder->getQuery();

        // Paginate the results
        $pagination = $paginator->paginate(
            $query,       // Query to paginate
            $request->query->getInt('page', 1), // Current page number
            2           // Items per page
        );


        return $this->render('main/commandes/commande.html.twig', [
            'controller_name' => 'MainController',
            'image' => $image,
            // 'commandes' => $commande,
            'pagination' => $pagination,
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

        $devises = $em->getRepository(Devise::class)->findall();
        

        if ($request->isMethod('POST')) {
            // Get the submitted data
            $formData = $request->request->all();
            // dd($formData);
            $ref = $formData['ref'];
            $deviseId = $formData['devise'];
            $description = $formData['description'];
            $banqueId = $formData['banque'];
            $tauxtva = $formData['tauxtva'];
            $avance = $formData['avance'];
            $remise = $formData['remise'];
            $dateString = $formData['date'];
            $date = \DateTime::createFromFormat('Y-m-d', $dateString); // Assuming the date format is "YYYY-MM-DD"
            
            // Retrieve the selected Banque entity
            $banque = $em->getRepository(Banques::class)->find($banqueId);
            $devise = $em->getRepository(Devise::class)->find($deviseId);
        
                
            // Create a new Commande instance
            $commande = new Commande();
            $commande->setDescription($description);
            $commande->setTauxTVA($tauxtva);
            $commande->setAvance($avance);
            $commande->setRemise($remise);
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
        $banques = $em->getRepository(Banques::class)->createQueryBuilder('b')
        ->where('b.nom NOT LIKE :pattern')
        ->setParameter('pattern', 'caisse%')
        ->getQuery()
        ->getResult();

        // Retrieve the list of materiels from the database
        $materiels = $em->getRepository(Materiels::class)->findAll();

        // Retrieve the command entity
        $commande = $em->getRepository(Commande::class)->find($id);
        $commandeMaterials = $commande->getCommandeMateriels();

        $devises = $em->getRepository(Devise::class)->findall();

        // Check if the command exists
        if (!$commande) {
            throw $this->createNotFoundException('Commande not found');
        }

        // $this->denyAccessUnlessGranted(EditCommandeVoter::EDIT_COMMANDE, $commande, 'You are not allowed to edit this commande.');

        if ($request->isMethod('POST')) {
            // Get the submitted data
            $formData = $request->request->all();

            $description = $formData['description'];
            $deviseId = $formData['devise'];
            $tauxtva = $formData['tauxtva'];
            $avance = $formData['avance'];
            $remise = $formData['remise'];
            $ref = $formData['ref'];
            $dateString = $formData['date'];
            $date = \DateTime::createFromFormat('Y-m-d', $dateString); // Assuming the date format is "YYYY-MM-DD"
            $banqueId = $formData['banque'];
            $materielIds = $formData['materialSelect'];
            $quantities = $formData['quantite'];
            $prices = $formData['price'];

            // Retrieve the selected Banque entity
            $banque = $em->getRepository(Banques::class)->find($banqueId);
            $devise = $em->getRepository(Devise::class)->find($deviseId);

            // Update the command attributes
            $commande->setDescription($description);
            $commande->setTauxTVA($tauxtva);
            $commande->setAvance($avance);
            $commande->setRemise($remise);
            $commande->setBanque($banque);
            $commande->setDate($date);
            $commande->setDevise($devise);
            $commande->setRef($ref);
            
            // Before iterating over selected materials
            foreach ($commandeMaterials as $existingMaterial) {
                if (!in_array($existingMaterial->getMateriel()->getId(), $materielIds)) {
                    // Remove the existing material if not in the selected list
                    $em->remove($existingMaterial);
                }
            }
            // Iterate over the selected Materiel IDs and create/update CommandMaterial entities
            foreach ($materielIds as $index => $materielId) {
                // Retrieve the selected Materiel entity
                $materiel = $em->getRepository(Materiels::class)->find($materielId);

                // Get the quantity and price for the current Materiel
                $quantity = $quantities[$index];
                $price = $prices[$index];

                // Find existing CommandMaterial entity if it exists for this combination
                $existingCommandMaterial = $em->getRepository(CommandeMateriels::class)->findOneBy([
                    'commande' => $commande,
                    'materiel' => $materiel,
                ]);

                if ($existingCommandMaterial) {
                    // Update the existing CommandMaterial entity
                    $existingCommandMaterial->setQuantite($quantity);
                    $existingCommandMaterial->setPrixV($price);
                } else {
                    // Create a new CommandMaterial instance
                    $commandMaterial = new CommandeMateriels();
                    $commandMaterial->setCommande($commande);
                    $commandMaterial->setMateriel($materiel);
                    $commandMaterial->setQuantite($quantity);
                    $commandMaterial->setPrixV($price);

                    // Persist the CommandMaterial entity
                    $em->persist($commandMaterial);
                }
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
            'commandeMaterials' => $commandeMaterials,
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
        $reference = $request->query->get('reference');
        
        // Perform the filtering based on the selected "etat" value and bank ID
        $queryBuilder = $commandeRepository->createQueryBuilder('c');

        // Apply the banque filter (similar to your original code)
        if ($id) {
            if ($etat) {
                $queryBuilder->andWhere('c.banque = :id')
                    ->andWhere('c.etat = :etat')
                    ->setParameters(['id' => $id, 'etat' => $etat]);
            } else {
                $queryBuilder->andWhere('c.banque = :id')
                    ->setParameter('id', $id);
            }
        }

        // Apply the etat filter
        if ($etat) {
            $queryBuilder->andWhere('c.etat = :etat')
                ->setParameter('etat', $etat);
        }

        // Apply the reference filter
        if ($reference) {
            $queryBuilder->andWhere('c.ref LIKE :reference')
                ->setParameter('reference', '%' . $reference . '%');
        }

        $commande = $queryBuilder->getQuery()->getResult();

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

        $etat = $request->query->get('etat'); 
        $reference = $request->query->get('reference');// Retrieve the "etat" value from the query parameters

        $commandeRepository = $em->getRepository(Commande::class);

        // Perform the filtering based on the selected "etat" value and user ID
        $queryBuilder = $commandeRepository->createQueryBuilder('c');

        // Apply the user filter (similar to your original code)
        if ($id) {
            if ($etat) {
                $queryBuilder->andWhere('c.user = :id')
                    ->andWhere('c.etat = :etat')
                    ->setParameters(['id' => $id, 'etat' => $etat]);
            } else {
                $queryBuilder->andWhere('c.user = :id')
                    ->setParameter('id', $id);
            }
        }

        // Apply the etat filter
        if ($etat) {
            $queryBuilder->andWhere('c.etat = :etat')
                ->setParameter('etat', $etat);
        }

        // Apply the reference filter
        if ($reference) {
            $queryBuilder->andWhere('c.ref LIKE :reference')
                ->setParameter('reference', '%' . $reference . '%');
        }

        $commande = $queryBuilder->getQuery()->getResult();

        return $this->render('main/commandes/commandByUser.html.twig', [
            'controller_name' => 'MainController',
            'image' => $image,
            'commandes' => $commande,
            'Id' => $id,
        ]);
    }

// *************************************Gestion etat du commande********************************************************

    #[Route("/set-etat/{id}/{etat}", name: 'app_set_etat')]
    public function setEtat($id, $etat,PersistenceManagerRegistry $doctrine, SessionInterface $session, SendMailService $mail)
    {   
        $user = $this->getUser();
        $entityManager = $doctrine->getManager();
        $command = $entityManager->getRepository(Commande::class)->find($id);
        
        if (!$command) {
            throw $this->createNotFoundException('Command not found.');
        }
        
        // Set the new "etat" value
        $command->setEtat($etat);
        
        // Persist the changes to the database
        $entityManager->flush();

        // Send email to users with ROLE_USER
        // if (strpos($user->getRoles(), 'ROLE_SUPER_USER') !== false) {
        //     $userRepository = $entityManager->getRepository(User::class);
        //     $usersWithUserRole = $userRepository->findBy(['roles' => 'ROLE_USER']);
    
        //     $subject = 'Command Etat Changed';
        //     $template = 'command_etat_changed'; // Update this with your template name
    
        //     $data = [
        //         'command' => $command,
        //         'newEtat' => $etat,
        //     ];
    
        //     foreach ($usersWithUserRole as $userWithUserRole) {
        //         $mail->sendMail('truvision_tn@truvisionco.com', 'Secure Print', $userWithUserRole->getEmail(), $subject, $template, $data);
        //     }
        // }

        // Optionally, you can add a flash message to indicate successful update
        $session->getFlashBag()->add('success', 'Etat changed successfully!');

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

// *************************************Gestion des devises********************************************************

    #[Route('/ajout_devise', name: 'app_add_devise')]
    public function ajoutDevise(PersistenceManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();

        $em = $doctrine->getManager();
        $devises = $em->getRepository(Devise::class)->findall();

        $devise = new Devise();
        $form = $this->createForm(DeviseType::class, $devise);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $entityManager = $doctrine->getManager();
            $entityManager->persist($devise);
            $entityManager->flush();
            $this->addFlash('success', 'Devise ajouté avec succès');
            return $this->redirectToRoute('app_ajout_bon_commande');
        }

        return $this->render('main/devises/ajoutDevise.html.twig', [
            'controller_name' => 'MainController',
            'image' => $image,
            'deviseForm' =>$form->createView(),
        ]);
    }

    #[Route('/delete_devise/{id}', name: 'app_delete_devise')]
    public function deleteDevise(PersistenceManagerRegistry $doctrine, Request $request, $id): Response
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

    #[Route('/edit_devise/{id}', name: 'app_edit_devise')]
    public function editDevise(PersistenceManagerRegistry $doctrine, Request $request, $id): Response
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

    private function fetchExchangeRatesFromApi() {
        $apiKey = 'bfdc5c86f7d64f9ba81f69ab1334b4ab'; // Replace with your API key
        $apiEndpoint = 'https://openexchangerates.org/api/latest.json';
        
        $client = HttpClient::create();
        $response = $client->request('GET', $apiEndpoint, [
            'query' => [
                'app_id' => $apiKey,
                'base' => 'USD', // Base currency for exchange rates
            ],
        ]);
        
        $data = $response->toArray();
        return $data['rates'];
    }

// *************************************Gestion des comptes bancaires********************************************************
    
    #[Route('/comptes', name: 'app_pays_tresoreries')]
    public function PaysBanques(PersistenceManagerRegistry $doctrine, Request $request): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();

        $em = $doctrine->getManager();
        $pays = $em->getRepository(Pays::class)->findAll();

        return $this->render('main/tresoreries/pays.html.twig', [
            'controller_name' => 'MainController',
            'image' => $image,
            'etats' => $pays,
            
        ]);
    }

    #[Route('/banks_by_country_tresorerie/{id}', name: 'app_banks_by_country_tresorerie')]
    public function banksByCountryTresoreries(Pays $pays, PersistenceManagerRegistry $doctrine, Request $request, $id): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();

        $em = $doctrine->getManager();
        $banks = $em->getRepository(Banques::class)->createQueryBuilder('b')
            ->where('b.pays = :paysId')
            ->setParameter('paysId', $id)
            ->getQuery()
            ->getResult();

        
        return $this->render('main/tresoreries/banksByCountry.html.twig', [
            'controller_name' => 'MainController',
            'image' => $image,
            'banks' => $banks,
            'pays' => $pays,
           
        ]);
    }


    #[Route('/comptes_by_bank/{id}', name: 'app_comptes_by_bank')]
    public function ComptesByBank(PersistenceManagerRegistry $doctrine, Request $request, $id, Banques $banque): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();

        $entityManager = $doctrine->getManager();
        
        // Fetch the Banque entity by its ID
        $banque = $entityManager->getRepository(Banques::class)->find($id);

        if (!$banque) {
            throw $this->createNotFoundException('Banque not found.');
        }

        $comptes = $entityManager->getRepository(Compte::class)->findBy(['banques' => $banque]);

        $compte = new Compte();
        $compte->setBanques($banque);
        $compte->setSolde(0);

        // Create a form using the CompteType form type
        $form = $this->createForm(CompteType::class, $compte);

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // If the form is submitted and valid, persist the Compte entity
            $entityManager->persist($compte);
            $entityManager->flush();
            
            return $this->redirectToRoute('app_comptes_by_bank', ['id' => $id]);
        }

        return $this->render('main/tresoreries/compteByBank.html.twig', [
            'controller_name' => 'MainController',
            'image' => $image,
            'form' => $form->createView(),
            'comptes' => $comptes,
            'banque' => $banque,
            
        ]);
    }


    #[Route('/operation_by_compte/{id}', name: 'app_operations_by_comptes')]
    public function OperationsByCompte(PersistenceManagerRegistry $doctrine, Request $request, $id, Compte $comptee): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();

        $entityManager = $doctrine->getManager();
        
        // Fetch the Banque entity by its ID
        $compte = $entityManager->getRepository(Compte::class)->find($id);

        if (!$compte) {
            throw $this->createNotFoundException('Compte not found.');
        }

        $operations = $entityManager->getRepository(Operation::class)->findBy(['compte' => $compte]);

        $operation = new Operation();
        $operation->setCompte($compte);
        $operation->setUser($user);
        // $operation->setDate(new \DateTime());

        // Create a form using the CompteType form type
        $form = $this->createForm(OperationType::class, $operation);

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Update the soldeR and soldeam fields based on form data
            $montant = $operation->getMontant();
            $type = $operation->getType();
        
            // Fetch the latest soldeam from the database for the given compte
            $latestOperation = $entityManager->getRepository(Operation::class)
                ->findOneBy(['compte' => $compte], ['id' => 'DESC']);
        
            $lastsoldeAM = $latestOperation ? $latestOperation->getSoldeam() : $compte->getSolde();
        
            // The soldeR should be based on the previous soldeam value
            $soldeR = $lastsoldeAM;
        
            // Set the initial soldeR value to the Operation entity
            $operation->setSoldeR($soldeR);
        
            if ($operation->getType() == 'entree') {
                $operation->setSoldeAM($operation->getSoldeR() + $operation->getMontant());
            } elseif ($operation->getType() == 'sortie') {
                $operation->setSoldeAM($operation->getSoldeR() - $operation->getMontant());
            }

            $compte->setSolde($operation->getSoldeAM());
            
            // Persist and flush the changes for both entities
            $entityManager->persist($operation);
            $entityManager->persist($compte);
            $entityManager->flush();
        
            return $this->redirectToRoute('app_operations_by_comptes', ['id' => $id]);
        }
        
        
        

        return $this->render('main/tresoreries/operationByCompte.html.twig', [
            'controller_name' => 'MainController',
            'image' => $image,
            'form' => $form->createView(),
            'operations' => $operations,
            'compte' => $comptee,
        ]);
    }


    #[Route('/delete_operation/{id}', name: 'app_delete_operation')]
    public function deleteOperation(PersistenceManagerRegistry $doctrine, Request $request, $id): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();

        $em = $doctrine->getManager();
        $operation = $em->getRepository(Operation::class)->find($id);

        // Capture the pays ID before deleting the operation
        $compteId = $operation->getCompte()->getId();

        $em->remove($operation);
        $em->flush();
        $this->addFlash('success', 'Opération supprimée avec succès');

        // Redirect with the pays ID after deletion
        return $this->redirectToRoute('app_operations_by_comptes', ['id' => $compteId]);
    }
  
    #[Route('/liste_tr', name: 'app_liste_tr')]
    public function listeTr(Request $request, PersistenceManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();
        $image = $user->getImage();

        $paysRepository = $doctrine->getRepository(Pays::class);
        $paysWithBanques = $paysRepository->findAll();
        // $entityManager
        $entityManager = $doctrine->getManager();

        // Get filter values from the request
        $qb = $entityManager->createQueryBuilder();

        $qb->select('p')
           ->from(Pays::class, 'p')
           ->leftJoin('p.banques', 'b'); // Assuming you have a relation 'banques' in your Pays entity
    
        // Get filter values from the request
        $selectedPaysId = $request->query->get('pays');
    
        // Apply filters
        if ($selectedPaysId) {
            $qb->andWhere('p.id = :selectedPaysId')
               ->setParameter('selectedPaysId', $selectedPaysId);
        }
    
        $query = $qb->getQuery();
        $paysWithBanques = $query->getResult();
    

        return $this->render('main/tresoreries/listeTR.html.twig', [
            'paysWithBanques' => $paysWithBanques,
            'image' => $image,
            'selectedPaysId' => $selectedPaysId, 
        ]);
    }





}
