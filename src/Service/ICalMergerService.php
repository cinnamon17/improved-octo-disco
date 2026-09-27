<?php
namespace App\Service;

use Sabre\VObject\Reader;
use Sabre\VObject\Component\VCalendar;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ICalMergerService
{
    public function __construct(
        private HttpClientInterface $httpClient
    ) {}

    public function mergeFromUrls(array $urls): string
    {
        $mergedCalendar = new VCalendar();

        foreach ($urls as $url) {
            try {
                $response = $this->httpClient->request('GET', trim($url), [
                    'timeout' => 3.0,
                ]);

                if ($response->getStatusCode() === 200) {
                    $vcal = Reader::read($response->getContent());
                    if (isset($vcal->VEVENT)) {
                        foreach ($vcal->VEVENT as $event) {
                            $mergedCalendar->add(clone $event);
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Si una URL falla o da timeout, continuamos con las demás
                continue;
            }
        }

        return $mergedCalendar->serialize();
    }
}
