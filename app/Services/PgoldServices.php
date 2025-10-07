<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;

class PgoldServices
{
 protected string $baseUrl;

 public function __construct()
 {
  $this->baseUrl = rtrim(config('services.pgold.sandbox_url', env('PGOLD_SANDBOX_URL', 'https://sandbox.example.com')), '/');
 }

 public function getCryptoRates(): array
 {
  return $this->fetchFromApi('/api/guest/cryptocurrencies');
 }

 public function getGiftcardRates(): array
 {
  return $this->fetchFromApi('/api/guest/giftcards');
 }

 private function fetchFromApi(string $path): array
 {
  try {
   $response = Http::timeout(10)
    ->acceptJson()
    ->get("{$this->baseUrl}{$path}");

   $response->throw();

   return [
    'success' => true,
    'data' => $response->json(),
   ];
  } catch (RequestException $e) {
   Log::error("PGold API error [{$path}]: {$e->getMessage()}");

   return [
    'success' => false,
    'message' => 'Failed to fetch data from PGold API.',
    'error' => $e->getMessage(),
   ];
  } catch (\Throwable $e) {
   Log::error("Unexpected PGold API error [{$path}]: {$e->getMessage()}");

   return [
    'success' => false,
    'message' => 'Unexpected error occurred.',
    'error' => $e->getMessage(),
   ];
  }
 }
}
