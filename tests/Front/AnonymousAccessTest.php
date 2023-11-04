<?php 

namespace App\Tests\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AnonymousAccessTest extends WebTestCase
{
    /**
     * GET routes to anonymous users
     * 
     * @dataProvider getUrls()
     */
    public function testPageGetIsForbidden($url)
    {
        $client = self::createClient();
        $client->request('GET', $url);

        $this->assertResponseStatusCodeSame('401');
    }

    public function getUrls()
    {
        yield['/api/front/cases/1'];
        yield['/api/front/profils/1'];
    }

    /**
     * PATCH routes to anonymous users
     *
     * @dataProvider patchUrls()
     */
    public function testPagePatch($url)
    {
        $client = self::createClient();
        $client->request('PATCH', $url);

        $this->assertResponseStatusCodeSame('401');
    }

    public function patchUrls()
    {
        yield['/api/front/profils/1/update'];
        yield['/api/front/cases/1/update'];
    }

    /**
     * POST routes to anonymous users, a user just can just see homepage and register
     *
     * @dataProvider postUrls()
     */
    public function testPagePost($url)
    {
        $client = self::createClient();
        $client->request('POST', $url);

        $this->assertResponseStatusCodeSame('401');
    }

    public function postUrls()
    {
        yield['/api/front/report/add'];
        yield['/api/front/proposals/add'];
    }
}