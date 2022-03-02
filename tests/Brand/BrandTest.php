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
        $counterInitial = $this->em->getRepository(Brand::class)->count([]);
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
        $counterFinal = $this->em->getRepository(Brand::class)->count([]);
        $this->assertEquals($counterInitial + 5, $counterFinal);
    }

    /** Test Delete Brands
     * @throws Exception
     */
    public function testDeleteBrands()
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

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->close();
    }
}