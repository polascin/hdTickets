<?php declare(strict_types=1);

namespace App\Services\Scraping\Traits;

use Exception;
use Illuminate\Support\Facades\Log;

use function count;

trait CurrencyHandlingTrait
{
    /**
     * Parse price information from text with multi-currency support
     */
    /**
     * ParsePriceInfo
     */
    protected function parsePriceInfo(string $priceText): array
    {
        $priceInfo = ['min' => NULL, 'max' => NULL];

        if (empty($priceText)) {
            return $priceInfo;
        }

        // Detect currency from the text
        $detectedCurrency = $this->detectCurrency($priceText);
        if ($detectedCurrency && $detectedCurrency !== $this->currency) {
            Log::info('Currency mismatch detected', [
                'expected'   => $this->currency,
                'detected'   => $detectedCurrency,
                'price_text' => $priceText,
            ]);
        }

        // Clean text and extract prices
        $cleanText = $this->cleanPriceText($priceText);

        // Extract numeric prices (handle European decimal formats)
        $pricePatterns = [
            // European format: 1.234,56 or 1 234,56
            '/(\d{1,3}(?:[.\s]\d{3})*,\d{2})/',
            // US/UK format: 1,234.56
            '/(\d{1,3}(?:,\d{3})*\.\d{2})/',
            // Simple formats: 123.45, 123,45, 123
            '/(\d+(?:[.,]\d{1,2})?)/',
        ];

        $prices = [];
        foreach ($pricePatterns as $pattern) {
            if (preg_match_all($pattern, $cleanText, $matches)) {
                foreach ($matches[1] as $price) {
                    $normalizedPrice = $this->normalizePriceValue($price);
                    if ($normalizedPrice !== NULL) {
                        $prices[] = $normalizedPrice;
                    }
                }

                break; // Use first successful pattern
            }
        }

        if (! empty($prices)) {
            $priceInfo['min'] = min($prices);
            $priceInfo['max'] = max($prices);

            // If only one price found, set both min and max to same value
            if (count($prices) === 1) {
                $priceInfo['max'] = $priceInfo['min'];
            }

            // Convert currency if needed
            if ($detectedCurrency && $detectedCurrency !== $this->currency) {
                $priceInfo = $this->convertCurrency($priceInfo, $detectedCurrency, $this->currency);
            }
        }

        return $priceInfo;
    }

    /**
     * Detect currency from price text
     */
    /**
     * DetectCurrency
     */
    protected function detectCurrency(string $priceText): ?string
    {
        $currencyPatterns = [
            'EUR' => [
                '€', 'EUR', 'euro', 'euros',
                // Multi-language
                'eur', 'euro',
            ],
            'GBP' => [
                '£', 'GBP', 'pound', 'pounds', 'sterling',
                // Multi-language
                'libra', 'libras', 'pfund',
            ],
            'USD' => [
                '$', 'USD', 'dollar', 'dollars',
                // Multi-language
                'dólar', 'dólares', 'dollaro', 'dollari',
            ],
        ];

        $priceText = strtolower($priceText);

        foreach ($currencyPatterns as $currency => $patterns) {
            foreach ($patterns as $pattern) {
                if (str_contains($priceText, strtolower($pattern))) {
                    return $currency;
                }
            }
        }

        return NULL;
    }

    /**
     * Clean price text by removing non-price content
     */
    /**
     * CleanPriceText
     */
    protected function cleanPriceText(string $priceText): string
    {
        // Remove common non-price words in multiple languages
        $stopWords = [
            // English
            'from', 'starting', 'price', 'cost', 'ticket', 'tickets', 'each', 'per',
            // Spanish
            'desde', 'precio', 'coste', 'entrada', 'entradas', 'cada', 'por',
            // German
            'ab', 'von', 'preis', 'kosten', 'ticket', 'tickets', 'je', 'pro',
            // Italian
            'da', 'prezzo', 'costo', 'biglietto', 'biglietti', 'ogni', 'per',
            // French
            'à partir de', 'prix', 'coût', 'billet', 'billets', 'chaque', 'par',
        ];

        $cleanText = $priceText;
        foreach ($stopWords as $word) {
            $cleanText = str_ireplace($word, ' ', $cleanText);
        }

        return trim($cleanText);
    }

    /**
     * Normalize price value to float
     */
    /**
     * NormalizePriceValue
     */
    protected function normalizePriceValue(string $price): ?float
    {
        try {
            // Handle European decimal format (1.234,56 -> 1234.56)
            if (preg_match('/^\d{1,3}(?:[.\s]\d{3})*,\d{2}$/', $price)) {
                // Remove thousands separators and convert comma to dot
                $normalized = str_replace(['.', ' '], '', $price);
                $normalized = str_replace(',', '.', $normalized);

                return (float) $normalized;
            }

            // Handle US/UK decimal format (1,234.56 -> 1234.56)
            if (preg_match('/^\d{1,3}(?:,\d{3})*\.\d{2}$/', $price)) {
                // Remove thousands separators
                $normalized = str_replace(',', '', $price);

                return (float) $normalized;
            }

            // Handle simple formats
            $normalized = str_replace(',', '.', $price);
            $value = (float) $normalized;

            // Validate reasonable price range
            if ($value >= 0 && $value <= 10000) {
                return $value;
            }

            return NULL;
        } catch (Exception $e) {
            Log::warning('Failed to normalize price value', [
                'price' => $price,
                'error' => $e->getMessage(),
            ]);

            return NULL;
        }
    }

    /**
     * Convert currency (basic implementation - would use real exchange rates in production)
     */
    /**
     * ConvertCurrency
     */
    protected function convertCurrency(array $priceInfo, string $fromCurrency, string $toCurrency): array
    {
        if ($fromCurrency === $toCurrency || empty($priceInfo['min'])) {
            return $priceInfo;
        }

        // Basic exchange rates (in production, use real-time rates)
        $exchangeRates = [
            'EUR' => [
                'GBP' => 0.85,
                'USD' => 1.10,
            ],
            'GBP' => [
                'EUR' => 1.18,
                'USD' => 1.27,
            ],
            'USD' => [
                'EUR' => 0.91,
                'GBP' => 0.79,
            ],
        ];

        $rate = $exchangeRates[$fromCurrency][$toCurrency] ?? 1.0;

        Log::info('Converting currency', [
            'from'         => $fromCurrency,
            'to'           => $toCurrency,
            'rate'         => $rate,
            'original_min' => $priceInfo['min'],
            'original_max' => $priceInfo['max'],
        ]);

        return [
            'min' => $priceInfo['min'] ? round($priceInfo['min'] * $rate, 2) : NULL,
            'max' => $priceInfo['max'] ? round($priceInfo['max'] * $rate, 2) : NULL,
        ];
    }

    /**
     * Format price for display
     */
    /**
     * FormatPrice
     */
    protected function formatPrice(float $price, ?string $currency = NULL): string
    {
        $currency ??= $this->currency;

        return match ($currency) {
            'EUR'   => '€' . number_format($price, 2, ',', '.'),
            'GBP'   => '£' . number_format($price, 2, '.', ','),
            'USD'   => '$' . number_format($price, 2, '.', ','),
            default => $currency . ' ' . number_format($price, 2),
        };
    }

    /**
     * Get currency symbol
     */
    /**
     * Get  currency symbol
     */
    protected function getCurrencySymbol(?string $currency = NULL): string
    {
        $currency ??= $this->currency;

        return match ($currency) {
            'EUR'   => '€',
            'GBP'   => '£',
            'USD'   => '$',
            default => $currency,
        };
    }

    /**
     * Validate price range for the currency
     */
    /**
     * Check if  valid price range
     */
    protected function isValidPriceRange(float $price, ?string $currency = NULL): bool
    {
        $currency ??= $this->currency;

        // Define reasonable price ranges per currency
        $ranges = [
            'EUR' => ['min' => 1, 'max' => 5000],
            'GBP' => ['min' => 1, 'max' => 5000],
            'USD' => ['min' => 1, 'max' => 6000],
        ];

        $range = $ranges[$currency] ?? ['min' => 0, 'max' => 10000];

        return $price >= $range['min'] && $price <= $range['max'];
    }
}
