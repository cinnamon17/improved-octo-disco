<?php
namespace App\Controller;

use App\Entity\IcalCalendar;
use App\Service\ICalMergerService;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(host: 'ical.lify.win')]
class IcalController extends AbstractController
{
    // 1. Landing page con prefijos de idioma ({_locale})
    #[Route([
        'es' => '/',
        'en' => '/en'
    ], name: 'ical_landing', methods: ['GET'])]
    public function landing(): Response
    {
        return $this->render('ical/landing.html.twig');
    }

    // 2. Procesar el formulario (se mantiene genérico para el submit)
    #[Route('/ical-merger/create', name: 'ical_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        ICalMergerService $merger,
        Filesystem $filesystem
    ): Response {
        $projectDir = $this->getParameter('kernel.project_dir');
        $urlsRaw = $request->request->all('urls');
        $urls = array_values(array_filter($urlsRaw, fn($url) => !empty(trim($url))));

        if (empty($urls)) {
            $this->addFlash('error', 'Introduce al menos una URL de iCal válida.');
            return $this->redirectToRoute('ical_landing');
        }

        $calendar = new IcalCalendar();
        $token = bin2hex(random_bytes(16));
        $calendar->setToken($token);
        $calendar->setSources($urls);
        $calendar->setSyncInterval(720);
        $calendar->setCreatedAt(new \DateTimeImmutable());

        $em->persist($calendar);
        $em->flush();

        // Generar el primer .ics inmediatamente al crear
        $icsContent = $merger->mergeFromUrls($urls);
        $filePath = sprintf('%s/var/calendars/%s.ics', $projectDir, $token);
        $filesystem->dumpFile($filePath, $icsContent);

        $publicUrl = $this->generateUrl('ical_export', ['token' => $token], 0);

        // Si usas el mismo template renderizando la URL directamente:
        return $this->render('ical/landing.html.twig', [
            'mergedUrl' => $publicUrl,
            'calendar'  => $calendar,
        ]);
    }

    // 3. Endpoint técnico para exportación de archivos .ics (NO requiere idioma)
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
