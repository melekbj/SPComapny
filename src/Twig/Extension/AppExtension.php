<?php

namespace App\Twig\Extension;

use DateTime;
use DateInterval;
use Twig\TwigFunction;
use App\Repository\UserRepository;
use Twig\Extension\AbstractExtension;
use App\Repository\CommandeRepository;
use Symfony\Component\Security\Core\Security;

class AppExtension extends AbstractExtension
{
    private $commandRepository;
    private $userRepository;
    private $security;

    public function __construct(CommandeRepository $commandRepository, UserRepository $userRepository, Security $security)
    {
        $this->commandRepository = $commandRepository;
        $this->userRepository = $userRepository;
        $this->security = $security;
        
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('oldCommandsCount', [$this, 'getOldCommandsCount']),
            new TwigFunction('pendingUsersCount', [$this, 'getPendingUsers']),
            new TwigFunction('currentUser', [$this, 'getCurrentUser']),
        ];
    }

    public function getOldCommandsCount(): int
    {
        // Get commands older than 30 days
        $thirtyDaysAgo = new DateTime();
        $thirtyDaysAgo->sub(new DateInterval('P30D'));
        $oldCommands = $this->commandRepository->createQueryBuilder('c')
            ->where('c.date < :thirtyDaysAgo')
            ->andWhere('c.etat = :etat')
            ->setParameter('thirtyDaysAgo', $thirtyDaysAgo)
            ->setParameter('etat', 'pending')
            ->getQuery()
            ->getResult();

        // Count the number of old commands
        return count($oldCommands);
    }



    public function getPendingUsers(): int
    {
        // Get commands older than 30 days
        $pendingUsers = $this->userRepository->createQueryBuilder('c')
            ->Where('c.etat = :etat')
            ->setParameter('etat', 'pending')
            ->getQuery()
            ->getResult();

        // Count the number of old commands
        return count($pendingUsers);
    }


    public function getCurrentUser()
    {
        return $this->security->getUser();
    }



}
