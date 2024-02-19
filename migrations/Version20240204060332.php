<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Repository\UserRepository;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Contracts\Service\Attribute\SubscribedService;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240204060332 extends AbstractMigration implements ServiceSubscriberInterface
{
    use ServiceSubscriberTrait;

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD roles longtext NOT NULL COMMENT \'(DC2Type:simple_array)\' AFTER jogok');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
// TODO meg kell varni ami ez jo lesz: https://github.com/doctrine/DoctrineMigrationsBundle/issues/521
//    public function postUp(Schema $schema): void
//    {
//        $repository = $this->getUserRepository();
//        foreach ($repository->findAll() as $user) {
//            $repository->migrateOldRolesToNew($user);
//        }
//
//        $repository->flush();
//    }
//
//    #[SubscribedService]
//    public function getUserRepository(): UserRepository
//    {
//        return $this->container->get(__METHOD__);
//    }
}
