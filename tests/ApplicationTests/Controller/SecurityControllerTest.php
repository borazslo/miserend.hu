<?php

namespace ApplicationTests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\DataCollector\ValidatorDataCollector;

/**
 * @group application
 */
class SecurityControllerTest extends WebTestCase
{
    public function testRegistrationPageLoad(): void
    {
        $client = static::createClient();

        $client->request('GET', '/bejelentkezes');

        $this->assertResponseIsSuccessful();
    }
}
