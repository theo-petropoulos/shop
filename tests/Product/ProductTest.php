<?php

namespace App\Tests\Product;

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

    /** Test New Product
     * @throws Exception
     */
    public function testNewProducts()
    {
        for ($i = 0; $i < 10; $i++) {
            $product = new Product();
            try {
                $this->em->persist($product);
            } catch (Exception $e) {
                error_log($e->getMessage());
            }
            $this->em->flush();
        }
        $counter = $this->em->getRepository(Product::class)->count([]);
        $this->assertEquals(10, $counter);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->close();
        $this->em = null;
    }
}