<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ProfilType;
use App\Form\ChangePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/profil')]
#[IsGranted('ROLE_USER')]
class ProfilController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private SluggerInterface $slugger
    ) {
    }

    #[Route('/', name: 'profil_show')]
    public function show(): Response
    {
        $participant = $this->getUser();

        return $this->render('profil/show.html.twig', [
            'participant' => $participant
        ]);
    }

    #[Route('/editer', name: 'profil_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request): Response
    {
        $participant = $this->getUser();
        $form = $this->createForm(ProfilType::class, $participant);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload de la photo de profil
            $photoFile = $form->get('photoProfil')->getData();

            if ($photoFile) {
                // Validation de l'extension du fichier (sans dépendre de fileinfo)
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $originalFilename = $photoFile->getClientOriginalName();
                $fileExtension = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION));

                if (!in_array($fileExtension, $allowedExtensions)) {
                    $this->addFlash('error', 'Format de fichier non supporté. Veuillez utiliser JPG, PNG, GIF ou WebP.');
                    return $this->render('profil/edit.html.twig', [
                        'form' => $form->createView(),
                        'participant' => $participant
                    ]);
                }

                // Validation de la taille du fichier (2MB max)
                if ($photoFile->getSize() > 2 * 1024 * 1024) {
                    $this->addFlash('error', 'Le fichier est trop volumineux. La taille maximale autorisée est de 2MB.');
                    return $this->render('profil/edit.html.twig', [
                        'form' => $form->createView(),
                        'participant' => $participant
                    ]);
                }
                // Supprimer l'ancienne photo si elle existe
                if ($participant->getPhotoProfil()) {
                    $oldPhotoPath = $this->getParameter('kernel.project_dir') . '/public' . $participant->getPhotoProfil();
                    if (file_exists($oldPhotoPath)) {
                        unlink($oldPhotoPath);
                    }
                }

                // Générer un nom de fichier unique
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $fileExtension;

                // Déplacer le fichier vers le répertoire de stockage
                $photoFile->move(
                    $this->getParameter('kernel.project_dir') . '/public/uploads/profiles',
                    $newFilename
                );

                // Mettre à jour le chemin de la photo dans l'entité
                $participant->setPhotoProfil('/uploads/profiles/' . $newFilename);
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');

            return $this->redirectToRoute('profil_show');
        }

        return $this->render('profil/edit.html.twig', [
            'form' => $form->createView(),
            'participant' => $participant
        ]);
    }

    #[Route('/changer-mot-de-passe', name: 'profil_change_password', methods: ['GET', 'POST'])]
    public function changePassword(Request $request): Response
    {
        $participant = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Vérifier l'ancien mot de passe
            if (!$this->passwordHasher->isPasswordValid($participant, $data['oldPassword'])) {
                $this->addFlash('error', 'L\'ancien mot de passe est incorrect.');
                return $this->render('profil/change_password.html.twig', [
                    'form' => $form->createView()
                ]);
            }

            // Hasher le nouveau mot de passe
            $hashedPassword = $this->passwordHasher->hashPassword($participant, $data['newPassword']);
            $participant->setMotPasse($hashedPassword);

            $this->entityManager->flush();

            $this->addFlash('success', 'Votre mot de passe a été modifié avec succès.');

            return $this->redirectToRoute('profil_show');
        }

        return $this->render('profil/change_password.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/{id}', name: 'profil_show_other', requirements: ['id' => '\d+'])]
    public function showOther(Participant $participant): Response
    {
        return $this->render('profil/show_other.html.twig', [
            'participant' => $participant
        ]);
    }
}
