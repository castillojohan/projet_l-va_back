<?php 

namespace App\Tests\Controller\Back;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AccessDeniedAnonymousTest extends WebTestCase
{
    /**
     * GET back routes to unregistered users
     * 
     * @dataProvider getUrls()
     */
    public function testPageGet($url)
    {
        $client = static::createClient();

        $client->request('GET', $url);
        //response must be 401 JWT token not found or forbidden access
        $this->assertResponseStatusCodeSame('401');
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