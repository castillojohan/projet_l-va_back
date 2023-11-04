<?php 

namespace App\Tests\Controller\Front;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserAccessTest extends WebTestCase
{
    /**
     * GET routes to registered users
     * 
     * @dataProvider getUrls()
     */
    public function testPageGet($url)
    {
        $client = static::createClient();
        $userRepo = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepo->findOneByEmail('userEmail_1@example.com');
        $client->loginUser($testUser);

        $client->request('GET', $url);

        $this->assertResponseStatusCodeSame('200');
    }

    public function getUrls()
    {
        yield['/api/front/cases/1'];
        yield['/api/front/profils/1'];
    }
}