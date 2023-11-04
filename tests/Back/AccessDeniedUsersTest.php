<?php 

namespace App\Tests\Controller\Back;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AccessDeniedUsersTest extends WebTestCase
{
    /**
     * GET back routes to registered users
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
        // response must be forbidden access
        $this->assertResponseStatusCodeSame('403');
    }

    public function getUrls()
    {
        yield['/api/back/cases'];
        yield['/api/back/cases/1'];
        yield['/api/back/platforms'];
        yield['/api/back/platforms/1'];
        yield['/api/back/proposals'];
        yield['/api/back/proposals/1'];
        yield['/api/back/reporteds'];
        yield['/api/back/reporteds/1'];
        yield['/api/back/users'];
        yield['/api/back/users/1'];
    }
}