<?php

namespace App\Twig;

use DateTime;
use DateInterval;
use Twig\TwigFunction;
use App\Repository\UserRepository;
use Twig\Extension\AbstractExtension;
use App\Repository\CommandeRepository;

class AppExtension extends AbstractExtension
{
    private $commandRepository;
    private $userRepository;

    public function __construct(CommandeRepository $commandRepository, UserRepository $userRepository)
    {
        $this->commandRepository = $commandRepository;
        $this->userRepository = $userRepository;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('oldCommandsCount', [$this, 'getOldCommandsCount']),
            new TwigFunction('pendingUsersCount', [$this, 'getPendingUsers']),
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




}
