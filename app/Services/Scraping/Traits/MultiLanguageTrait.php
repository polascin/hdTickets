<?php declare(strict_types=1);

namespace App\Services\Scraping\Traits;

use DateTime;
use Exception;
use Illuminate\Support\Facades\Log;

use function count;

trait MultiLanguageTrait
{
    /**
     * Get Accept-Language header based on plugin language
     */
    protected function getAcceptLanguageHeader(): string
    {
        return match ($this->language) {
            'es-ES' => 'es-ES,es;q=0.9,en;q=0.8',
            'de-DE' => 'de-DE,de;q=0.9,en;q=0.8',
            'it-IT' => 'it-IT,it;q=0.9,en;q=0.8',
            'fr-FR' => 'fr-FR,fr;q=0.9,en;q=0.8',
            default => 'en-GB,en-US;q=0.9,en;q=0.8',
        };
    }

    /**
     * Parse date in multiple European formats
     */
    protected function parseDate(string $dateString): ?string
    {
        if (empty($dateString)) {
            return NULL;
        }

        try {
            // European date patterns by language
            $patterns = [
                // German: DD.MM.YYYY
                '/(\d{1,2})\.(\d{1,2})\.(\d{4})/' => 'd.m.Y',
                // French/Spanish/Italian: DD/MM/YYYY
                '/(\d{1,2})\/(\d{1,2})\/(\d{4})/' => 'd/m/Y',
                // ISO format: YYYY-MM-DD
                '/(\d{4}-\d{1,2}-\d{1,2})/' => 'Y-m-d',
                // UK format: DD-MM-YYYY
                '/(\d{1,2})-(\d{1,2})-(\d{4})/' => 'd-m-Y',
            ];

            foreach ($patterns as $pattern => $format) {
                if (preg_match($pattern, $dateString, $matches)) {
                    if (count($matches) === 4) {
                        // DD.MM.YYYY, DD/MM/YYYY, DD-MM-YYYY formats
                        $separator = str_contains($dateString, '.') ? '.' : (str_contains($dateString, '/') ? '/' : '-');
                        $actualFormat = str_replace(['.', '/', '-'], $separator, $format);
                        $date = DateTime::createFromFormat($actualFormat, "{$matches[1]}{$separator}{$matches[2]}{$separator}{$matches[3]}");
                    } else {
                        // YYYY-MM-DD format
                        $date = DateTime::createFromFormat($format, $matches[1]);
                    }

                    if ($date) {
                        return $date->format('Y-m-d H:i:s');
                    }
                }
            }

            // Try to parse with Carbon (handles many formats automatically)
            $parsed = \Carbon\Carbon::parse($dateString);

            return $parsed->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            Log::warning('Failed to parse date', [
                'date_string' => $dateString,
                'language'    => $this->language ?? 'unknown',
                'error'       => $e->getMessage(),
            ]);

            return NULL;
        }
    }

    /**
     * Normalize availability status in multiple languages
     */
    protected function normalizeAvailability(string $availability): string
    {
        $availability = strtolower(trim($availability));

        // Sold out detection (multi-language)
        $soldOutTerms = [
            'sold out', 'unavailable', 'not available',
            'agotado', 'no disponible', 'sin entradas',  // Spanish
            'ausverkauft', 'nicht verfügbar', 'vergriffen', // German
            'esaurito', 'non disponibile', 'terminato',     // Italian
            'épuisé', 'indisponible', 'complet',            // French
        ];

        foreach ($soldOutTerms as $term) {
            if (str_contains($availability, $term)) {
                return 'sold_out';
            }
        }

        // Available detection (multi-language)
        $availableTerms = [
            'available', 'on sale', 'in stock',
            'disponible', 'en venta', 'a la venta',        // Spanish
            'verfügbar', 'erhältlich', 'im verkauf',       // German
            'disponibile', 'in vendita', 'acquistabile',   // Italian
            'disponible', 'en vente', 'à vendre',           // French
        ];

        foreach ($availableTerms as $term) {
            if (str_contains($availability, $term)) {
                return 'available';
            }
        }

        // Coming soon detection (multi-language)
        $comingSoonTerms = [
            'coming soon', 'pre-sale', 'pre-order',
            'próximamente', 'preventa', 'pronto',          // Spanish
            'bald verfügbar', 'vorverkauf', 'demnächst',   // German
            'prossimamente', 'prevendita', 'in arrivo',    // Italian
            'bientôt disponible', 'prévente', 'à venir',    // French
        ];

        foreach ($comingSoonTerms as $term) {
            if (str_contains($availability, $term)) {
                return 'coming_soon';
            }
        }

        return 'unknown';
    }

    /**
     * Detect if event is football/soccer related (multi-language)
     */
    protected function isFootballEvent(string $eventName): bool
    {
        $footballTerms = [
            // English
            'football', 'soccer', ' fc ', ' cf ', 'premier league', 'champions league',
            'europa league', 'fa cup', 'league cup', 'vs ', 'v ',
            // Spanish
            'fútbol', 'liga', 'copa', 'real madrid', 'barcelona', 'atlético',
            'el clásico', 'derbi', 'contra ',
            // German
            'fußball', 'bundesliga', 'dfb-pokal', 'bayern', 'dortmund', 'gegen ',
            // Italian
            'calcio', 'serie a', 'coppa italia', 'juventus', 'milan', 'inter',
            'derby', 'contro ',
            // French
            'foot', 'ligue 1', 'coupe de france', 'psg', 'paris', 'contre ',
        ];

        foreach ($footballTerms as $term) {
            if (str_contains($eventName, $term)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Detect if event is music related (multi-language)
     */
    protected function isMusicEvent(string $eventName): bool
    {
        $musicTerms = [
            // English
            'concert', 'tour', 'live', 'music', 'festival', 'band', 'singer',
            // Spanish
            'concierto', 'gira', 'música', 'cantante', 'grupo',
            // German
            'konzert', 'musik', 'sänger', 'band', 'gruppe',
            // Italian
            'concerto', 'musica', 'cantante', 'gruppo', 'festival',
            // French
            'concert', 'musique', 'chanteur', 'groupe', 'festival',
        ];

        foreach ($musicTerms as $term) {
            if (str_contains($eventName, $term)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Detect if event is theater related (multi-language)
     */
    protected function isTheaterEvent(string $eventName): bool
    {
        $theaterTerms = [
            // English
            'theater', 'theatre', 'play', 'musical', 'drama', 'comedy', 'show',
            // Spanish
            'teatro', 'obra', 'musical', 'drama', 'comedia', 'espectáculo',
            // German
            'theater', 'schauspiel', 'musical', 'drama', 'komödie', 'show',
            // Italian
            'teatro', 'spettacolo', 'musical', 'dramma', 'commedia',
            // French
            'théâtre', 'pièce', 'musical', 'drame', 'comédie', 'spectacle',
        ];

        foreach ($theaterTerms as $term) {
            if (str_contains($eventName, $term)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Get localized error messages
     */
    protected function getLocalizedErrorMessage(string $key): string
    {
        $messages = [
            'connection_failed' => [
                'en' => 'Connection failed',
                'es' => 'Conexión fallida',
                'de' => 'Verbindung fehlgeschlagen',
                'it' => 'Connessione fallita',
                'fr' => 'Connexion échouée',
            ],
            'page_not_found' => [
                'en' => 'Page not found',
                'es' => 'Página no encontrada',
                'de' => 'Seite nicht gefunden',
                'it' => 'Pagina non trovata',
                'fr' => 'Page non trouvée',
            ],
            'access_denied' => [
                'en' => 'Access denied',
                'es' => 'Acceso denegado',
                'de' => 'Zugriff verweigert',
                'it' => 'Accesso negato',
                'fr' => 'Accès refusé',
            ],
        ];

        $langCode = substr($this->language ?? 'en', 0, 2);

        return $messages[$key][$langCode] ?? $messages[$key]['en'] ?? $key;
    }
}
