<?php

namespace App\Tests\Address;

use App\Entity\Address;
use App\Entity\User;
use App\Exceptions\InvalidSizeException;
use Doctrine\ORM\EntityManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AddressCreationTest extends KernelTestCase
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
     * Test adding Addresses to existing Users to the database
     * @test
     * @throws Exception
     */
    public function addNewAddresses()
    {
        $counterInitial = $this->em->getRepository(Address::class)->count([]);
        $users          = $this->em->getRepository(User::class)->findAll();
        foreach ($users as $user) {
            $address = new Address();
            $address
                ->setCustomer($user)
                ->setFirstName($user->getFirstName())
                ->setLastName($user->getLastName())
                ->setCity("Marseille")
                ->setPostalCode("45000")
                ->setStreetNumber("3")
                ->setStreetName("rue des cerisiers")
                ->setMain(true);
            $this->em->persist($address);
        }
        $this->em->flush();
        $counterFinal = $this->em->getRepository(Address::class)->count([]);
        $this->assertEquals($counterInitial + count($users), $counterFinal);
    }

    /**
     * Test setting an invalid postal code
     * @test
     * @throws Exception
     */
    public function invalidTypePostalCode()
    {
        $this->expectException(\TypeError::class);
        $address    = new Address();
        $user       = $this->em->getRepository(User::class)->findOneBy([], ['id' => 'DESC']);
        $address
            ->setCustomer($user)
            ->setFirstName($user->getFirstName())
            ->setLastName($user->getLastName())
            ->setCity("Marseille")
            ->setPostalCode("45 000")
            ->setStreetNumber("3")
            ->setStreetName("rue des cerisiers")
            ->setMain(true);
        $this->em->persist($address);
        $this->em->flush();
    }

    /**
     * Test setting an invalid postal code
     * @test
     * @throws Exception
     */
    public function invalidSizePostalCode()
    {
        $this->expectException(InvalidSizeException::class);
        $address    = new Address();
        $user       = $this->em->getRepository(User::class)->findOneBy([], ['id' => 'DESC']);
        $address
            ->setCustomer($user)
            ->setFirstName($user->getFirstName())
            ->setLastName($user->getLastName())
            ->setCity("Marseille")
            ->setPostalCode("455000")
            ->setStreetNumber("3")
            ->setStreetName("rue des cerisiers")
            ->setMain(true);
        $this->em->persist($address);
        $this->em->flush();
    }

    /**
     * Test deleting newly added Addresses from the database
     * @test
     * @throws Exception
     */
    public function deleteNewlyAddedAddresses()
    {
        $counterInitial = $this->em->getRepository(Address::class)->count([]);
        $users          = $this->em->getRepository(User::class)->findAll();
        foreach ($users as $user) {
            $address = $this->em->getRepository(Address::class)->findBy(["customer" => $user], ['id' => 'DESC'], 1, 0);
            $this->em->remove($address[0]);
            $this->em->flush();
        }
        $counterFinal = $this->em->getRepository(Address::class)->count([]);
        $this->assertEquals($counterInitial - count($users), $counterFinal);
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
