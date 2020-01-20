<?php

namespace App\Console\Commands\Commodities;

use Illuminate\Console\Command;
use App\Commodity;

class Prices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commodity:prices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'API Call to Quandl for commodity price data';

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
        $commodities = Commodity::all();        

        $indicators = collect([
            'gold' => 'CME_GC1',
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
        $url = "https://www.quandl.com/api/v3/datasets/";
        $api_key = "Z8h3qyUutR2CP3jYpscW";

        foreach ($countries as $country) {
            try {
                $this->comment($url . $country->code . '/indicator/' . $indicator_codes . $query);
                $response = Request::get($url . $country->code . '/indicator/' . $indicator_codes . $query);
                if ($response->code == 200) {
                    $array = last($response->body);
                    $collection = collect();

                    if (gettype($array) == 'array') {
                        if (count($array) > 1) {
                            foreach ($array as $object) {
                                if (empty($object->value)) {
                                    continue;
                                }
                                $collection->push([
                        'indicator' => $object->indicator->id,
                        'date' => $object->date,
                        'value' => $object->value
                    ]);
                            }
                            $grouped_stats = $collection->groupBy('indicator');
                            $data = collect();
                            $grouped_stats->each(function ($group, $indicator) use ($data) {
                                $filtered = $group->map(function ($set) {
                                    unset($set['indicator']);
                                    return $set;
                                });
                                $data->put($indicator, $filtered);
                            });

                            $indicators->each(function ($id, $indicator) use ($data, $country) {
                                if (isset($data[$id])) {
                                    $country->update([
                            $indicator =>  $data[$id]
                        ]);
                                    $this->info("saved $country->name $indicator");
                                }
                            });
                        } else {
                            $this->error($array);
                        }
                    } else {
                        $this->error(gettype($array));
                    }
                } else {
                    $this->error($response->code . ' - ' . $response->headers);
                }
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
