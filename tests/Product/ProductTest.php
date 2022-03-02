<?php

namespace App\Tests\Product;

use App\Entity\Brand;
use App\Entity\Product;
use Doctrine\ORM\EntityManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProductTest extends KernelTestCase
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

    /** Test New Products
     * @throws Exception
     */
    public function testNewProducts()
    {
        $brand = $this->em->getRepository(Brand::class)->findOneBy([], ['id' => 'DESC']);
        $counterInitial = $this->em->getRepository(Product::class)->count([]);
        for ($i = 0; $i < 10; $i++) {
            $product = new Product();
            $product
                ->setActive(true)
                ->setDescription("Description product " . $i)
                ->setName("Name product " . $i)
                ->setBrand($brand)
                ->setImagePath("/assets/products/images/toto.jpg")
                ->setPrice("45.95")
                ->setStock("25");
            try {
                $this->em->persist($product);
            } catch (Exception $e) {
                error_log($e->getMessage());
            }
            $this->em->flush();
        }
        $counterFinal = $this->em->getRepository(Product::class)->count([]);
        $this->assertEquals($counterInitial + 10, $counterFinal);
    }

    /** Test Delete Products
     * @throws Exception
     */
    public function testDeleteProducts()
    {
        $counterInitial = $this->em->getRepository(Product::class)->count([]);
        for ($i = 0; $i < 10; $i++) {
            $brand = $this->em->getRepository(Product::class)->findOneBy([], ['id' => 'DESC']);
            try {
                $this->em->remove($brand);
            } catch (Exception $e) {
                error_log($e->getMessage());
            }
            $this->em->flush();
        }
        $counterFinal = $this->em->getRepository(Product::class)->count([]);
        $this->assertEquals($counterInitial - 10, $counterFinal);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->close();
    }
}