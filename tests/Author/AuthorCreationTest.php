<?php

namespace App\Tests\Author;

use App\Entity\Author;
use Doctrine\ORM\EntityManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AuthorCreationTest extends KernelTestCase
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

    /** Test adding new Authors to the database
     * @test
     * @throws Exception
     */
    public function createNewAuthors()
    {
        $counterInitial = $this->em->getRepository(Author::class)->count([]);
        for ($i = 0; $i < 5; $i++) {
            $author = new Author();
            $author
                ->setName('Author ' . $i)
                ->setDescription('Author Description ' . $i)
                ->setActive(true);
            try {
                $this->em->persist($author);
            } catch (Exception $e) {
                error_log($e->getMessage());
            }
            $this->em->flush();
        }
        $counterFinal = $this->em->getRepository(Author::class)->count([]);
        $this->assertEquals($counterInitial + 5, $counterFinal);
    }

    /** Test deleting newly added Authors from the database
     * @test
     * @throws Exception
     */
    public function deleteNewlyCreatedAuthors()
    {
        $counterInitial = $this->em->getRepository(Author::class)->count([]);
        for ($i = 0; $i < 5; $i++) {
            $author = $this->em->getRepository(Author::class)->findOneBy([], ['id' => 'DESC']);
            try {
                $this->em->remove($author);
            } catch (Exception $e) {
                error_log($e->getMessage());
            }
            $this->em->flush();
        }
        $counterFinal = $this->em->getRepository(Author::class)->count([]);
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
