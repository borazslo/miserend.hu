<?php

namespace App\Tests\ApplicationTests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\DataCollector\ValidatorDataCollector;

/**
 * @group application
 */
class UserControllerTest extends WebTestCase
{
    public function testRegistrationPageLoad(): void
    {
        $client = static::createClient();

        $client->request('GET', '/regisztracio');

        $this->assertResponseIsSuccessful();
    }

    /**
     * @return array<string, string>
     */
    private static function getValidRegistrationFormData(): array
    {
        return [
            'user[username]'    => 'tesztelek',
            'user[nickname]'    => 'ElekKecske',
            'user[fullName]' => 'Teszt Elek',
            'user[email]' => 'elek@miserend.hu',
            'user[terms]' => true,
            'user[question]' => 'MKPK',
        ];
    }

    public function testSendData(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/regisztracio');

        $buttonCrawlerNode = $crawler->selectButton('Regisztráció');

        $form = $buttonCrawlerNode->form();

        $client->enableProfiler();

        $client->submit($form, self::getValidRegistrationFormData());

        $this->assertResponseIsSuccessful();

        $profiler = $client->getProfile();

        /** @var ValidatorDataCollector $validatorDataCollector */
        $validatorDataCollector = $profiler->getCollector('validator');
        $this->assertSame(0, $validatorDataCollector->getViolationsCount());

        $users = $client->getContainer()->get(UserRepository::class)->findAll();

        $this->assertCount(1, $users);
        $user = $users[0];
        $this->assertSame('tesztelek', $user->getUsername());
        $this->assertSame('ElekKecske', $user->getNickname());
        $this->assertSame('Teszt Elek', $user->getFullName());
        $this->assertSame('elek@miserend.hu', $user->getEmail());
        $this->assertSame(['ROLE_USER'], $user->getRoles());
        $this->assertNotNull($user->getPasswordChangeHash());
        $this->assertNotNull($user->getPasswordChangeDeadline());
    }

    public function testWelcomeMailSending(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/regisztracio');
        $buttonCrawlerNode = $crawler->selectButton('Regisztráció');
        $form = $buttonCrawlerNode->form();
        $client->submit($form, self::getValidRegistrationFormData());

        $this->assertResponseIsSuccessful();
        $this->assertEmailCount(1);

        $email = $this->getMailerMessage();

        $users = $client->getContainer()->get(UserRepository::class)->findAll();
        $this->assertCount(1, $users);
        $user = $users[0];

        $this->assertEmailSubjectContains($email, 'Miserend - Regisztráció');
        $this->assertEmailHtmlBodyContains($email, $user->getPasswordChangeHash());
    }

}
