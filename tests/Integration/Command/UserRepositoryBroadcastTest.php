<?php

namespace App\Tests\Integration\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * El iterador de broadcast tiene que recorrer todos los usuarios sin
 * duplicados y sin saltarse ninguno entre lotes, y permitir reanudar por id.
 */
class UserRepositoryBroadcastTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private UserRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->em = $container->get('doctrine')->getManager();
        $this->repository = $container->get(UserRepository::class);
    }

    public function testIteraTodosLosUsuariosSinDuplicados(): void
    {
        $marker = random_int(100000, 999999);
        $created = [];

        for ($i = 0; $i < 7; $i++) {
            $user = (new User())
                ->setChatId(500000000 + $marker * 10 + $i)
                ->setFirstName('BC' . $marker)
                ->setIsBot(false);

            $this->em->persist($user);
            $created[] = $user;
        }

        $this->em->flush();
        $this->em->clear();

        $seen = [];
        foreach ($this->repository->iterateForBroadcast(3) as $user) {
            if ($user->getFirstName() === 'BC' . $marker) {
                $seen[] = $user->getId();
            }
        }

        $this->assertCount(7, $seen, 'Los 7 usuarios deben aparecer exactamente una vez');
        $this->assertSame($seen, array_unique($seen), 'No puede haber ids repetidos entre lotes');
    }

    public function testReanudaDespuesDeUnIdSinRepetir(): void
    {
        $marker = random_int(100000, 999999);
        $ids = [];

        for ($i = 0; $i < 5; $i++) {
            $user = (new User())
                ->setChatId(500000000 + $marker * 10 + $i + 100)
                ->setFirstName('RS' . $marker)
                ->setIsBot(false);

            $this->em->persist($user);
            $this->em->flush();
            $ids[] = $user->getId();
        }

        $this->em->clear();

        // Todo lo que venga despues del tercero es lo que quedaria tras
        // una ejecucion interrumpida.
        $after = $ids[2];

        $collected = [];
        foreach ($this->repository->iterateForBroadcast(10, $after) as $user) {
            if ($user->getFirstName() === 'RS' . $marker) {
                $collected[] = $user->getId();
            }
        }

        $this->assertSame([$ids[3], $ids[4]], $collected);
        $this->assertNotContains($after, $collected, 'El id de corte no debe repetirse');
    }

    public function testIteraEnOrdenAscendente(): void
    {
        $marker = random_int(100000, 999999);

        for ($i = 0; $i < 4; $i++) {
            $this->em->persist(
                (new User())
                    ->setChatId(500000000 + $marker * 10 + $i + 200)
                    ->setFirstName('OR' . $marker)
                    ->setIsBot(false)
            );
        }

        $this->em->flush();
        $this->em->clear();

        $ids = [];
        foreach ($this->repository->iterateForBroadcast(2) as $user) {
            if ($user->getFirstName() === 'OR' . $marker) {
                $ids[] = $user->getId();
            }
        }

        $sorted = $ids;
        sort($sorted);

        $this->assertSame($sorted, $ids, 'El iterador debe avanzar por id ascendente');
    }
}