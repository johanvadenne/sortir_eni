<?php

namespace App\Command;

use App\Entity\Sortie;
use App\Service\SortieStateService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:sortie:tick',
    description: 'Traite les transitions automatiques des sorties',
)]
class SortieTickCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SortieStateService $sortieStateService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Affiche les actions sans les exécuter')
            ->addOption('details', 'd', InputOption::VALUE_NONE, 'Affiche les détails des opérations');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = $input->getOption('dry-run');
        $details = $input->getOption('details');

        $io->title('Traitement des transitions automatiques des sorties');

        $now = new \DateTime();
        $io->text("Heure actuelle : {$now->format('d/m/Y H:i:s')}");

        $transitions = [
            'cloturee' => 0,
            'en_cours' => 0,
            'terminee' => 0,
            'historisee' => 0
        ];

        $errors = [];

        // 1. Traiter les sorties "Ouverte" → "Clôturée"
        $transitions['cloturee'] = $this->processOuverteToCloturee($io, $dryRun, $details, $errors);

        // 2. Traiter les sorties "Clôturée" → "Activité en cours"
        $transitions['en_cours'] = $this->processClotureeToEnCours($io, $dryRun, $details, $errors);

        // 3. Traiter les sorties "Activité en cours" → "Activité terminée"
        $transitions['terminee'] = $this->processEnCoursToTerminee($io, $dryRun, $details, $errors);

        // 4. Traiter les sorties "Activité terminée" → "Activité historisée"
        $transitions['historisee'] = $this->processTermineeToHistorisee($io, $dryRun, $details, $errors);

        // Affichage du résumé
        $io->section('Résumé des transitions');
        $io->table(
            ['Transition', 'Nombre'],
            [
                ['Ouverte → Clôturée', $transitions['cloturee']],
                ['Clôturée → Activité en cours', $transitions['en_cours']],
                ['Activité en cours → Activité terminée', $transitions['terminee']],
                ['Activité terminée → Activité historisée', $transitions['historisee']],
            ]
        );

        $totalTransitions = array_sum($transitions);
        $io->success("$totalTransitions transitions automatiques traitées");

        if (!empty($errors)) {
            $io->warning('Erreurs rencontrées :');
            foreach ($errors as $error) {
                $io->text("• $error");
            }
        }

        return Command::SUCCESS;
    }

    private function processOuverteToCloturee(SymfonyStyle $io, bool $dryRun, bool $details, array &$errors): int
    {
        $io->section('1. Traitement : Ouverte → Clôturée');

        $sorties = $this->entityManager->getRepository(Sortie::class)
            ->createQueryBuilder('s')
            ->join('s.etat', 'e')
            ->where('e.libelle = :etat')
            ->setParameter('etat', 'Ouverte')
            ->getQuery()
            ->getResult();

        $count = 0;

        foreach ($sorties as $sortie) {
            $shouldCloture = false;
            $reason = '';

            // Vérifier si le nombre max d'inscriptions est atteint
            if ($sortie->isComplete()) {
                $shouldCloture = true;
                $reason = 'Nombre max d\'inscriptions atteint';
            }
            // Vérifier si la date limite d'inscription est dépassée
            elseif ($sortie->getDateLimiteInscription() < new \DateTime()) {
                $shouldCloture = true;
                $reason = 'Date limite d\'inscription dépassée';
            }

            if ($shouldCloture) {
                if ($details) {
                    $io->text("• {$sortie->getNom()} : $reason");
                }

                if (!$dryRun) {
                    try {
                        if ($this->sortieStateService->cloturerInscriptions($sortie)) {
                            $count++;
                        } else {
                            $errors[] = "Impossible de clôturer la sortie : {$sortie->getNom()}";
                        }
                    } catch (\Exception $e) {
                        $errors[] = "Erreur lors de la clôture de {$sortie->getNom()} : " . $e->getMessage();
                    }
                } else {
                    $count++;
                }
            }
        }

        $io->text("Sorties à clôturer : $count");
        return $count;
    }

    private function processClotureeToEnCours(SymfonyStyle $io, bool $dryRun, bool $details, array &$errors): int
    {
        $io->section('2. Traitement : Clôturée → Activité en cours');

        $sorties = $this->entityManager->getRepository(Sortie::class)
            ->createQueryBuilder('s')
            ->join('s.etat', 'e')
            ->where('e.libelle = :etat')
            ->setParameter('etat', 'Clôturée')
            ->getQuery()
            ->getResult();

        $count = 0;
        $now = new \DateTime();

        foreach ($sorties as $sortie) {
            if ($sortie->getDateHeureDebut() <= $now) {
                if ($details) {
                    $io->text("• {$sortie->getNom()} : Date de début atteinte");
                }

                if (!$dryRun) {
                    try {
                        if ($this->sortieStateService->demarrerSortie($sortie)) {
                            $count++;
                        } else {
                            $errors[] = "Impossible de démarrer la sortie : {$sortie->getNom()}";
                        }
                    } catch (\Exception $e) {
                        $errors[] = "Erreur lors du démarrage de {$sortie->getNom()} : " . $e->getMessage();
                    }
                } else {
                    $count++;
                }
            }
        }

        $io->text("Sorties à démarrer : $count");
        return $count;
    }

    private function processEnCoursToTerminee(SymfonyStyle $io, bool $dryRun, bool $details, array &$errors): int
    {
        $io->section('3. Traitement : Activité en cours → Activité terminée');

        $sorties = $this->entityManager->getRepository(Sortie::class)
            ->createQueryBuilder('s')
            ->join('s.etat', 'e')
            ->where('e.libelle = :etat')
            ->setParameter('etat', 'Activité en cours')
            ->getQuery()
            ->getResult();

        $count = 0;
        $now = new \DateTime();

        foreach ($sorties as $sortie) {
            $shouldTerminate = false;
            $reason = '';

            if ($sortie->getDuree() !== null) {
                // Calculer la fin de la sortie
                $finSortie = clone $sortie->getDateHeureDebut();
                $finSortie->modify("+{$sortie->getDuree()} minutes");

                if ($now >= $finSortie) {
                    $shouldTerminate = true;
                    $reason = "Durée écoulée ({$sortie->getDuree()} minutes)";
                }
            } else {
                // Si pas de durée définie, on ne termine pas automatiquement
                continue;
            }

            if ($shouldTerminate) {
                if ($details) {
                    $io->text("• {$sortie->getNom()} : $reason");
                }

                if (!$dryRun) {
                    try {
                        if ($this->sortieStateService->terminerSortie($sortie)) {
                            $count++;
                        } else {
                            $errors[] = "Impossible de terminer la sortie : {$sortie->getNom()}";
                        }
                    } catch (\Exception $e) {
                        $errors[] = "Erreur lors de la fin de {$sortie->getNom()} : " . $e->getMessage();
                    }
                } else {
                    $count++;
                }
            }
        }

        $io->text("Sorties à terminer : $count");
        return $count;
    }

    private function processTermineeToHistorisee(SymfonyStyle $io, bool $dryRun, bool $details, array &$errors): int
    {
        $io->section('4. Traitement : Activité terminée → Activité historisée');

        $sorties = $this->entityManager->getRepository(Sortie::class)
            ->createQueryBuilder('s')
            ->join('s.etat', 'e')
            ->where('e.libelle = :etat')
            ->setParameter('etat', 'Activité terminée')
            ->getQuery()
            ->getResult();

        $count = 0;
        $now = new \DateTime();

        foreach ($sorties as $sortie) {
            // Calculer la date d'archivage (1 mois après la date de début)
            $dateArchivage = clone $sortie->getDateHeureDebut();
            $dateArchivage->modify('+1 month');

            if ($now >= $dateArchivage) {
                if ($details) {
                    $io->text("• {$sortie->getNom()} : 1 mois écoulé depuis la date de début");
                }

                if (!$dryRun) {
                    try {
                        if ($this->sortieStateService->historiserSortie($sortie)) {
                            $count++;
                        } else {
                            $errors[] = "Impossible d'historiser la sortie : {$sortie->getNom()}";
                        }
                    } catch (\Exception $e) {
                        $errors[] = "Erreur lors de l'historisation de {$sortie->getNom()} : " . $e->getMessage();
                    }
                } else {
                    $count++;
                }
            }
        }

        $io->text("Sorties à historiser : $count");
        return $count;
    }
}
