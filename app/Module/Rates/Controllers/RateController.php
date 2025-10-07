<?php

namespace App\Module\Rates\Controllers;

use App\Http\Controllers\Controller;
use App\Module\Rates\Services\RateServices;
use App\Helpers\ResponseHelper;
use Throwable;

class RateController extends Controller
{
    protected RateServices $rates;

    public function __construct(RateServices $rates)
    {
        $this->rates = $rates;
    }

    public function cryptoRates()
    {
        try {
            $data = $this->rates->getCryptoRates();
            return ResponseHelper::success($data, 'Crypto rates fetched successfully.');
        } catch (Throwable $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function giftcardRates()
    {
        try {
            $data = $this->rates->getGiftcardRates();
            return ResponseHelper::success($data, 'Giftcard rates fetched successfully.');
        } catch (Throwable $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }
}
