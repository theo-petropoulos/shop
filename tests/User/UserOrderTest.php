<?php

namespace App\Tests\User;

use App\Entity\Order;
use App\Entity\OrderDetail;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserOrderTest extends KernelTestCase
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
     * Test if an User can place an Order
     * @test
     * @throws Exception
     */
    public function userPlaceOrder()
    {
        $counterInitial = $this->em->getRepository(Order::class)->count([]);
        $user           = $this->em->getRepository(User::class)->findOneBy([], ['id' => 'DESC']);
        $products       = $this->em->getRepository(Product::class)->findBy([], [], 5, 0);

        $order          = new Order();
        $order
            ->setCustomer($user)
            ->setAddress($user->getAddresses()[0])
            ->setPurchaseDate(new \DateTime("today"))
            ->setStatus(Order::STATUS_PENDING);

        $this->em->persist($order);
        $this->em->flush();

        foreach ($products as $product) {
            $orderDetail    = new OrderDetail();
            $quantity       = random_int(1, 10);
            $orderDetail
                ->setProduct($product)
                ->setProductQuantity($quantity)
                ->setTotal($quantity * $product->getPrice())
                ->setOrder($order);

            $this->em->persist($orderDetail);
        }
        $this->em->flush();

        $counterFinal = $this->em->getRepository(Order::class)->count([]);
        $this->assertEquals($counterInitial + 1, $counterFinal);
    }

    /**
     * Test if an Address associated with an Order cannot be deleted
     * @test
     * @throws Exception
     */
    public function cannotDeleteOrderAddress()
    {
        $this->expectException(ForeignKeyConstraintViolationException ::class);

        /** @var Order $order */
        $order          = $this->em->getRepository(Order::class)->findOneBy([], ['id' => 'DESC']);
        $address        = $order->getAddress();

        $this->em->remove($address);
        $this->em->flush();
    }

    /**
     * Test if an Order is successfully deleted
     * @test
     * @throws Exception
     */
    public function deleteUserOrder()
    {
        $counterInitial = $this->em->getRepository(Order::class)->count([]);
        $order          = $this->em->getRepository(Order::class)->findOneBy([], ['id' => 'DESC']);
        $orderDetails   = $this->em->getRepository(OrderDetail::class)->findBy(['order' => $order]);

        foreach ($orderDetails as $detail) {
            $this->em->remove($detail);
        }
        $this->em->flush();

        $this->em->remove($order);
        $this->em->flush();

        $counterFinal = $this->em->getRepository(Order::class)->count([]);
        $this->assertEquals($counterInitial - 1, $counterFinal);
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
