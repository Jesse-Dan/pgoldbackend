<?php

namespace App\Module\Rates\Services;

use App\Services\PgoldServices;
use RuntimeException;

class RateServices
{
 protected PgoldServices $pgold;

 public function __construct(PgoldServices $pgold)
 {
  $this->pgold = $pgold;
 }


 public function getCryptoRates(): array
 {
  $response = $this->pgold->getCryptoRates();

  if (!$response['success']) {
   throw new RuntimeException($response['message'] ?? 'Unable to fetch crypto rates.');
  }

  $data = $response['data'] ?? [];

  if (empty($data)) {
   throw new RuntimeException('No cryptocurrency data returned.');
  }

  return $data;
 }


 public function getGiftcardRates(): array
 {
  $response = $this->pgold->getGiftcardRates();

  if (!$response['success']) {
   throw new RuntimeException($response['message'] ?? 'Unable to fetch giftcard rates.');
  }

  $data = $response['data'] ?? [];

  if (empty($data)) {
   throw new RuntimeException('No giftcard data returned.');
  }

  return $data;
 }
}
