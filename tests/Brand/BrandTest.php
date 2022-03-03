<?php

namespace App\Tests\Brand;

use App\Entity\Brand;
use Doctrine\ORM\EntityManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BrandTest extends KernelTestCase
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

    /** Test adding new Brands to the database
     * @test
     * @throws Exception
     */
    public function createNewBrands()
    {
        $counterInitial = $this->em->getRepository(Brand::class)->count([]);
        for ($i = 0; $i < 5; $i++) {
            $brand = new Brand();
            $brand
                ->setName('Brand ' . $i)
                ->setDescription('Brand Description ' . $i)
                ->setActive(true);
            try {
                $this->em->persist($brand);
            } catch (Exception $e) {
                error_log($e->getMessage());
            }
            $this->em->flush();
        }
        $counterFinal = $this->em->getRepository(Brand::class)->count([]);
        $this->assertEquals($counterInitial + 5, $counterFinal);
    }

    /** Test deleting newly added Brands from the database
     * @test
     * @throws Exception
     */
    public function deleteNewlyCreatedBrands()
    {
        $counterInitial = $this->em->getRepository(Brand::class)->count([]);
        for ($i = 0; $i < 5; $i++) {
            $brand = $this->em->getRepository(Brand::class)->findOneBy([], ['id' => 'DESC']);
            try {
                $this->em->remove($brand);
            } catch (Exception $e) {
                error_log($e->getMessage());
            }
            $this->em->flush();
        }
        $counterFinal = $this->em->getRepository(Brand::class)->count([]);
        $this->assertEquals($counterInitial - 5, $counterFinal);
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
