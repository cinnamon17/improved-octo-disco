<?php
namespace App\Command;

use App\Repository\IcalCalendarRepository;
use App\Service\ICalMergerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(name: 'app:sync-ical-calendars', description: 'Sincroniza y fusiona los archivos iCal en disco')]
class SyncIcalCalendarsCommand extends Command
{
    public function __construct(
        private IcalCalendarRepository $repository,
        private ICalMergerService $merger,
        private Filesystem $filesystem,
        private EntityManagerInterface $em,
        #[Autowire('%kernel.project_dir%')] private string $projectDir
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $calendars = $this->repository->findAll();
        $now = new \DateTimeImmutable();

        foreach ($calendars as $calendar) {
            $lastSynced = $calendar->getLastSyncedAt();
            $intervalMinutes = $calendar->getSyncInterval();

            // Comprobar si le toca actualizarse según su intervalo
            if ($lastSynced !== null) {
                $diffInMinutes = ($now->getTimestamp() - $lastSynced->getTimestamp()) / 60;
                if ($diffInMinutes < $intervalMinutes) {
                    continue;
                }
            }

            // Descargar y fusionar
            $icsContent = $this->merger->mergeFromUrls($calendar->getSources());

            // Guardar en var/calendars/{token}.ics
            $dirPath = sprintf('%s/var/calendars', $this->projectDir);
            $filePath = sprintf('%s/%s.ics', $dirPath, $calendar->getToken());

            $this->filesystem->dumpFile($filePath, $icsContent);

            $calendar->setLastSyncedAt($now);
        }

        $this->em->flush();
        $output->writeln('Sincronización completada.');

        return Command::SUCCESS;
    }
}
