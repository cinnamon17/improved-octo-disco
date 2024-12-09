<?php

namespace App\Tests\Integration\Repository;

use App\Entity\Prompt;
use App\Repository\PromptRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PromptRepositoryTest extends KernelTestCase
{
    private ?EntityManager $entityManager;
    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }
    public function testGetPromptRepository(): void
    {
        self::bootKernel();
        $promptRepository = static::getContainer()->get(PromptRepository::class);

        $prompt = new Prompt();
        $prompt->setLanguage('es');
        $prompt->setRole('asistente');
        $prompt->setMessage('hello');

        $this->entityManager->persist($prompt);
        $this->entityManager->flush();

        $result = $promptRepository->findOneBy(['role' => 'asistente', 'language' => 'es']);
        $this->assertInstanceOf(Prompt::class, $result);
    }


    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
