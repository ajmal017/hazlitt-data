<?php

namespace App\Console\Commands\Countries;

use Illuminate\Console\Command;
use App\Country;
use App\Commodity;
use Illuminate\Support\Carbon;
use Goutte\Client;

class Indicators extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'country:indicators';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape country indicators';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $start = microtime(true);
        $countries = Country::all();
        $client = new Client();

        foreach ($countries as $country) {

            $pages = collect([
                'inflation' => 'inflation-cpi',
                'corporate_tax' => 'corporate-tax-rate',
                'interest_rate' => 'interest-rate',
                'unemployment_rate' => 'unemployment-rate',
                'labor_force' => 'labor-force-participation-rate',
                'income_tax' => 'personal-income-tax-rate',
                'gdp' => 'gdp-per-capita',
                'gov_debt_to_gdp' => 'government-debt-to-gdp',
                'central_bank_balance_sheet' => 'central-bank-balance-sheet',
                'budget' => 'government-budget-value'
            ]);

            $pages->each(function ($slug, $indicator) use ($country, $client) {

                try {

                    $this->comment('https://tradingeconomics.com/' . $country->slug .  '/' . $slug);
                    $crawler = $client->request('GET', 'https://tradingeconomics.com/' . $country->slug .  '/' . $slug);

                    $crawler->filter('body')->each(function ($node) use ($country, $indicator) {
                        if (!isset($node)) {
                            $this->error($country->name . ' ' . $indicator . ' indicator missing.');
                            return;
                        }

                        if (
                            $node->filter('#ctl00_ContentPlaceHolder1_ctl03_PanelDefinition td:nth-child(2)')->count() == 0
                        ) {
                            $this->error($country->name . ' ' . $indicator . ' indicator missing.');
                            return;
                        }

                        $country = Country::query()
                            ->whereName($country->name);

                        $country->update([
                            $indicator => $node->filter('#ctl00_ContentPlaceHolder1_ctl03_PanelDefinition td:nth-child(2)')->text()
                        ]);
                    });                    
                    $this->info('Saved ' . $country->name . ' ' . $indicator);                    

                } catch (\Exception $e) {
                    $this->error($e);
                    report($e);
                }
            });
        }
        $end = microtime(true);
        $time = number_format(($end - $start)/60);
        $this->info("\n" . 'Done: ' . $time . ' minutes');
    }
}
