<?php

namespace App\Controller;

use App\Entity\Groupe;
use App\Entity\Participant;
use App\Form\GroupeType;
use App\Repository\GroupeRepository;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/groupes')]
#[IsGranted('ROLE_USER')]
class GroupeController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private GroupeRepository $groupeRepository,
        private ParticipantRepository $participantRepository
    ) {
    }

    #[Route('/', name: 'groupe_index', methods: ['GET'])]
    public function index(): Response
    {
        $user = $this->getUser();

        // Récupérer les groupes selon le rôle de l'utilisateur
        if ($user->isAdministrateur()) {
            $groupes = $this->groupeRepository->findActifs();
        } else {
            $groupes = $this->groupeRepository->findByParticipant($user);
        }

        return $this->render('groupe/index.html.twig', [
            'groupes' => $groupes,
            'user' => $user,
        ]);
    }

    #[Route('/nouveau', name: 'groupe_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $groupe = new Groupe();
        $groupe->setCreateur($this->getUser());
        $groupe->addParticipant($this->getUser()); // Le créateur est automatiquement membre

        $form = $this->createForm(GroupeType::class, $groupe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($groupe);
            $this->entityManager->flush();

            $this->addFlash('success', 'Groupe créé avec succès.');

            return $this->redirectToRoute('groupe_show', ['id' => $groupe->getId()]);
        }

        return $this->render('groupe/new.html.twig', [
            'groupe' => $groupe,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'groupe_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Groupe $groupe): Response
    {
        $user = $this->getUser();

        // Vérifier si l'utilisateur peut voir ce groupe
        if (!$groupe->isMembre($user) && !$user->isAdministrateur()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à ce groupe.');
        }

        return $this->render('groupe/show.html.twig', [
            'groupe' => $groupe,
            'user' => $user,
            'can_edit' => $groupe->canGérer($user),
            'can_manage_members' => $groupe->canGérer($user),
        ]);
    }

    #[Route('/{id}/editer', name: 'groupe_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Groupe $groupe): Response
    {
        $user = $this->getUser();

        // Vérifier si l'utilisateur peut modifier ce groupe
        if (!$groupe->canGérer($user)) {
            throw $this->createAccessDeniedException('Vous n\'avez pas le droit de modifier ce groupe.');
        }

        $form = $this->createForm(GroupeType::class, $groupe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Groupe modifié avec succès.');

            return $this->redirectToRoute('groupe_show', ['id' => $groupe->getId()]);
        }

        return $this->render('groupe/edit.html.twig', [
            'groupe' => $groupe,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/membres', name: 'groupe_membres', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function membres(Groupe $groupe): Response
    {
        $user = $this->getUser();

        // Vérifier si l'utilisateur peut voir les membres
        if (!$groupe->isMembre($user) && !$user->isAdministrateur()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à ce groupe.');
        }

        return $this->render('groupe/membres.html.twig', [
            'groupe' => $groupe,
            'user' => $user,
            'can_manage_members' => $groupe->canGérer($user),
        ]);
    }

    #[Route('/{id}/ajouter-membre', name: 'groupe_add_member', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function addMember(Request $request, Groupe $groupe): Response
    {
        $user = $this->getUser();

        // Vérifier si l'utilisateur peut gérer les membres
        if (!$groupe->canGérer($user)) {
            throw $this->createAccessDeniedException('Vous n\'avez pas le droit de gérer les membres de ce groupe.');
        }

        $pseudo = $request->request->get('pseudo');
        if (!$pseudo) {
            $this->addFlash('error', 'Pseudo requis.');
            return $this->redirectToRoute('groupe_membres', ['id' => $groupe->getId()]);
        }

        $participant = $this->participantRepository->findOneBy(['pseudo' => $pseudo]);
        if (!$participant) {
            $this->addFlash('error', 'Participant non trouvé.');
            return $this->redirectToRoute('groupe_membres', ['id' => $groupe->getId()]);
        }

        if ($groupe->isMembre($participant)) {
            $this->addFlash('warning', 'Ce participant est déjà membre du groupe.');
            return $this->redirectToRoute('groupe_membres', ['id' => $groupe->getId()]);
        }

        $groupe->addParticipant($participant);
        $this->entityManager->flush();

        $this->addFlash('success', 'Membre ajouté avec succès.');

        return $this->redirectToRoute('groupe_membres', ['id' => $groupe->getId()]);
    }

    #[Route('/{id}/retirer-membre/{participantId}', name: 'groupe_remove_member', methods: ['POST'], requirements: ['id' => '\d+', 'participantId' => '\d+'])]
    public function removeMember(Groupe $groupe, int $participantId): Response
    {
        $user = $this->getUser();

        // Vérifier si l'utilisateur peut gérer les membres
        if (!$groupe->canGérer($user)) {
            throw $this->createAccessDeniedException('Vous n\'avez pas le droit de gérer les membres de ce groupe.');
        }

        $participant = $this->participantRepository->find($participantId);
        if (!$participant) {
            $this->addFlash('error', 'Participant non trouvé.');
            return $this->redirectToRoute('groupe_membres', ['id' => $groupe->getId()]);
        }

        // Ne pas permettre de retirer le créateur
        if ($participant === $groupe->getCreateur()) {
            $this->addFlash('error', 'Impossible de retirer le créateur du groupe.');
            return $this->redirectToRoute('groupe_membres', ['id' => $groupe->getId()]);
        }

        $groupe->removeParticipant($participant);
        $this->entityManager->flush();

        $this->addFlash('success', 'Membre retiré avec succès.');

        return $this->redirectToRoute('groupe_membres', ['id' => $groupe->getId()]);
    }

    #[Route('/{id}/quitter', name: 'groupe_leave', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function leave(Groupe $groupe): Response
    {
        $user = $this->getUser();

        // Vérifier si l'utilisateur est membre
        if (!$groupe->isMembre($user)) {
            $this->addFlash('error', 'Vous n\'êtes pas membre de ce groupe.');
            return $this->redirectToRoute('groupe_index');
        }

        // Ne pas permettre au créateur de quitter
        if ($user === $groupe->getCreateur()) {
            $this->addFlash('error', 'Le créateur ne peut pas quitter le groupe. Transférez d\'abord la propriété.');
            return $this->redirectToRoute('groupe_show', ['id' => $groupe->getId()]);
        }

        $groupe->removeParticipant($user);
        $this->entityManager->flush();

        $this->addFlash('success', 'Vous avez quitté le groupe.');

        return $this->redirectToRoute('groupe_index');
    }

    #[Route('/{id}/supprimer', name: 'groupe_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Groupe $groupe): Response
    {
        $user = $this->getUser();

        // Vérifier si l'utilisateur peut supprimer ce groupe
        if (!$groupe->canGérer($user)) {
            throw $this->createAccessDeniedException('Vous n\'avez pas le droit de supprimer ce groupe.');
        }

        if ($this->isCsrfTokenValid('delete' . $groupe->getId(), $request->request->get('_token'))) {
            // Désactiver le groupe au lieu de le supprimer
            $groupe->setActif(false);
            $this->entityManager->flush();

            $this->addFlash('success', 'Groupe supprimé avec succès.');
        }

        return $this->redirectToRoute('groupe_index');
    }

    #[Route('/recherche', name: 'groupe_search', methods: ['GET'])]
    public function search(Request $request): Response
    {
        $searchTerm = $request->query->get('q', '');
        $groupes = [];

        if (!empty($searchTerm)) {
            $groupes = $this->groupeRepository->searchByNom($searchTerm);
        }

        return $this->render('groupe/search.html.twig', [
            'groupes' => $groupes,
            'searchTerm' => $searchTerm,
        ]);
    }

    #[Route('/statistiques', name: 'groupe_stats', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function stats(): Response
    {
        $stats = [
            'total' => $this->groupeRepository->countActifs(),
            'mostPopular' => $this->groupeRepository->findMostPopular(5),
            'mostActive' => $this->groupeRepository->findMostActive(5),
            'recentlyCreated' => $this->groupeRepository->findRecentlyCreated(5),
            'empty' => $this->groupeRepository->findEmpty(),
            'withOneParticipant' => $this->groupeRepository->findWithOneParticipant(),
        ];

        return $this->render('groupe/stats.html.twig', [
            'stats' => $stats,
        ]);
    }
}
