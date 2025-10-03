<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
use App\Service\InscriptionService;
use App\Service\SortieStateService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/sorties')]
class SortieController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SortieStateService $sortieStateService,
        private InscriptionService $inscriptionService
    ) {
    }

    #[Route('/', name: 'sortie_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(Request $request): Response
    {
        // Rediriger vers la nouvelle carte des sorties
        return $this->redirectToRoute('sortie_map_index', [
            'view' => 'liste',
            'groupe' => $request->query->get('groupe')
        ]);
    }

    #[Route('/nouvelle', name: 'sortie_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request): Response
    {
        $sortie = new Sortie();
        $sortie->setOrganisateur($this->getUser());

        // Définir l'état par défaut à "Créée"
        $etatCreee = $this->entityManager->getRepository(\App\Entity\Etat::class)
            ->findOneBy(['libelle' => 'Créée']);
        $sortie->setEtat($etatCreee);

        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($sortie);
            $this->entityManager->flush();

            $this->addFlash('success', 'Sortie créée avec succès.');

            return $this->redirectToRoute('sortie_show', ['id' => $sortie->getId()]);
        }

        // Récupérer les villes pour la sélection sur carte
        $villes = $this->entityManager->getRepository(\App\Entity\Ville::class)->findAll();

        // Récupérer les groupes de l'utilisateur pour la sélection
        $user = $this->getUser();
        $groupes = $this->entityManager->getRepository(\App\Entity\Groupe::class)->findByParticipant($user);

        return $this->render('sortie/new.html.twig', [
            'form' => $form->createView(),
            'villes' => $villes,
            'groupes' => $groupes
        ]);
    }

    #[Route('/{id}', name: 'sortie_show', requirements: ['id' => '\d+'])]
    public function show(Sortie $sortie): Response
    {
        $user = $this->getUser();

        // Vérifier si l'utilisateur peut voir cette sortie
        if (!$sortie->canVoir($user)) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette sortie.');
        }

        $participantsInscrits = $this->inscriptionService->getParticipantsInscrits($sortie);
        $isInscrit = $user ? $this->inscriptionService->isInscrit($user, $sortie) : false;

        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie,
            'participants_inscrits' => $participantsInscrits,
            'is_inscrit' => $isInscrit,
            'can_publish' => $this->isGranted('PUBLISH', $sortie),
            'can_edit' => $this->isGranted('EDIT', $sortie),
            'can_cancel' => $this->isGranted('CANCEL', $sortie),
            'can_delete' => $this->isGranted('DELETE', $sortie),
            'can_see' => $this->isGranted('VOIR', $sortie),
            'can_inscribe' => $this->isGranted('S_INSCRIRE', $sortie),
        ]);
    }

    #[Route('/{id}/editer', name: 'sortie_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('EDIT', subject: 'sortie')]
    public function edit(Request $request, Sortie $sortie): Response
    {
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Sortie modifiée avec succès.');

            return $this->redirectToRoute('sortie_show', ['id' => $sortie->getId()]);
        }

        // Récupérer les villes pour la sélection sur carte
        $villes = $this->entityManager->getRepository(\App\Entity\Ville::class)->findAll();

        return $this->render('sortie/edit.html.twig', [
            'form' => $form->createView(),
            'sortie' => $sortie,
            'villes' => $villes
        ]);
    }

    #[Route('/{id}/publier', name: 'sortie_publier', methods: ['POST'])]
    #[IsGranted('PUBLISH', subject: 'sortie')]
    public function publier(Sortie $sortie): Response
    {
        if ($this->sortieStateService->publierSortie($sortie)) {
            $this->addFlash('success', 'Sortie publiée avec succès');
        } else {
            $this->addFlash('error', 'Impossible de publier la sortie');
        }

        return $this->redirectToRoute('sortie_show', ['id' => $sortie->getId()]);
    }

    #[Route('/{id}/annuler', name: 'sortie_annuler', methods: ['POST'])]
    #[IsGranted('CANCEL', subject: 'sortie')]
    public function annuler(Sortie $sortie): Response
    {
        if ($this->sortieStateService->annulerSortie($sortie)) {
            // Annuler toutes les inscriptions
            $nbInscriptions = $this->inscriptionService->annulerToutesInscriptions($sortie);
            $this->addFlash('success', "Sortie annulée avec succès. $nbInscriptions inscriptions annulées.");
        } else {
            $this->addFlash('error', 'Impossible d\'annuler la sortie');
        }

        return $this->redirectToRoute('sortie_show', ['id' => $sortie->getId()]);
    }

    #[Route('/{id}/inscrire', name: 'sortie_inscrire', methods: ['POST'])]
    public function inscrire(Sortie $sortie): Response
    {
        $participant = $this->getUser();

        if (!$participant) {
            $this->addFlash('error', 'Vous devez être connecté pour vous inscrire');
            return $this->redirectToRoute('sortie_show', ['id' => $sortie->getId()]);
        }

        try {
            $this->inscriptionService->inscrire($participant, $sortie);
            $this->addFlash('success', 'Inscription réussie');
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('sortie_show', ['id' => $sortie->getId()]);
    }

    #[Route('/{id}/desister', name: 'sortie_desister', methods: ['POST'])]
    public function desister(Sortie $sortie): Response
    {
        $participant = $this->getUser();

        if (!$participant) {
            $this->addFlash('error', 'Vous devez être connecté pour vous désinscrire');
            return $this->redirectToRoute('sortie_show', ['id' => $sortie->getId()]);
        }

        try {
            $this->inscriptionService->desister($participant, $sortie);
            $this->addFlash('success', 'Désistement réussi');
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('sortie_show', ['id' => $sortie->getId()]);
    }

    #[Route('/{id}/supprimer', name: 'sortie_supprimer', methods: ['POST'])]
    #[IsGranted('DELETE', subject: 'sortie')]
    public function supprimer(Sortie $sortie): Response
    {
        $this->entityManager->remove($sortie);
        $this->entityManager->flush();

        $this->addFlash('success', 'Sortie supprimée avec succès');
        return $this->redirectToRoute('home');
    }
}
