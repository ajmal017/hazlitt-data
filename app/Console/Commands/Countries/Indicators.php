<?php

namespace App\Console\Commands\Countries;

use Illuminate\Console\Command;
use App\Country;
use Illuminate\Support\Carbon;
use Unirest\Request;
use Unirest\Request\Body;
use Illuminate\Support\Arr;

class IndicatorsNew extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var strings
     */
    protected $signature = 'country:indicators';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collect indicators by country from World Bank';

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
        // $client = new Client();

        $indicators = collect([
            'population' => 'SP.POP.TOTL',
            'gdp' => 'NY.GDP.MKTP.CD',
            'inflation' => 'FP.CPI.TOTL.ZG',
            'corporate_tax' => 'IC.TAX.TOTL.CP.ZS',
            'interest_rate' => 'FR.INR.RINR',
            'income' => 'NY.GNP.PCAP.CD',
            'personal_savings' => 'NY.ADJ.SVNG.GN.ZS',
            'unemployment_rate' => 'SL.UEM.TOTL.ZS',
            'labor_force' => 'SL.TLF.CACT.ZS',
            'income_tax' => 'GC.TAX.YPKG.ZS',
            'gov_debt_to_gdp' => 'GC.DOD.TOTL.GD.ZS',
            'bank_reserves' => 'FI.RES.TOTL.CD',
            'budget' => 'GC.NLD.TOTL.CN'
        ]);

        $indicator_codes = $indicators->join(';');
        $url = "https://api.worldbank.org/v2/country/";
        $query = "?source=2&per_page=16000&format=json";

        foreach ($countries as $country) {
            try {
                $this->comment($url . $country->code . '/indicator/' . $indicator_codes . $query);
                $response = Request::get($url . $country->code . '/indicator/' . $indicator_codes . $query);
                $array = last($response->body);
                
                $figures = collect();
                foreach ($array as $object) {
                    if (empty($object->value)) {
                        continue;
                    }
                    $figures->push([
                        'indicator' => $object->indicator->id,
                        'date' => $object->date,
                        'value' => $object->value
                    ]);
                }
                $data = $figures->groupBy('indicator');

                $indicators->each(function ($id, $indicator) use ($data, $country) {
                    if (isset($data[$id])) {
                        $country->update([
                            $indicator => $data[$id][0]['indicator'] == $id ? $data[$id] : null,
                        ]);
                        $this->info("saved $indicator");
                    }
                });                
            } catch (\Exception $e) {
                $this->error($e);
                report($e);
            }
        }
        $end = microtime(true);
        $time = number_format(($end - $start) / 60);
        $this->info("\n" . 'Done: ' . $time . ' minutes');
    }
}
