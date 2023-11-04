<?php 

namespace App\Tests\Controller\Back;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AccessGrantedAdminTest extends WebTestCase
{
    /**
     * GET back routes to admin
     * 
     * @dataProvider getUrls()
     */
    public function testPageGet($url)
    {
        $client = static::createClient();
        $userRepo = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepo->findOneByEmail('admin@admin.com');
        $client->loginUser($testUser);

        $client->request('GET', $url);
        // response must be a 200
        $this->assertResponseStatusCodeSame('200');
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