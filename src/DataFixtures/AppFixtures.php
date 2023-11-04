<?php

namespace App\DataFixtures;

use DateTime;
use Faker\Factory;
use App\Entity\User;
use App\Entity\Platform;
use App\Entity\Proposal;
use App\Entity\Reported;
use App\Entity\CaseFolder;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ObjectManager;
use App\DataFixtures\Provider\LevaProvider;
use App\Entity\Screenshots;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    // On récupère la connexion DBAL pour exécuter des requêtes SQL (pour le TRUNCATE)
    private $connection;
    private $passwordHasher;
    private $pics = [
        'https://mediaorigcdna.qooq.com/cache/201911.1/qooq/images/ingredients/zoom_106.jpg',
        'https://lahalledes3abbayes.fr/wp-content/uploads/2021/03/Aubergine.jpg',
        'https://media.gerbeaud.net/2022/10/640/courgette-2.jpg'
    ];

    public function __construct(Connection $connection, UserPasswordHasherInterface $passwordHasher)
    {
        $this->connection = $connection;
        $this->passwordHasher = $passwordHasher;
    }
 
    /**
     * Allow to TRUNCATE the tables restart the AI from 1
     */
    private function truncate()
    {
        
        // Disable the checking for FK Constraints
        $this->connection->executeQuery('SET foreign_key_checks = 0');
        
        $this->connection->executeQuery('TRUNCATE TABLE proposal');
        $this->connection->executeQuery('TRUNCATE TABLE reported');
        $this->connection->executeQuery('TRUNCATE TABLE platform');
        $this->connection->executeQuery('TRUNCATE TABLE case_folder');
        $this->connection->executeQuery('TRUNCATE TABLE user');
        $this->connection->executeQuery('TRUNCATE TABLE screenshots');
    }

    public function load(ObjectManager $manager): void
    {
        $this->truncate();
        $faker = Factory::create('fr_FR');
        $faker->addProvider(new LevaProvider());



        // xxxx platform xxxx
        $numberOfPlatforms = count($faker->getPlatformList());

        $platformList = [];
        
        for ($i = 1; $i <= $numberOfPlatforms; $i++) {

            $platform = new Platform();
            $platform->setName($faker->unique()->platform())
                     ->setCreatedAt(new DateTime);
            
            $platformList[] = $platform;

            $manager->persist($platform);
        }



        // xxxx user xxxx

        $userList = [];

        for ($i = 1; $i <=20; $i++) {

            $user = new User();
            $pseudo = 'user_' . $i;
            $email = 'userEmail_' . $i . '@example.com';
    
            $user->setPseudo($pseudo)
                 ->setEmail($email)
                 ->setRole(['ROLE_USER'])
                 ->setCreatedAt(new DateTime);

                 $plainpassword = $pseudo;
                 $password = $this->passwordHasher->hashPassword($user, $plainpassword);
            
            $user->setPassword($password);
            
            $userList[] = $user;

            $manager->persist($user);
        }



        // xxxx reported xxxx
        
        $reportedList = [];
        
        for ($i = 1; $i <= 10; $i++) {

            $platform = $platformList[mt_rand(0, count($platformList) - 1)];
            
            $reported = new Reported();
            $pseudo = 'reported_' . $i;
    
            $reported->setReportedPseudo($pseudo)
                     ->setReportedNumber($faker->numberbetween(1,2))
                     ->setCreatedAt(new Datetime)
                     // added for mode consistence, a reported got a platform, then, pickup his platform to fill a caseFolder
                     ->setPlatform($platform);

            $reportedList[] = $reported;

            $manager->persist($reported);
        }

        

        // xxxx caseFolder xxxx

        for ($i = 1; $i <= 10; $i++) {

            $caseFolder = new caseFolder();

            $lorem = $faker->paragraph();

            $user = $userList[mt_rand(0, count($userList) - 1)];

            $reported = $reportedList[mt_rand(0, count($reportedList) - 1)];

            $status = $faker->getStatus();

            $caseFolderId = $i;

            $reference = $user->getPseudo() . '-' . $reported->getReportedPseudo() . '-' . $reported->getPlatform()->getName() . '-' . $caseFolderId;

            
            $caseFolder->setContent($lorem)
                       ->setReference($reference)
                       ->setCreatedAt(new DateTime)
                       ->setUser($user)
                       ->setReported($reported)
                       // added for mode consistence, a reported got a platform, then, pickup his platform to fill a caseFolder
                       ->setPlatform($reported->getPlatform())
                       ->setStatus([$faker->randomElement($status)]);


                $manager->persist($caseFolder);

                for ($u = 1; $u <= random_int(1, 2); $u++) {
                     $screenshot = new Screenshots();
                     $screenshot->setName($this->pics[mt_rand(0, count($this->pics) - 1)])
                                ->setCaseFolder($caseFolder);

                    $manager->persist($screenshot);
                }
        }

        
        // xxxx proposal xxxx

        for ($i = 1; $i <= 10; $i++) {

            $proposal = new Proposal();
            $lorem = $faker->paragraph();
            $user = $userList[mt_rand(0, count($userList) - 1)];

            $proposal->setContent('je suggère que' . $lorem)
                     ->setUser($user)
                     ->setCreatedAt(new DateTime);

            $manager->persist($proposal);
        }

        $manager->flush();
    }
}
