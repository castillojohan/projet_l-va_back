<?php 

namespace App\Service;

use App\Entity\Reported;
use App\Repository\PlatformRepository;
use App\Repository\ReportedRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class ReportedService
{
    private $entityManager;
    private $reportedRepository;
    private $platformRepository;

    public function __construct(ReportedRepository $reportedRepository, PlatformRepository $platformRepository, EntityManagerInterface $entityManager)
    {
        $this->reportedRepository = $reportedRepository;
        $this->platformRepository = $platformRepository;
        $this->entityManager = $entityManager;
    }

     /**
     * Finds or creates a reported entity based on the reported pseudo and platform.
     *
     * This method searches for an existing reported entity with the given pseudo. If one doesn't exist
     * or if the platform differs, it creates a new reported entity and associates it with the platform.
     * The reported count is incremented, and the changes are persisted and flushed to the database.
     *
     * @param string $reportedPseudo The reported pseudo.
     * @param int $reportedPlatform The ID of the reported platform.
     * 
     * @return Reported|null 
     */
    public function findOrCreateReported($reportedPseudo, $reportedPlatform)
    {
        $platform = $this->platformRepository->find($reportedPlatform);

        if($reportedPseudo === ''){
            return null;
        }
        $reported = $this->reportedRepository->findOneBy(['reportedPseudo' => $reportedPseudo]);
        
        if($reported === null || $reported->getPlatform() !== $platform){
            $reported = new Reported();
            $reported->setReportedPseudo($reportedPseudo);
            $reported->setPlatform($platform);
        }
        
        $reported->incrementReportedNbr();
        $this->entityManager->persist($reported);
        $this->entityManager->flush();
        
        return $reported;
    }
}