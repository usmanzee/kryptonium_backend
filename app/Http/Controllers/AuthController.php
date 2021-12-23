<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\BaseController as BaseController;
use App\Models\User;
use App\Models\Currency;
use App\Models\Account;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\Http;
use Config;
use Illuminate\Support\Facades\App;

class AuthController extends BaseController
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users',
            'phone' => 'required',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] = $user->createToken('MyApp')->accessToken;

        $url = config('thirdParty.tatumBaseURL') . '/v3/ledger/account';
        $apiKey = App::environment('prod')
            ? config('thirdParty.tatumMainNetKey')
            : config('thirdParty.tatumTestNetKey');

        $currencies = Currency::with('wallet')->get();

        foreach ($currencies as $key => $currency) {
            if($currency->label == 'bnb') {
                $this->generateBNBWalletAndAccount($currency->id, $user->id);
            } else {
                $requestData = [
                    'currency' => strtoupper($currency->tatum_label),
                    'xpub' => $currency->wallet->xpub,
                    'customer' => [
                        'externalId' => strval($user->id),
                        'accountingCurrency' => 'USD',
                    ],
                    'accountingCurrency' => 'USD',
                ];
                $response = Http::withHeaders([
                    'x-api-key' => $apiKey,
                ])->post($url, $requestData);
    
                if ($response->successful()) {
                    $tatumAccountId = $response->object()->id;
                    $tatumCustomerId = $response->object()->customerId;
                    $account = new Account();
                    $account->user_id = $user->id;
                    $account->currency_id = $currency->id;
                    $account->tatum_account_id = $tatumAccountId;
                    $account->tatum_customer_id = $tatumCustomerId;
                    $account->mnemonic = $currency->wallet->mnemonic;
                    $account->xpub = $currency->wallet->xpub;
                    $account->save();
                }
            }
            
        }
        return $this->sendResponse($success, 'User register successfully.');
    }

    public function generateBNBWalletAndAccount($currencyId, $userId) {
        $apiKey = App::environment('prod')
        ? config('thirdParty.tatumMainNetKey')
        : config('thirdParty.tatumTestNetKey');

        $url = config('thirdParty.tatumBaseURL') . '/v3/bnb/account';

        $bnbAccountResponse = Http::withHeaders([
            'x-api-key' => $apiKey,
        ])->get($url);

        $ledgerAccountURL = config('thirdParty.tatumBaseURL') . '/v3/ledger/account';
        $requestData = [
            'currency' => strtoupper('bnb'),
            'xpub' => $bnbAccountResponse->object()->address,
            'customer' => [
                'externalId' => strval($userId),
                'accountingCurrency' => 'USD',
            ],
            'accountingCurrency' => 'USD',
        ];
        $ledgerAccountResponse = Http::withHeaders([
            'x-api-key' => $apiKey,
        ])->post($ledgerAccountURL, $requestData);

        if ($ledgerAccountResponse->successful()) {
            $tatumAccountId = $ledgerAccountResponse->object()->id;
            $tatumCustomerId = $ledgerAccountResponse->object()->customerId;
            $account = new Account();
            $account->user_id = $userId;
            $account->currency_id = $currencyId;
            $account->tatum_account_id = $tatumAccountId;
            $account->tatum_customer_id = $tatumCustomerId;
            $account->xpub = $bnbAccountResponse->object()->address;
            $account->private_key = $bnbAccountResponse->object()->privateKey;
            $account->save();
        }
    }

    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        if (
            Auth::attempt([
                'email' => $request->email,
                'password' => $request->password,
            ])
        ) {
            $user = Auth::user();
            $success['token'] = $user->createToken('MyApp')->accessToken;

            return $this->sendResponse($success, 'User login successfully.');
        } else {
            return $this->sendError('Unauthorised.', [
                'error' => 'Unauthorised',
            ]);
        }
    }
}
