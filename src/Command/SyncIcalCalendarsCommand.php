<?php
namespace App\Command;

use App\Entity\IcalCalendar;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route(host: 'ical.lify.win')]
class SyncIcalCalendarsCommand extends AbstractController
{
    #[Route([
        'es' => '/',
        'en' => '/en'
    ], name: 'ical_landing', methods: ['GET'])]
    public function landing(): Response
    {
        return $this->render('ical/landing.html.twig');
    }

    #[Route('/ical-merger/create', name: 'ical_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        KernelInterface $kernel
    ): Response {
        $urlsRaw = $request->request->all('urls');
        $urls = array_values(array_filter($urlsRaw, fn($url) => !empty(trim($url))));

        if (empty($urls)) {
            $this->addFlash('error', 'Introduce al menos una URL de iCal válida.');
            return $this->redirectToRoute('ical_landing');
        }

        // 1. Guardar la entidad en BBDD
        $calendar = new IcalCalendar();
        $token = bin2hex(random_bytes(16));
        $calendar->setToken($token);
        $calendar->setSources($urls);
        $calendar->setSyncInterval(720);
        $calendar->setCreatedAt(new \DateTimeImmutable());

        $em->persist($calendar);
        $em->flush(); // Guardamos para que el comando pueda encontrarlo con findAll()

        // 2. Ejecutar el comando de consola programáticamente
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'app:sync-ical-calendars',
        ]);

        $output = new BufferedOutput();
        $application->run($input, $output);

        // 3. Generar la URL pública de exportación
        $publicUrl = $this->generateUrl('ical_export', ['token' => $token], 0);

        return $this->render('ical/landing.html.twig', [
            'mergedUrl' => $publicUrl,
            'calendar'  => $calendar,
        ]);
    }

    #[Route('/ical/export/{token}.ics', name: 'ical_export', methods: ['GET'])]
    public function export(string $token): Response
    {
        $projectDir = $this->getParameter('kernel.project_dir');
        $filePath = sprintf('%s/var/calendars/%s.ics', $projectDir, $token);

        if (!file_exists($filePath)) {
            return new Response('Calendario no encontrado o pendiente de primera sincronización.', 404);
        }

        return new BinaryFileResponse($filePath, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'inline; filename="calendar.ics"',
            'Cache-Control' => 'no-cache, must-revalidate',
        ]);
    }
}
