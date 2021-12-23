<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Currency;
use App\Models\Wallet;
use Illuminate\Support\Facades\Http;
use Config;
use Illuminate\Support\Facades\App;

class WalletController extends Controller
{
    public function index()
    {
    }

    public function generateWallets()
    {
        $currencies = Currency::get();
        $environment = App::environment();

        $apiKey = App::environment('prod')
            ? config('thirdParty.tatumMainNetKey')
            : config('thirdParty.tatumTestNetKey');
        for ($i = 0; $i < count($currencies); $i++) {
            if (
                !$this->checkCurrencyWallet($currencies[$i]->id) &&
                $currencies[$i]->label != 'bnb'
            ) {
                $url =
                    config('thirdParty.tatumBaseURL') .
                    '/v3/' .
                    strtolower($currencies[$i]->tatum_name) .
                    '/wallet';
                $response = Http::withHeaders([
                    'x-api-key' => $apiKey,
                ])->get($url);

                if ($response->successful()) {
                    $mnemonic = $response->object()->mnemonic;
                    $xpub = $response->object()->xpub;
                    $wallet = new Wallet();
                    $wallet->currency_id = $currencies[$i]->id;
                    $wallet->mnemonic = $mnemonic;
                    $wallet->xpub = $xpub;
                    $wallet->save();
                }
            }
        }
    }

    public function checkCurrencyWallet($currencyId)
    {
        return Wallet::where('currency_id', $currencyId)->first();
    }
}
