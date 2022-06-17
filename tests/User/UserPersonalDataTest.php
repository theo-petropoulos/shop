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
     * Tests in an User can update his infos
     * @test
     * @throws Exception
     */
    public function canUpdateInfos()
    {
        $user = new User();
        $user
            ->setFirstName("John")
            ->setLastName("Doe")
            ->setEmail(mt_rand(1, 999999) . "@gmail.com")
            ->setPhone("0102030405")
            ->setPassword("MOTDEPASSEENCLAIR")
            ->setCreationDate(new \DateTime("today"));

        $this->em->persist($user);
        $this->em->flush();

        $oldMail = $user->getEmail();

        $user
            ->setFirstName("Jane")
            ->setLastName("Dorian")
            ->setEmail("new_" . mt_rand(1, 999999) . "@gmail.com")
            ->setPhone("999999000");

        $this->assertEquals("Jane", $user->getFirstName());
        $this->assertEquals("Dorian", $user->getLastName());
        $this->assertEquals("999999000", $user->getPhone());
        $this->assertNotEquals($oldMail, $user->getEmail());

        $this->em->remove($user);
        $this->em->flush();
    }

    /**
     * Tests in an User cannot use and already used Email
     * @test
     * @throws Exception
     */
    public function cannotUseAlreadyUsedEmail()
    {
        $this->expectException(UniqueConstraintViolationException::class);

        $user = new User();
        $user
            ->setFirstName("John")
            ->setLastName("Doe")
            ->setEmail("gblfjvc@gmail.com")
            ->setPhone("0102030405")
            ->setPassword("MOTDEPASSEENCLAIR")
            ->setCreationDate(new \DateTime("today"));

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
