<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\BaseController as BaseController;

use App\Models\Account;
use App\Models\Wallet;
use Illuminate\Support\Facades\Http;
use Config;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\CurrencyController;

class AccountController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = collect();
        $userAccount = collect();
        $loggedInUser = auth('api')->user();
        $accounts = Account::where('user_id', $loggedInUser->id)
            ->with('currency')
            ->get();
        $apiKey = App::environment('prod')
            ? config('thirdParty.tatumMainNetKey')
            : config('thirdParty.tatumTestNetKey');
        foreach ($accounts as $key => $account) {
            $url =
                config('thirdParty.tatumBaseURL') .
                '/v3/ledger/account/' .
                $account->tatum_account_id;
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
            ])->get($url);
            if ($response->successful()) {
                $returnAccountObj = new Account();
                $returnAccountObj->id = $account->id;
                $returnAccountObj->tatum_account_id =
                    $account->tatum_account_id;
                $returnAccountObj->tatum_customer_id =
                    $account->tatum_customer_id;
                $returnAccountObj->deposit_address = $account->deposit_address;
                $returnAccountObj->accountBalance = $response->object()->balance->accountBalance;
                $returnAccountObj->availableBalance = $response->object()->balance->availableBalance;
                //Add current rate to currency data
                if ($account->currency->label != 'lion') {
                    $rateUrl =
                        config('thirdParty.cryptoCompareURL') .
                        '/data/price?fsym=' .
                        $account->currency->label .
                        '&tsyms=USD';
                    $rateResponse = Http::get($rateUrl);
                    $account->currency->usdRate = $rateResponse->object()->USD;
                } else {
                    $rateUrl =
                        config('thirdParty.moralisV3URL') .
                        '/api/v2/erc20/' .
                        config('thirdParty.lionAddress') .
                        '/price?chain=bsc&exchange=pancakeswap-v2';
                    $rateResponse = Http::withHeaders([
                        'x-api-key' => config('thirdParty.moralisV3Key'),
                    ])->get($rateUrl);
                    $account->currency->usdRate = $rateResponse->object()->usdPrice;
                }
                $returnAccountObj->currency = $account->currency;
                $returnAccountObj->created_at = $account->created_at;
                $returnAccountObj->updated_at = $account->updated_at;
                $userAccount->add($returnAccountObj);
            }
        }
        $data->offsetSet('user', $loggedInUser);
        $data->offsetSet('accounts', $userAccount);
        return $this->sendResponse($data, '');
    }

    public function getDepositAddress($accountId)
    {
        $loggedInUser = auth('api')->user();
        $account = Account::where('id', $accountId)
            ->where('user_id', $loggedInUser->id)
            ->with('currency')
            ->first();
        if ($account) {
            if($account->deposit_address == '') {

                $apiKey = App::environment('prod')
                    ? config('thirdParty.tatumMainNetKey')
                    : config('thirdParty.tatumTestNetKey');
    
                $url =
                    config('thirdParty.tatumBaseURL') .
                    '/v3/offchain/account/' .
                    $account->tatum_account_id .
                    '/address';
                $response = Http::withHeaders([
                    'x-api-key' => $apiKey,
                ])->post($url, []);
    
                if ($response->successful()) {
                    $account->deposit_address = $response->object()->address;
                    $account->derivation_key = $response->object()->derivationKey;
                    if($account->currency->label == 'bnb') {
                        $account->memo = $response->object()->memo;
                    }
                    $account->save();
                }
            }
            return $this->sendResponse([
                'address' => $account->deposit_address,
                'memeo' => $account->memo
            ], '');
        } else {
            return $this->sendError('account not found.', [
                'error' => 'account not found',
            ]);
        }
    }

    public function createPrivateKey($accountId)
    {
        $loggedInUser = auth('api')->user();
        $account = Account::where('id', $accountId)
            ->where('user_id', $loggedInUser->id)
            ->with('currency')
            ->first();
        $wallet = Wallet::where('currency_id', $account->currency->id)->first();
        if ($account) {
            $apiKey = App::environment('prod')
                ? config('thirdParty.tatumMainNetKey')
                : config('thirdParty.tatumTestNetKey');
            $url =
                config('thirdParty.tatumBaseURL') .
                '/v3/' .
                $account->currency->tatum_name .
                '/wallet/priv';
            $requestObj = [
                'mnemonic' => $wallet->mnemonic,
                'index' => $account->user_id,
            ];
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
            ])->post($url, $requestObj);
            if ($response->successful()) {
                $account->private_key = $response->object()->key;
                $account->save();
            }
            return $this->sendResponse([], 'Key generated');
        } else {
            return $this->sendError('account not found.', [
                'error' => 'account not found',
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
