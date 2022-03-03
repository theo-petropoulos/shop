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

class UserPersonalDataTest extends KernelTestCase
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
     * Test using an invalid firstname
     * @test
     * @throws Exception
     */
    public function invalidFirstName()
    {
        $this->expectException(\InvalidArgumentException::class);
        $user = new User();
        $user
            ->setCreationDate(new \DateTime("today"))
            ->setFirstName("Pa + trick");
        $this->em->persist($user);
        $this->em->flush();
    }

    /**
     * Test using an invalid lastname
     * @test
     * @throws Exception
     */
    public function invalidLastName()
    {
        $this->expectException(\InvalidArgumentException::class);
        $user = new User();
        $user
            ->setCreationDate(new \DateTime("today"))
            ->setLastName("Pa + trick");
        $this->em->persist($user);
        $this->em->flush();
    }

    /**
     * Test using an invalid phone number
     * @test
     * @throws Exception
     */
    public function invalidPhone()
    {
        $this->expectException(\InvalidArgumentException::class);
        $user = new User();
        $user
            ->setCreationDate(new \DateTime("today"))
            ->setPhone("01(23456789");
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
