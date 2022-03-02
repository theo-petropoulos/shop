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

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->em = $kernel->getContainer()->get('doctrine')->getManager();
    }

    /** Test New Brands
     * @throws Exception
     */
    public function testNewBrands()
    {
        for ($i = 0; $i < 5; $i++) {
            $brand = new Brand();
            $brand->setName('Brand ' . $i);
            $brand->setDescription('Brand Description ' . $i);
            $brand->setActive(true);
            try {
                $this->em->persist($brand);
            } catch (Exception $e) {
                error_log($e->getMessage());
            }
            $this->em->flush();
        }
        $counter = $this->em->getRepository(Brand::class)->count([]);
        $this->assertEquals(10, $counter);
    }

    /** Test Delete Brands
     * @throws Exception
     */
    public function testDeleteBrands()
    {
        for ($i = 0; $i < 5; $i++) {
            $brand = $this->em->getRepository(Brand::class)->findOneBy([], ['id' => 'DESC']);
            try {
                $this->em->remove($brand);
            } catch (Exception $e) {
                error_log($e->getMessage());
            }
            $this->em->flush();
        }
        $counter = $this->em->getRepository(Brand::class)->count([]);
        $this->assertEquals(5, $counter);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->close();
    }
}