<?php

namespace App\Tests\User;

use App\Entity\Product;
use App\Entity\User;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use SebastianBergmann\Diff\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserCreationTest extends KernelTestCase
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

    /** Test adding new Users to the database
     * @test
     * @throws Exception
     */
    public function createNewUsers()
    {
        $counterInitial = $this->em->getRepository(User::class)->count([]);
        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user
                ->setCreationDate(new \DateTime("today"))
                ->setEmail(random_int(1, 555555555) . "@" . random_int(1, 555555555) . ".com")
                ->setFirstName("Patrick")
                ->setLastName("Jane")
                ->setPassword("h4sh3dp4ssw0rd")
                ->setPhone("0123456789")
                ->setRoles(["ROLE_USER"]);
            $this->em->persist($user);
        }
        $this->em->flush();
        $counterFinal = $this->em->getRepository(User::class)->count([]);
        $this->assertEquals($counterInitial + 10, $counterFinal);
    }

    /**
     * Test creating an User with an already used mail
     * @test
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

    /** Test deleting newly added Users from the database
     * @test
     */
    public function deleteNewlyCreatedUsers()
    {
        $counterInitial = $this->em->getRepository(User::class)->count([]);
        for ($i = 0; $i < 10; $i++) {
            $user = $this->em->getRepository(User::class)->findOneBy([], ['id' => 'DESC']);
            $this->em->remove($user);
            $this->em->flush();
        }
        $counterFinal = $this->em->getRepository(User::class)->count([]);
        $this->assertEquals($counterInitial - 10, $counterFinal);
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
