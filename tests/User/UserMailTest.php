<?php

namespace App\Tests\User;

use App\Entity\User;
use App\Exceptions\InvalidEmailException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserMailTest extends KernelTestCase
{
    /**
     * @var EntityManager
     */
    private EntityManager $em;

    /**
     * Set up the kernel
     * @test
     * @return void
     */
    protected function setUp(): void
    {
        $kernel     = self::bootKernel();
        $this->em   = $kernel->getContainer()->get('doctrine')->getManager();
    }

    /**
     * Test creating an User with an already used mail
     * @test
     * @throws Exception
     */
    public function alreayUsedMail()
    {
        $this->expectException(UniqueConstraintViolationException::class);
        $registeredUser = $this->em->getRepository(User::class)->findOneBy([], ['id' => 'DESC']);
        $mail = $registeredUser->getEmail();
        $user = new User();
        $user
            ->setCreationDate(new \DateTime("today"))
            ->setEmail($mail)
            ->setFirstName("Patrick")
            ->setLastName("Jane")
            ->setPassword("h4sh3dp4ssw0rd")
            ->setPhone("0123456789")
            ->setRoles(["ROLE_USER"]);
        $this->em->persist($user);
        $this->em->flush();
    }

    /**
     * Test creating an User with an invalid email
     * @test
     * @throws Exception
     */
    public function invalidMail()
    {
        $this->expectException(InvalidEmailException::class);
        $user = new User();
        $user
            ->setCreationDate(new \DateTime("today"))
            ->setEmail("toto")
            ->setFirstName("Patrick")
            ->setLastName("Jane")
            ->setPassword("h4sh3dp4ssw0rd")
            ->setPhone("0123456789")
            ->setRoles(["ROLE_USER"]);
        $this->em->persist($user);
        $this->em->flush();
    }

    /**
     * Close kernel
     * @test
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->close();
    }
}
