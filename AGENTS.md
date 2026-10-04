# AGENTS.md

## Contexto

Monolito Symfony que aloja varias mini-apps personales bajo `lify.win`. Un solo repo, un solo deploy, varias apps aisladas por subdominio vía Cloudflare Tunnel. Cada idea nueva se mete aquí en lugar de crear un proyecto aparte, para no estar reconfigurando installs constantemente.

Stack: Symfony 7.3.4, PHP 8.4, Doctrine ORM 3.2, Messenger, Twig, `sabre/vobject`.

## Mapa de apps

| App | Controller | Host | Notas |
|---|---|---|---|
| iCal Merger | `IcalController` | `ical.lify.win` | Único con subdominio propio |
| Telegram bot | `TelegramController` | `lify.win` | `POST /telegram`, vhost Apache propio |
| Chess API | `ChessApiController` | `lify.win` | `/chess/api`, lanza Stockfish |

## Cómo añadir una app nueva

1. Controller en `src/Controller/`. Si lleva subdominio propio, `#[Route(host: 'subdominio.lify.win')]` a nivel de clase. Si comparte vhost Apache, sin host.
2. Endpoint en Cloudflare Tunnel apuntando a ese host.
3. Vhost en Apache en el servidor si comparte dominio.
4. Commit a `staging`. Actions despliega a test automáticamente.
5. Cuando valides, push a `master`.

Aisla por nombre: servicios `Telegram*`, `ICal*`, `AI*`. Evita que una app dependa de otra.

## Convenciones

- Rutas con atributos `#[Route]`, nunca annotations. `config/routes.yaml` solo escanea `src/Controller/`.
- El enrutado interno del bot de Telegram vive en `src/Service/TelegramRouter.php`, no en atributos. `TelegramController` es un dispatcher delgado.
- DTOs en `src/Dto/`. Los de Telegram implementan `TelegramDtoInterface`.
- Messenger solo lo usa Telegram: `TelegramService` despacha, `TelegramMessageProcessor` consume.
- Los ficheros iCal se sirven desde `var/calendars/{token}.ics`. Se leen con `BinaryFileResponse` en `IcalController::export()`.

## Comandos

```bash
php bin/console debug:router                   # 6 rutas
php bin/console app:sync-ical-calendars         # sincroniza iCal
php bin/console app:sync-ical-calendars --force # ignora syncInterval
php bin/console doctrine:migrations:migrate -n
php bin/console lint:container
vendor/bin/phpunit
composer dump-env prod
```

## Flujo de trabajo

Commits a `staging` primero. `.github/workflows/staging.yml` dispara en push a `staging`, corre los tests y luego llama a `deploy.yml`, que hace clone fresco en `/var/www/html/` vía Cloudflare Tunnel y aplica migraciones.

Cuando valides en test, push a `master`. **No hace falta PR**: la protección de rama existe pero la salta el owner del repo. GitHub avisa por consola, el push pasa igual.

`master` no tiene workflow de deploy. Se despliega fuera de GitHub Actions.

Usa conventional commits. El historial mezcla estilos (`feature:`, `fix:`, `refactor:`) sin scopes, así que sé consistente con eso.

### Commits firmados

`commit.gpgsign=true` con `gpg.format=ssh` en la config global de git. El comando de commit falla con `Couldn't find key in agent?` si el agente ssh no tiene la clave cargada.

**No gestionas las llaves ssh.** Cuando un commit falle por esto, dilo al usuario y deja que lo ejecute él. No cargues claves ni arranques agentes ssh por tu cuenta.

## Entorno

| Dónde | Base de datos |
|---|---|
| Local | MariaDB 11.8 en `192.168.1.236` |
| CI | MySQL 8 service container |
| `.env` (commit) | Postgres, placeholder, no se usa |

El deploy sobrescribe `.env` con secrets de GitHub. No edites `.env` para producción.

`APP_ENV` lo fija el workflow.

## Trampas conocidas

**`DEFAULT_URI=http://localhost` en `.env`.** Por eso existe el `str_replace('http://', 'https://')` en `IcalController:60`. Es un parche sobre la config. Si lo arreglas, quita también el parche.

**`config/services_test.yaml` reemplaza a `services.yaml`, no lo importa.** El entorno test carga solo el primero. Cualquier binding explícito hay que duplicarlo en los dos archivos o la suite rompe con `Cannot autowire service`.

**Los tests solo cubren Telegram.** `tests/Unit/Service/` y `tests/Unit/Dto/` son casi todos del bot. iCal y Chess no tienen ninguno, y Chess es el que lanza un subproceso de Stockfish.

**`ICalMergerService` se traga los errores de red.** `mergeFromUrls()` tiene un `catch (\Throwable)` que hace `continue`. Si todas las fuentes fallan devuelve un `VCALENDAR` vacío pero sintácticamente válido. Quien lo consume debe comprobar `BEGIN:VEVENT` antes de escribir en disco; `SyncIcalCalendarsCommand` lo hace, `IcalController::create()` no.

**`ChessApiController` llama a un binario por ruta relativa.** `../bin/stockfish-ubuntu-x86_64-avx2`. Depende del cwd de PHP-FPM. Frágil.

**La firma SSH puede bloquear los commits.** Si falla con `Couldn't find key in agent?`, no lo resuelvas cargando llaves: pídeselo al usuario. Detalle en la sección de flujo de trabajo.

## Invariantes

- Las 6 rutas de `debug:router` no deben cambiar de nombre. El sitemap y los enlaces generados dependen de ellas.
- `public/sitemap.xml` es estático y manual. Las URLs nuevas hay que añadirlas a mano.
- `public/robots.txt` está en el repo y lo sirve el servidor web, no Symfony. Cuidado con `Allow: /$`: el `$` ancla a la ruta literal y bloquea todo el sitio.