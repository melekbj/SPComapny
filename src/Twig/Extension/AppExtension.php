<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use App\Repository\CommandeRepository;
use DateTime;
use DateInterval;

class AppExtension extends AbstractExtension
{
    private $commandRepository;

    public function __construct(CommandeRepository $commandRepository)
    {
        $this->commandRepository = $commandRepository;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('oldCommandsCount', [$this, 'getOldCommandsCount']),
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
}
