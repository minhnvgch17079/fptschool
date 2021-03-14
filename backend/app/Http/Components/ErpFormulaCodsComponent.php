<?php

namespace App\Http\Components;

use App\Models\ErpCod;
use App\Models\ErpEmpContract;
use App\Models\ErpSgwCodSalaryFormula;
use App\Models\ErpSgwFormulaPosition;
use App\Models\ErpSgwFormulaSalary;
use App\Models\ErpSgwFormulaStation;
use App\Models\ErpSgwFormulaWorkType;
use App\Models\ErpStation;
use App\Models\ErpUser;

/**
 * Class ErpFormulaCodsComponent
 * @property ErpSgwFormulaSalary ErpSgwFormulaSalary
 * @property ErpSgwFormulaWorkType ErpSgwFormulaWorkType
 * @property ErpSgwFormulaPosition ErpSgwFormulaPosition
 * @property ErpSgwFormulaStation ErpSgwFormulaStation
 * @property ErpStation ErpStation
 * @property ErpCod ErpCod
 * @property ErpUser ErpUser
 * @property ErpSgwCodSalaryFormula ErpSgwCodSalaryFormula
 * @property TimeSheetsComponent TimeSheets
 * @property ErpEmpContract ErpEmpContract
 */
class ErpFormulaCodsComponent
{
    public $models = [
        'ErpSgwFormulaSalary', 'ErpSgwFormulaWorkType', 'ErpSgwFormulaPosition', 'ErpSgwFormulaStation', 'ErpStation', 'ErpCod', 'ErpUser', 'ErpSgwCodSalaryFormula', 'ErpEmpContract'
    ];

    public $components = [
        'Admin.TimeSheets', 'Admin.Calendar',
    ];

    public function __construct()
    {
        foreach ($this->models as $model) {
            $this->{$model} = getInstance($model);
        }
    }

    //Todo: Hàm lấy danh sách công thức lương, tên ct lương
    public function getListFormula()
    {
        $data = $this->ErpSgwFormulaSalary->find('all');
        $data = Hash::extract($data, '{n}.ErpSgwFormulaSalary');
        return $data;
    }

    //Todo: Hàm lưu mới or update dữ liệu formula
    public function saveDateFormula($data)
    {
        if (empty($data['formula']) || empty($data['name']) || empty($data['stations']) || empty($data['work_types']) || empty($data['positions'])) {
            return false;
        }
        $data['stations'] = array_unique($data['stations']);
        $data['work_types'] = array_unique($data['work_types']);
        $data['positions'] = array_unique($data['positions']);

        $created_user = AuthComponent::user('username') ?? 'admin';
        $formula = $this->ErpSgwFormulaSalary->findByFormula($data['formula']);
        $formulaSave = [
            'formula' => $data['formula'],
            'name' => $data['name'],
            'modified_by' => $created_user,
        ];
        if (!empty($formula)) {
            $formulaSave['id'] = $formula['ErpSgwFormulaSalary']['id'];
        }
        $formulaPositionSave = [];
        $formulaStationSave = [];
        $formulaWorkTypeSave = [];
        foreach ($data['stations'] as $st) {
            $formulaStationSave[] = [
                'formula' => $data['formula'],
                'station_id' => $st
            ];
        }
        foreach ($data['positions'] as $p) {
            $formulaPositionSave[] = [
                'formula' => $data['formula'],
                'position_id' => $p
            ];
        }
        foreach ($data['work_types'] as $wt) {
            $formulaWorkTypeSave[] = [
                'formula' => $data['formula'],
                'work_type_id' => $wt
            ];
        }
        $sourceData = $this->ErpSgwFormulaSalary->getDataSource();
        $sourceData->begin();
        try {
            if (!$this->ErpSgwFormulaSalary->save($formulaSave)) {
                $sourceData->rollback();
                return false;
            }
            if (!$this->ErpSgwFormulaStation->deleteAll(['formula' => $data['formula']])) {
                $sourceData->rollback();
                return false;
            }
            if (!$this->ErpSgwFormulaWorkType->deleteAll(['formula' => $data['formula']])) {
                $sourceData->rollback();
                return false;
            }
            if (!$this->ErpSgwFormulaPosition->deleteAll(['formula' => $data['formula']])) {
                $sourceData->rollback();
                return false;
            }
            if (!$this->ErpSgwFormulaStation->saveMany($formulaStationSave)) {
                $sourceData->rollback();
                return false;
            }
            if (!$this->ErpSgwFormulaWorkType->saveMany($formulaWorkTypeSave)) {
                $sourceData->rollback();
                return false;
            }
            if (!$this->ErpSgwFormulaPosition->saveMany($formulaPositionSave)) {
                $sourceData->rollback();
                return false;
            }
            $sourceData->commit();
            return true;
        } catch (Exception $exception) {
            pr('Error when update data formula: ' . $exception->getMessage());
            $sourceData->rollback();
            return false;
        }
    }

    //todo Hàm lấy dữ liệu config 1 formula
    public function getDataFormula($formula)
    {
        $dataFormula = $this->ErpSgwFormulaSalary->findByFormula($formula);
        $dataFormulaStation = $this->ErpSgwFormulaStation->find('all', [
            'fields' => [
                'Station.id', 'Station.name', 'Address.id', 'Address.name', 'Address.region'
            ],
            'conditions' => [
                'ErpSgwFormulaStation.formula' => $formula
            ],
            'joins' => [
                [
                    'table' => 'stations',
                    'alias' => 'Station',
                    'type' => 'INNER',
                    'conditions' => array(
                        'Station.working' => 1,
                        'Station.type' =>['station', 'post_office'],
                        'Station.id = ErpSgwFormulaStation.station_id',
                    )
                ],
                [
                    'table' => 'addresses',
                    'alias' => 'Address',
                    'type' => 'INNER',
                    'conditions' => array(
                        'Station.province_id = Address.id',
                    )
                ]
            ]
        ]);
        $dataFormulaRegion = array_unique(Hash::extract($dataFormulaStation, '{n}.Address.region'));
        $dataFormulaProvince = Hash::combine($dataFormulaStation, '{n}.Address.id', '{n}.Address.name');
        $dataFormulaWorkType = $this->ErpSgwFormulaWorkType->find('list', [
            'fields' => [
                'WorkType.id', 'WorkType.work_type_description'
            ],
            'conditions' => [
                'ErpSgwFormulaWorkType.formula' => $formula
            ],
            'joins' => [
                [
                    'table' => 'work_types',
                    'alias' => 'WorkType',
                    'type' => 'INNER',
                    'conditions' => array(
                        'WorkType.id = ErpSgwFormulaWorkType.work_type_id',
                    )
                ],
            ]
        ]);

        $dataFormulaPostion = $this->ErpSgwFormulaPosition->find('list', [
            'fields' => [
                'EmpPosition.id', 'EmpPosition.position_name'
            ],
            'conditions' => [
                'ErpSgwFormulaPosition.formula' => $formula
            ],
            'joins' => [
                [
                    'table' => 'emp_positions',
                    'alias' => 'EmpPosition',
                    'type' => 'INNER',
                    'conditions' => array(
                        'EmpPosition.id = ErpSgwFormulaPosition.position_id',
                    )
                ],
            ]
        ]);
        $responseData = [
            'formula' => $formula,
            'formula_name' => $dataFormula['ErpSgwFormulaSalary']['name'],
            'regions' => [],
            'provinces' => [],
            'stations' => [],
            'work_types' => [],
            'positions' => [],
        ];
        foreach ($dataFormulaStation as $valueSt) {
            $responseData['stations'][] = [
                'id' => $valueSt['Station']['id'],
                'name' => $valueSt['Station']['name'],
            ];
        }
        foreach ($dataFormulaRegion as $r) {
            $responseData['regions'][] = [
                'id' => $r,
                'name' => $this->ErpStation::$regions[$r],
            ];
        }
        foreach ($dataFormulaProvince as $idPv => $namePv) {
            $responseData['provinces'][] = [
                'id' => $idPv,
                'name' => $namePv,
            ];
        }
        foreach ($dataFormulaWorkType as $idWt => $nameWt) {
            $responseData['work_types'][] = [
                'id' => $idWt,
                'name' => $nameWt,
            ];
        }
        foreach ($dataFormulaPostion as $idP => $nameP) {
            $responseData['positions'][] = [
                'id' => $idP,
                'name' => $nameP,
            ];
        }
        return $responseData;
    }

    //todo: Hàm lọc theo buu cục kho
    public function getFilteredStations($stationIds, $optionDistrict, $optionStation) {
        if (empty($stationIds)) return null;
        $conditions = [
            'ErpStation.id' => $stationIds,
        ];
        if ($optionStation === 'post_office') {
            $conditions['ErpStation.type'] = 'post_office';
        }
        if ($optionStation === 'station') {
            $conditions['ErpStation.type'] = 'station';
        }
        if ($optionDistrict == 3) {
            $conditions['Address.type'] = 3;
        }
        if ($optionDistrict == 7) {
            $conditions['Address.type'] = 7;
        }
        $data = $this->ErpStation->find('list', [
            'fields' => ['ErpStation.id', 'ErpStation.name'],
            'conditions' => $conditions,
            'joins' => [
                [
                    'table' => 'addresses',
                    'alias' => 'Address',
                    'type' => 'LEFT',
                    'conditions' => [
                        'Address.id = ErpStation.district_id'
                    ]
                ]
            ]
        ]);
/*        $log = $this->ErpStation->getDataSource()->getLog(false, false);
        debug($log);die;*/
        return $data;
    }

    //Todo: Hàm xác định các công thức lương phù hợp cho 1 COD
    public function getFormulaCod($codId, $month = null, $year = null) {
        if (empty($month) || empty($year)) {
            $date = date('Y-m-d');
            $rangeTime = $this->TimeSheets->getSalaryMonthByDate($date);
            $month = $rangeTime['month'];
            $year = $rangeTime['year'];
        }
        $user_id = $this->ErpCod->getCodById($codId)['user_id'] ?? null;
        if (empty($user_id)) return null;
        $infoCod = $this->ErpUser->getInfoUser($user_id);
        if(empty($infoCod['station_id']) || empty($infoCod['position_job']) || empty($infoCod['work_type_id'])) {
            return null;
        }
        $currentFormulas = $this->getFormulaByInfo($infoCod['position_job'], $infoCod['work_type_id'], $infoCod['station_id']);
        $contract = $this->ErpEmpContract->getCurrentContract($user_id);
        if (in_array($contract['ErpEmpContract']['type_contract'] ?? null,['service_contract'])) rsort($currentFormulas);
        $prTimeStamp = strtotime("-1 month", strtotime("$year-$month-01"));
        $preMonth =  date('m', $prTimeStamp);
        $preYear =  date('Y', $prTimeStamp);
        $preFormula = $this->ErpSgwCodSalaryFormula->getFormulaCodInMonthSalary($codId, $preMonth, $preYear);
        $responseData = [
            'user_id' => $infoCod['user_id'],
            'cod_id' => $infoCod['cod_id'],
            'month' => $month,
            'year' => $year,
            'station_id' => $infoCod['station_id'],
            'position_id' => $infoCod['position_job'],
            'work_type_id' => $infoCod['work_type_id'],
            'formula' => null
        ];
        if (empty($currentFormulas)) {
            $responseData['formula'] =  $preFormula ?? 0; // không xác định được F mới, lấy F tháng cũ
        } elseif (!empty($currentFormulas) && !empty($preFormula) && in_array($preFormula, $currentFormulas)) {
            $responseData['formula'] = $preFormula; // nếu F tháng cũ có trong list F mới, lấy F tháng cũ
        } else {
            $responseData['formula'] = $currentFormulas[0] ?? 0; // ưu tiên phần tử đầu tiên F mới tìm được
        }
        if (!empty($preFormula) && in_array($preFormula, ErpCod::$SPECIAL_COD_FORMULA)) $responseData['formula'] = $preFormula; // Các công thức đặc biệt auto lấy từ tháng cũ
        return $responseData;
    }

    public function getFormulaByInfo($positionId, $workTypeId, $stationId) {
        //tạm fix cứng cho F2 Fteam HCM
        if ($positionId == 64 && $workTypeId == 1) {
            $province = $this->ErpStation->findById($stationId)['ErpStation']['province_id'] ?? null;
            if ($province == 126 || $province == 129) return [2002];
        }

        // tạm fix cứng cho cod pk huyện hà nội f35 f1035
//        if ($positionId == 59 && $workTypeId == 1 && in_array($stationId, [158,161,164,167,170,173,205,1426,1430,1434,1438,1442,1446,1450,5610,5614,5618,6857,6327])) {
//            return [1035];
//        }
        $activeFormulas = $this->ErpSgwFormulaSalary->getAllActiveFormula();
        $formulaStation = $this->ErpSgwFormulaStation->find('list', [
            'fields' => ['formula', 'formula'],
            'conditions' => [
                'station_id' => $stationId,
                'formula' => $activeFormulas
            ]
        ]);
        $formulaStation = array_values($formulaStation);

        $formulaPosition = $this->ErpSgwFormulaPosition->find('list', [
            'fields' => ['formula', 'formula'],
            'conditions' => [
                'position_id' => $positionId,
                'formula' => $activeFormulas
            ]
        ]);
        $formulaPosition = array_values($formulaPosition);

        $formulaWorkType = $this->ErpSgwFormulaWorkType->find('list', [
            'fields' => ['formula', 'formula'],
            'conditions' => [
                'work_type_id' => $workTypeId,
                'formula' => $activeFormulas
            ]
        ]);
//        pd( $infoCod['work_type_id']);
        $formulaWorkType = array_values($formulaWorkType);

        $currentFormulas = array_intersect($formulaPosition, $formulaWorkType, $formulaStation);
        if (empty($currentFormulas)) {
            $provinceId = $this->ErpStation->getProvinceId($stationId);
            if (!in_array($provinceId, [1, 126, 129])) {
                if ($workTypeId == 1 && $positionId == 59) { // fulltime, pkb2c
                    $currentFormulas = [2111];
                } else {
                    $currentFormulas = [1000];
                }
            }
        }
        return array_values($currentFormulas);
    }
}
