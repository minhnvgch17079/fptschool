<?php

namespace App\Console\Commands;
use App\Console\Commands\BaseShell;
use App\Http\Components\TimeSheetsComponent;
use App\Http\Components\ErpSalaryCodProvinceComponent;
use App\Models\ErpSgwCodSalaryFormula;
use App\Http\Components\KafkaComponent;
/**
 * @property ErpSgwCodSalaryFormula ErpSgwCodSalaryFormula
 * @property TimeSheetsComponent TimeSheetsComponent
 * @property ErpSalaryCodProvinceComponent ErpSalaryCodProvincesComponent
 * @property KafkaComponent KafkaComponent
 * */

class SosShell extends BaseShell {
    protected $signature    = 'sos { function } {args?*}';
    protected $description  = 'Run each job with each worker';

}
