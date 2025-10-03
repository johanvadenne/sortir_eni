<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\AdminParticipantType;
use App\Form\SiteType;
use App\Form\VilleType;
use App\Service\SortieStateService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private SortieStateService $sortieStateService
    ) {
    }

    #[Route('/', name: 'admin_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    // Gestion des Villes
    #[Route('/villes', name: 'admin_villes')]
    public function villes(Request $request): Response
    {
        $villes = $this->entityManager->getRepository(Ville::class)->findAll();

        $ville = new Ville();
        $form = $this->createForm(VilleType::class, $ville);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($ville);
            $this->entityManager->flush();

            $this->addFlash('success', 'Ville créée avec succès.');
            return $this->redirectToRoute('admin_villes');
        }

        return $this->render('admin/villes.html.twig', [
            'villes' => $villes,
            'form' => $form->createView()
        ]);
    }

    #[Route('/villes/{id}/editer', name: 'admin_ville_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function editVille(Request $request, Ville $ville): Response
    {
        $form = $this->createForm(VilleType::class, $ville);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Ville modifiée avec succès.');
            return $this->redirectToRoute('admin_villes');
        }

        return $this->render('admin/ville_edit.html.twig', [
            'form' => $form->createView(),
            'ville' => $ville
        ]);
    }

    #[Route('/villes/{id}/supprimer', name: 'admin_ville_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function deleteVille(Ville $ville): Response
    {
        $this->entityManager->remove($ville);
        $this->entityManager->flush();

        $this->addFlash('success', 'Ville supprimée avec succès.');
        return $this->redirectToRoute('admin_villes');
    }

    // Gestion des Sites
    #[Route('/sites', name: 'admin_sites')]
    public function sites(Request $request): Response
    {
        $sites = $this->entityManager->getRepository(Site::class)->findAll();

        $site = new Site();
        $form = $this->createForm(SiteType::class, $site);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($site);
            $this->entityManager->flush();

            $this->addFlash('success', 'Site créé avec succès.');
            return $this->redirectToRoute('admin_sites');
        }

        return $this->render('admin/sites.html.twig', [
            'sites' => $sites,
            'form' => $form->createView()
        ]);
    }

    #[Route('/sites/{id}/editer', name: 'admin_site_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function editSite(Request $request, Site $site): Response
    {
        $form = $this->createForm(SiteType::class, $site);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Site modifié avec succès.');
            return $this->redirectToRoute('admin_sites');
        }

        return $this->render('admin/site_edit.html.twig', [
            'form' => $form->createView(),
            'site' => $site
        ]);
    }

    #[Route('/sites/{id}/supprimer', name: 'admin_site_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function deleteSite(Site $site): Response
    {
        $this->entityManager->remove($site);
        $this->entityManager->flush();

        $this->addFlash('success', 'Site supprimé avec succès.');
        return $this->redirectToRoute('admin_sites');
    }

    // Gestion des Participants
    #[Route('/participants', name: 'admin_participants')]
    public function participants(Request $request): Response
    {
        $participants = $this->entityManager->getRepository(Participant::class)->findAll();

        // Formulaire d'ajout d'un nouveau participant
        $participant = new Participant();
        $form = $this->createForm(AdminParticipantType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Vérifier l'unicité du pseudo
                $existingPseudo = $this->entityManager->getRepository(Participant::class)
                    ->findOneBy(['pseudo' => $participant->getPseudo()]);
                if ($existingPseudo) {
                    $this->addFlash('error', 'Ce pseudo est déjà utilisé par un autre participant.');
                    return $this->render('admin/participants.html.twig', [
                        'participants' => $participants,
                        'form' => $form->createView()
                    ]);
                }

                // Vérifier l'unicité de l'email
                $existingEmail = $this->entityManager->getRepository(Participant::class)
                    ->findOneBy(['mail' => $participant->getMail()]);
                if ($existingEmail) {
                    $this->addFlash('error', 'Cette adresse email est déjà utilisée par un autre participant.');
                    return $this->render('admin/participants.html.twig', [
                        'participants' => $participants,
                        'form' => $form->createView()
                    ]);
                }

                // Hacher le mot de passe
                $plainPassword = $form->get('plainPassword')->getData();
                $hashedPassword = $this->passwordHasher->hashPassword($participant, $plainPassword);
                $participant->setMotPasse($hashedPassword);

                $this->entityManager->persist($participant);
                $this->entityManager->flush();

                $this->addFlash('success', 'Participant créé avec succès.');
                return $this->redirectToRoute('admin_participants');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la création du participant : ' . $e->getMessage());
            }
        }

        return $this->render('admin/participants.html.twig', [
            'participants' => $participants,
            'form' => $form->createView()
        ]);
    }

    #[Route('/participants/{id}/activer', name: 'admin_participant_activate', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function activateParticipant(Participant $participant): Response
    {
        $participant->setActif(true);
        $this->entityManager->flush();

        $this->addFlash('success', 'Participant activé avec succès.');
        return $this->redirectToRoute('admin_participants');
    }

    #[Route('/participants/{id}/desactiver', name: 'admin_participant_deactivate', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function deactivateParticipant(Participant $participant): Response
    {
        $participant->setActif(false);
        $this->entityManager->flush();

        $this->addFlash('success', 'Participant désactivé avec succès.');
        return $this->redirectToRoute('admin_participants');
    }

    #[Route('/participants/{id}/reinitialiser-mdp', name: 'admin_participant_reset_password', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function resetPassword(Participant $participant): Response
    {
        // Générer un nouveau mot de passe temporaire
        $temporaryPassword = bin2hex(random_bytes(8));
        $hashedPassword = $this->passwordHasher->hashPassword($participant, $temporaryPassword);
        $participant->setMotPasse($hashedPassword);

        $this->entityManager->flush();

        $this->addFlash('success', "Mot de passe réinitialisé. Nouveau mot de passe temporaire : $temporaryPassword");
        return $this->redirectToRoute('admin_participants');
    }

    // Gestion des Sorties
    #[Route('/sorties', name: 'admin_sorties')]
    public function sorties(): Response
    {
        $sorties = $this->entityManager->getRepository(Sortie::class)->findAll();

        return $this->render('admin/sorties.html.twig', [
            'sorties' => $sorties
        ]);
    }

    #[Route('/sorties/{id}/annuler', name: 'admin_sortie_cancel', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function cancelSortie(Sortie $sortie): Response
    {
        if ($this->sortieStateService->annulerSortie($sortie)) {
            $this->addFlash('success', 'Sortie annulée avec succès.');
        } else {
            $this->addFlash('error', 'Impossible d\'annuler cette sortie.');
        }

        return $this->redirectToRoute('admin_sorties');
    }
}