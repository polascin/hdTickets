<?php

namespace App\Services\Scraping\Traits;

use Illuminate\Support\Facades\Log;

trait CurrencyHandlingTrait
{
    /**
     * Parse price information from text with multi-currency support
     */
    protected function parsePriceInfo(string $priceText): array
    {
        $priceInfo = ['min' => null, 'max' => null];
        
        if (empty($priceText)) {
            return $priceInfo;
        }

        // Detect currency from the text
        $detectedCurrency = $this->detectCurrency($priceText);
        if ($detectedCurrency && $detectedCurrency !== $this->currency) {
            Log::info("Currency mismatch detected", [
                'expected' => $this->currency,
                'detected' => $detectedCurrency,
                'price_text' => $priceText
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
                    if ($normalizedPrice !== null) {
                        $prices[] = $normalizedPrice;
                    }
                }
                break; // Use first successful pattern
            }
        }

        if (!empty($prices)) {
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

        return null;
    }

    /**
     * Clean price text by removing non-price content
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
    protected function normalizePriceValue(string $price): ?float
    {
        try {
            // Handle European decimal format (1.234,56 -> 1234.56)
            if (preg_match('/^\d{1,3}(?:[.\s]\d{3})*,\d{2}$/', $price)) {
                // Remove thousands separators and convert comma to dot
                $normalized = str_replace(['.', ' '], '', $price);
                $normalized = str_replace(',', '.', $normalized);
                return floatval($normalized);
            }
            
            // Handle US/UK decimal format (1,234.56 -> 1234.56)
            if (preg_match('/^\d{1,3}(?:,\d{3})*\.\d{2}$/', $price)) {
                // Remove thousands separators
                $normalized = str_replace(',', '', $price);
                return floatval($normalized);
            }
            
            // Handle simple formats
            $normalized = str_replace(',', '.', $price);
            $value = floatval($normalized);
            
            // Validate reasonable price range
            if ($value >= 0 && $value <= 10000) {
                return $value;
            }
            
            return null;
            
        } catch (\Exception $e) {
            Log::warning("Failed to normalize price value", [
                'price' => $price,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Convert currency (basic implementation - would use real exchange rates in production)
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

        Log::info("Converting currency", [
            'from' => $fromCurrency,
            'to' => $toCurrency,
            'rate' => $rate,
            'original_min' => $priceInfo['min'],
            'original_max' => $priceInfo['max']
        ]);

        return [
            'min' => $priceInfo['min'] ? round($priceInfo['min'] * $rate, 2) : null,
            'max' => $priceInfo['max'] ? round($priceInfo['max'] * $rate, 2) : null,
        ];
    }

    /**
     * Format price for display
     */
    protected function formatPrice(float $price, string $currency = null): string
    {
        $currency = $currency ?? $this->currency;
        
        return match($currency) {
            'EUR' => '€' . number_format($price, 2, ',', '.'),
            'GBP' => '£' . number_format($price, 2, '.', ','),
            'USD' => '$' . number_format($price, 2, '.', ','),
            default => $currency . ' ' . number_format($price, 2),
        };
    }

    /**
     * Get currency symbol
     */
    protected function getCurrencySymbol(string $currency = null): string
    {
        $currency = $currency ?? $this->currency;
        
        return match($currency) {
            'EUR' => '€',
            'GBP' => '£',
            'USD' => '$',
            default => $currency,
        };
    }

    /**
     * Validate price range for the currency
     */
    protected function isValidPriceRange(float $price, string $currency = null): bool
    {
        $currency = $currency ?? $this->currency;
        
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
