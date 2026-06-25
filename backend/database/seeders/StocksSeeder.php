<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Stock;
use App\Models\StockPrice;
use Illuminate\Database\Seeder;

/**
 * Seeds Nifty 50 stocks with realistic reference prices.
 * Prices will be overwritten on first market data refresh — these are placeholders.
 */
final class StocksSeeder extends Seeder
{
    public function run(): void
    {
        $stocks = [
            ['symbol' => 'RELIANCE',    'name' => 'Reliance Industries Ltd.',      'sector' => 'Energy',                  'is_nifty50' => true,  'ltp_paise' => 294600],
            ['symbol' => 'TCS',         'name' => 'Tata Consultancy Services Ltd.','sector' => 'Information Technology',  'is_nifty50' => true,  'ltp_paise' => 389500],
            ['symbol' => 'INFY',        'name' => 'Infosys Ltd.',                  'sector' => 'Information Technology',  'is_nifty50' => true,  'ltp_paise' => 163000],
            ['symbol' => 'HDFCBANK',    'name' => 'HDFC Bank Ltd.',                'sector' => 'Financial Services',      'is_nifty50' => true,  'ltp_paise' => 156000],
            ['symbol' => 'ICICIBANK',   'name' => 'ICICI Bank Ltd.',               'sector' => 'Financial Services',      'is_nifty50' => true,  'ltp_paise' => 123000],
            ['symbol' => 'HINDUNILVR',  'name' => 'Hindustan Unilever Ltd.',       'sector' => 'Consumer Goods',          'is_nifty50' => true,  'ltp_paise' => 267000],
            ['symbol' => 'ITC',         'name' => 'ITC Ltd.',                      'sector' => 'Consumer Goods',          'is_nifty50' => true,  'ltp_paise' => 46800],
            ['symbol' => 'SBIN',        'name' => 'State Bank of India',           'sector' => 'Financial Services',      'is_nifty50' => true,  'ltp_paise' => 82300],
            ['symbol' => 'BHARTIARTL', 'name' => 'Bharti Airtel Ltd.',             'sector' => 'Telecom',                 'is_nifty50' => true,  'ltp_paise' => 160000],
            ['symbol' => 'KOTAKBANK',   'name' => 'Kotak Mahindra Bank Ltd.',      'sector' => 'Financial Services',      'is_nifty50' => true,  'ltp_paise' => 193000],
            ['symbol' => 'LT',          'name' => 'Larsen & Toubro Ltd.',          'sector' => 'Industrials',             'is_nifty50' => true,  'ltp_paise' => 362000],
            ['symbol' => 'ASIANPAINT',  'name' => 'Asian Paints Ltd.',             'sector' => 'Consumer Goods',          'is_nifty50' => true,  'ltp_paise' => 247000],
            ['symbol' => 'AXISBANK',    'name' => 'Axis Bank Ltd.',                'sector' => 'Financial Services',      'is_nifty50' => true,  'ltp_paise' => 117000],
            ['symbol' => 'MARUTI',      'name' => 'Maruti Suzuki India Ltd.',      'sector' => 'Automobiles',             'is_nifty50' => true,  'ltp_paise' => 1275000],
            ['symbol' => 'SUNPHARMA',   'name' => 'Sun Pharmaceutical Industries','sector' => 'Healthcare',               'is_nifty50' => true,  'ltp_paise' => 163000],
            ['symbol' => 'TITAN',       'name' => 'Titan Company Ltd.',            'sector' => 'Consumer Goods',          'is_nifty50' => true,  'ltp_paise' => 351000],
            ['symbol' => 'BAJFINANCE',  'name' => 'Bajaj Finance Ltd.',            'sector' => 'Financial Services',      'is_nifty50' => true,  'ltp_paise' => 698000],
            ['symbol' => 'WIPRO',       'name' => 'Wipro Ltd.',                    'sector' => 'Information Technology',  'is_nifty50' => true,  'ltp_paise' => 52000],
            ['symbol' => 'HCLTECH',     'name' => 'HCL Technologies Ltd.',         'sector' => 'Information Technology',  'is_nifty50' => true,  'ltp_paise' => 165000],
            ['symbol' => 'ULTRACEMCO',  'name' => 'UltraTech Cement Ltd.',         'sector' => 'Cement',                  'is_nifty50' => true,  'ltp_paise' => 1094000],
        ];

        foreach ($stocks as $data) {
            $ltp = $data['ltp_paise'];
            $stock = Stock::updateOrCreate(['symbol' => $data['symbol']], [
                'name'         => $data['name'],
                'exchange'     => 'NSE',
                'sector'       => $data['sector'],
                'is_active'    => true,
                'is_nifty50'   => $data['is_nifty50'],
                'is_tradeable' => true,
            ]);

            StockPrice::updateOrCreate(['stock_id' => $stock->id], [
                'symbol'        => $stock->symbol,
                'ltp_paise'     => $ltp,
                'open_paise'    => (int) ($ltp * 0.995),
                'high_paise'    => (int) ($ltp * 1.01),
                'low_paise'     => (int) ($ltp * 0.99),
                'close_paise'   => (int) ($ltp * 0.998),
                'change_paise'  => (int) ($ltp * 0.002),
                'change_percent'=> 0.20,
                'volume'        => random_int(1000000, 50000000),
                'market_status' => 'closed',
                'quoted_at'     => now(),
            ]);
        }
    }
}
