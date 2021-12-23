<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrenciesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('currencies')->insert([
            [
                'name' => 'Bitcoin',
                'label' => 'btc',
                'blockchain_name' => 'Bitcoin',
                'blockchain_label' => 'btc',
                'tatum_name' => 'bitcoin',
                'tatum_label' => 'btc',
                'icon_url' =>
                    'https://icon-library.com/images/bitcoin-icon/bitcoin-icon-16.jpg',
            ],
            [
                'name' => 'Ethereum',
                'label' => 'eth',
                'blockchain_name' => 'Ethereum',
                'blockchain_label' => 'eth',
                'tatum_name' => 'ethereum',
                'tatum_label' => 'eth',
                'icon_url' =>
                    'https://icon-library.com/images/bitcoin-icon/bitcoin-icon-16.jpg',
            ],
            [
                'name' => 'Binance Coin',
                'label' => 'bnb',
                'blockchain_name' => 'Binance',
                'blockchain_label' => 'bnb',
                'tatum_name' => 'binance coin',
                'tatum_label' => 'bnb',
                'icon_url' =>
                    'https://icon-library.com/images/bitcoin-icon/bitcoin-icon-16.jpg',
            ],
            [
                'name' => 'Kryptonium',
                'label' => 'lion',
                'blockchain_name' => 'Binance Smart Chain',
                'blockchain_label' => 'bsc',
                'tatum_name' => 'bsc',
                'tatum_label' => 'bsc',
                'icon_url' =>
                    'https://icon-library.com/images/bitcoin-icon/bitcoin-icon-16.jpg',
            ],
        ]);
    }
}
