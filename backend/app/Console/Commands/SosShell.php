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

    public function calSalaryAllCodProvinceByDate () {
        $date = $this->args[0] ?? null;

        if (empty($date)) {
            $this->out('Missing date');
            return 1;
        }

        $this->ErpSgwCodSalaryFormula         = getInstance('ErpSgwCodSalaryFormula');
        $this->TimeSheetsComponent            = new TimeSheetsComponent();
        $this->ErpSalaryCodProvincesComponent = new ErpSalaryCodProvinceComponent();

        $salaryMonth        = $this->TimeSheetsComponent->getSalaryMonthByDate($date);
        $listCodProvince    = $this->ErpSgwCodSalaryFormula->getListCodsByMonthSalary($salaryMonth['month'], $salaryMonth['year'], 1000);

        if (empty($listCodProvince)) {
            $this->out('No list cod province founded');
            return 1;
        }

        $isSuccess = 0;
        $c = 0; $cc = count($listCodProvince);
        foreach ($listCodProvince as $cod) {
            $this->out(++$c . " / " . $cc);
            $result = $this->ErpSalaryCodProvincesComponent->calSalForEachCodProvinceByDate($cod['cod_id'], $date, $date, 1000);
            if (empty($result)) $isSuccess = 1;
        }
        return $isSuccess;
    }

    public function test () {
        pd(getenv('BIG_DATA_HOST'));
    }
}
