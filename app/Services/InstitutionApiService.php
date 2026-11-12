<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class InstitutionApiService
{
    private const API_URL = 'https://api-perguruan-tinggi-indonesia.herokuapp.com/graphql';
    
    /**
     * Fetch universities from PDDikti API
     */
    public static function fetchUniversities(?string $search = null, int $limit = 100): array
    {
        $cacheKey = 'universities_' . md5($search ?? 'all');
        
        return Cache::remember($cacheKey, now()->addDays(7), function () use ($search, $limit) {
            try {
                $query = self::buildUniversityQuery($search, $limit);
                
                $response = Http::timeout(10)->post(self::API_URL, [
                    'query' => $query
                ]);
                
                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['data']['perguruanTinggi']) && is_array($data['data']['perguruanTinggi'])) {
                        $universities = array_map(function ($pt) {
                            return $pt['nama'] ?? '';
                        }, $data['data']['perguruanTinggi']);
                        // Filter empty values and return unique
                        return array_values(array_unique(array_filter($universities)));
                    }
                    // If structure is different, try alternative paths
                    if (isset($data['data']) && is_array($data['data'])) {
                        $universities = [];
                        foreach ($data['data'] as $item) {
                            if (isset($item['nama'])) {
                                $universities[] = $item['nama'];
                            }
                        }
                        if (!empty($universities)) {
                            return array_values(array_unique($universities));
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to fetch universities from API: ' . $e->getMessage());
            }
            
            // Fallback to static list if API fails
            return \App\Helpers\InstitutionHelper::getUniversities();
        });
    }
    
    /**
     * Search universities by name
     */
    public static function searchUniversities(string $searchTerm, int $limit = 50): array
    {
        return self::fetchUniversities($searchTerm, $limit);
    }
    
    /**
     * Build GraphQL query for universities
     */
    private static function buildUniversityQuery(?string $search, int $limit): string
    {
        // Input pengguna tidak boleh menyusup ke struktur query: batasi limit dan escape literal string.
        $limit = max(1, min(100, $limit));
        $literal = json_encode((string) $search, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_QUOT);

        // GraphQL query untuk API PDDikti
        // Format query disesuaikan dengan schema API
        if ($search) {
            return <<<GRAPHQL
query {
    perguruanTinggi(filter: {nama: {contains: {$literal}}}, limit: $limit) {
        nama
        akreditasi
        alamat
        jenis
    }
}
GRAPHQL;
        } else {
            return <<<GRAPHQL
query {
    perguruanTinggi(limit: $limit) {
        nama
        akreditasi
        alamat
        jenis
    }
}
GRAPHQL;
        }
    }
    
    /**
     * Get all universities with caching
     */
    public static function getAllUniversities(): array
    {
        return self::fetchUniversities(null, 1000);
    }
    
    /**
     * Clear cache
     */
    public static function clearCache(): void
    {
        Cache::forget('universities_all');
        Cache::flush();
    }
}

