<?php

namespace App\Http\Components;

use App\Http\Components\TimeSheetsComponent;
use App\Http\Components\CalendarComponent;
use App\Models\BigDataConfigWorkShiftSachTuyen;
use App\Models\BigDataStationProcessCod;
use App\Models\BigDataSuccessByWs;
use App\Models\ErpCod;
use App\Models\ErpCheckinLog;
use App\Models\ErpConfigSalaryCod;
use App\Models\ErpEmpContract;
use App\Models\ErpSgwCodSalaryKpi;
use App\Models\ErpUser;
use App\TraitKpi\ErpSalaryTable;

/**
 * @property TimeSheetsComponent TimeSheets
 * @property ErpCod ErpCod
 * @property BigDataStationProcessCod BigDataStationProcessCod
 * @property ErpConfigSalaryCod ErpConfigSalaryCod
 * @property CalendarComponent Calendar
 * @property BigDataSuccessByWs BigDataSuccessByWs
 * @property ErpCheckinLog ErpCheckinLog
 * @property BigDataConfigWorkShiftSachTuyen BigDataConfigWorkShiftSachTuyen
 * @property ErpSgwCodSalaryKpi ErpSgwCodSalaryKpi
 * @property ErpUser ErpUser
 * @property ErpEmpContract ErpEmpContract
 * */

class ErpSalaryCodProvinceComponent {

    public $models = [
        'ErpCod', 'BigDataStationProcessCod', 'ErpConfigSalaryCod', 'ErpSgwCodSalaryKpi', 'ErpUser', 'ErpEmpContract'
    ];

    public function __construct() {
        $this->TimeSheets = new TimeSheetsComponent();
        $this->Calendar   = new CalendarComponent();

        foreach ($this->models as $model) {
            $this->{$model} = getInstance($model);
        }
    }

    public static $MAX_SESSION_BY_WORK_TYPE = [11 => 1, 12 => 1, 2 => 1];

    public static $CONVERT_INTEGRATION = [
        2 => 1.2, 3 => 1.5
    ];

    /*-----------------------------------------------LOGIC TÍNH LƯƠNG-------------------------------------------------*/
    // todo: Danh sách các ngày bỏ phạt tốc độ và phiên
    private $LIST_DATE_EXCLUDE_T6_2020_V1 = [
        '2020-05-30',
        '2020-06-11',
        '2020-06-15'
    ];

    //todo: Danh Sách Các Tỉnh Bỏ Phạt
    private $LIST_PROVINCE_EXCLUDE_V1 = [
        'binh_duong' => 839,
        'dong_nai' => 823,
        'long_an' => 855
    ];

    public $LIST_DATE_NOT_CAL  = []; // Loại những ngày này không tính lương cho cod

    private $dateStartDTeam = '2020-10-21';
    private $dateStartFine = '2020-11-21';
    private $DATE = null;
    private $DATE_START_PUNISH = null; // Ngày bắt đầu tính phạt cho cod

    private $LIST_COD_NOT_BP_T8_N2020 = [
        'T345215', 'T400030', 'T3295', 'T329369', 'T354611', 'T374573', 'T392683', 'T399748', 'T366320', 'T189676', 'T236624',
        'T97478', 'T19097', 'T406075', 'T185276', 'T375449', 'T102220', 'T406891', 'T358667', 'T367775', 'T75501', 'T398290',
        'T230327', 'T292484', 'T320399', 'T113243', 'T293588', 'T358232', 'T392443', 'T140788', 'T125019', 'T334676', 'T352415',
        'T401575', 'T313613', 'T386413', 'T127703', 'T403015', 'T371192', 'T362063', 'T237948', 'T379880', 'T390931', 'T50098',
        'T388744', 'T16853', 'T136864', 'T379868', 'T336224', 'T370715', 'T371471', 'T3090', 'T353933', 'T126903', 'T398923',
        'T314564', 'T78997', 'T301439', 'T328517', 'T362087', 'T323345', 'T152351', 'T373721', 'T356948', 'T210178', 'T131444',
        'T210786', 'T360875', 'T303170', 'T368777', 'T101708', 'T170987', 'T334589', 'T296795', 'T94338', 'T398137', 'T386392',
        'T112099'
    ];

    private $CONFIG_BP = [];

    public function extractConfigFineForDate ($configFine, $infoCod, $date) {
        $this->CONFIG_BP = [];
        if (empty($configFine)) return true;

        foreach ($configFine as $config) {
            $workType  = json_decode($config['work_type'], true);
            $fines     = json_decode($config['fine'], true);
            $workType  = array_map(function ($e) { return $e['id']; }, $workType);
            $startDate = $config['from_date'];
            $toDate    = $config['to_date'];

            if (
                !empty($config['province_id']) &&
                $infoCod['province_id'] != $config['province_id']
            ) continue;

            if (
                !empty($config['station_id']) &&
                $infoCod['station_id'] != $config['station_id']
            ) continue;

            if (!empty($fines['alias_apply']) && !in_array($infoCod['alias'], $fines['alias_apply'])) continue;
            if (!empty($fines['alias_not_apply']) && in_array($infoCod['alias'], $fines['alias_not_apply'])) continue;

            foreach ($fines as $fine) {
                if (empty($fine['id'])) continue;

                // Chỉ extract loại bỏ phạt ngày hoặc tháng
                if (in_array($fine['id'], ['session_2_2', 'session_0_2', 'session_1_1', 'session_0_1'])) continue;

                if (strtotime($date) >= strtotime($startDate) && strtotime($date) <= strtotime($toDate)) {
                    $this->CONFIG_BP[$fine['id']] = $fine['work_shift'] ?? '';
                }
            }
        }
    }

    public function calSalForEachCodProvinceByDate ($codId, $from_date, $to_date, $formula) {
        $date        = date('Y-m-d', strtotime($from_date));
        $this->DATE  = $date;
        $salaryMonth = $this->TimeSheets->getSalaryMonthByDate($date);
        $infoCod     = $this->ErpCod->getInfoCod($codId);
        $this->DATE_START_PUNISH = date('Y-m-d', strtotime('+30 days', strtotime($infoCod['active_date'])));
        $data        = $this->BigDataStationProcessCod->getAllDataCodProvinceByListDate($salaryMonth['from'], $date, $infoCod['cod_order']);
        $configFine  = $this->ErpConfigSalaryCod->getConfigFineForCal($date, $infoCod['region'], $infoCod['province_id'], $infoCod['station_id']);
        $this->extractConfigFineForDate($configFine, $infoCod, $date);

        if (empty($data)) return true;

        $isSunday = $this->Calendar->checkDateIsSunday($date);

        $dataSave = [
            'date'      => $date,
            'cod_id'    => $codId,
            'formula'   => $formula
        ];

        $fix = [
            // lương
            'd_reward_speed_salary'     => 0, // tổng lương thưởng tốc độ giao
            'd_reward_quality_salary'   => 0, // tổng lương thưởng chất lượng giao
            'p_reward_quality_salary'   => 0, // tổng lương chất lượng lấy
            'r_reward_quality_salary'   => 0, // tổng lương chất lượng trả
            'r_add_salary'              => 0, // tổng lương vượt 20 đơn trả
            'p_add_salary'              => 0, // tổng lương vượt 20 đơn lấy hoặc lấy bằng xe tải
            // sản lượng
            'd_pkg_reward_speed'    => 0, // tổng đơn thưởng tốc độ giao
            'd_pkg_reward_quality'  => 0, // tổng đơn thưởng chất lượng giao
            'p_pkg_reward_quality'  => 0, // tổng đơn thưởng chất lượng lấy
            'r_pkg_reward_quality'  => 0, // tổng đơn thưởng chất lượng trả
            'over_time'             => 0, // tổng công overtime
            'fine_clean_salary'     => 0, // Phạt không sạch phiên
            'fine_clean_package'    => 0,
            'total_workshift_work'  => 0 // tổng số phiên
        ];
        $dataInDate = $dataArrShift = [];
        $dataForValidateSessionByWs = [
            'ws_1' => ['v_1_4' => ['ss' => 0, 'at' => 0], 'v_5_8' => ['ss' => 0, 'at' => 0]],
            'ws_2' => ['v_1_4' => ['ss' => 0, 'at' => 0], 'v_5_8' => ['ss' => 0, 'at' => 0]],
            'ws_3' => ['v_1_4' => ['ss' => 0, 'at' => 0], 'v_5_8' => ['ss' => 0, 'at' => 0]]
        ];
        $isCalArea5_8 = false;

        foreach ($data as $detailData) {
            $deliverIndicator = $detailData['d_total'];
            if (strtotime($date) > strtotime('2020-06-20')) $deliverIndicator = $detailData['d_success'];

            if ($detailData['p_success'] > 0 || $detailData['r_success'] > 0 || $deliverIndicator > 0) {
                $isCalArea5_8 = true;
                break;
            }
        }

        foreach ($data as $detailData) {
            $workShift  = 'ws_' . $detailData['workshift'];
            $area       = $detailData['area'];
            $overPick   = $detailData['p_pkg_bonus'] + $detailData['p_truck']; // Thù lao lấy vượt 20 đơn trên điểm hoặc xe tải đón tại shop

            // Check phiên hợp lệ hay không
            $deliverIndicator = $detailData['d_total'];
            if (strtotime($date) > strtotime('2020-06-20')) $deliverIndicator = $detailData['d_success'];

            $totalPkg         = $deliverIndicator + $detailData['p_success'] + $detailData['r_success'];
            $totalPkgByActive = $detailData['d_is_active'] + $detailData['p_is_active'] + $detailData['r_is_active'];

            if ($area <= 4) {
                $dataForValidateSessionByWs[$workShift]['v_1_4']['ss'] += $totalPkg;
            } elseif (strtotime($date) > strtotime('2020-07-20')) {
                if ($isCalArea5_8) {
                    $dataForValidateSessionByWs[$workShift]['v_5_8']['ss'] += $totalPkg;
                    $dataForValidateSessionByWs[$workShift]['v_5_8']['at'] += $totalPkgByActive;
                }
            } else {
                $dataForValidateSessionByWs[$workShift]['v_5_8']['ss'] += $totalPkg;
                $dataForValidateSessionByWs[$workShift]['v_5_8']['at'] += $totalPkgByActive;
            }
            // group dữ liệu theo ca
            $dataInDate[$workShift][$area] = $detailData;
            // lương lấy trả vượt 20 đơn hoặc xe tải đón tại shop
            $fix['p_add_salary'] += $overPick * ErpSalaryTable::$F1000_SALARY_OVER_PICK_RETURN_DELIVER;
            $fix['r_add_salary'] += $detailData['r_pkg_bonus'] * ErpSalaryTable::$F1000_SALARY_OVER_PICK_RETURN_DELIVER;
        }

        $this->BigDataSuccessByWs = getInstance('BigDataSuccessByWs');
        $dataOverAll = $this->BigDataSuccessByWs->getDataInDate($salaryMonth['from'], $date, $infoCod['cod_order']);

        foreach ($dataInDate as $workShift => $dataInWorkShift) {

            $dataArrShift[$workShift]['validate_session'] = 0;

            $totalV_1_4      = array_sum($dataForValidateSessionByWs[$workShift]['v_1_4']);
            $totalV_5_8      = array_sum($dataForValidateSessionByWs[$workShift]['v_5_8']);
            $totalV_1_4_ss   = $dataForValidateSessionByWs[$workShift]['v_1_4']['ss'] ?? 0;
            $totalV_5_8_ss   = $dataForValidateSessionByWs[$workShift]['v_5_8']['ss'] ?? 0;
            $totalPkgSuccess = $totalV_1_4_ss + $totalV_5_8_ss;

            // Tu ky luong thang 9 sua lai logic tinh phu cap linh dong, xet phien dat it nhat 10 don thanh cong
            if (strtotime($this->DATE) > strtotime('2020-08-20')) {
                $total4Tuyen = 0;
                array_walk($dataInWorkShift, function ($e) use (&$total4Tuyen) {
                    $total4Tuyen += ($e['d_is_4_tuyen'] ?? 0) + ($e['p_is_4_tuyen'] ?? 0);
                });
            }

            // Validate phiên theo rule bình thường. Lớn hơn 0 được coi là phiên hợp lệ
            if ($totalV_1_4 > 0) {
                $dataArrShift[$workShift]['validate_session'] = 1;
                if (!empty($total4Tuyen) && $totalPkgSuccess > 9) $dataArrShift[$workShift]['validate_allowance'] = 1;
            }
            if ($totalV_5_8 > 0) {
                $dataArrShift[$workShift]['validate_session'] = 1;
                if (!empty($total4Tuyen) && $totalPkgSuccess > 9) $dataArrShift[$workShift]['validate_allowance'] = 1;
            }

            // Trong một ca hoạt động có đơn hàng ở cả hai vùng
            if ($totalV_1_4 > 0 && $totalV_5_8 > 0 && $totalV_5_8 > $totalV_1_4) {
                $dataArrShift[$workShift]['validate_session'] = 2;
                if (!empty($total4Tuyen) && $totalPkgSuccess > 9) $dataArrShift[$workShift]['validate_allowance'] = 2;
            }

            if (strtotime($date) > strtotime('2020-09-30')) {

                if ($totalV_1_4_ss + $totalV_5_8_ss < 5) $dataArrShift[$workShift]['validate_session'] = 0;
                else $dataArrShift[$workShift]['validate_session'] = 1;

                if (
                    ($totalV_5_8_ss > 4) ||
                    ($totalV_1_4_ss > 0 && $totalV_5_8_ss > ($totalPkgSuccess / 2))
                ) $dataArrShift[$workShift]['validate_session'] = 2;

            }

            //Logic bổ sung mới từ kỳ lương tháng 8 năm 2020
            if (strtotime($date) > strtotime('2020-07-20')) {

                // Phiên chỉ có thao tác không có phát sinh đơn thành công
                if ($dataForValidateSessionByWs[$workShift]['v_1_4']['ss'] == 0 && $dataForValidateSessionByWs[$workShift]['v_5_8']['ss'] == 0) $dataArrShift[$workShift]['validate_session'] = 0;

                // Vùng 5-8 có 10 đơn thành công trở lên auto tính 2 phiên tính từ kỳ lương tháng 8 năm 2020
                if ($dataForValidateSessionByWs[$workShift]['v_5_8']['ss'] > 9) $dataArrShift[$workShift]['validate_session'] = 2;
            }

            // Các data lương tính theo từng vùng của từng ca
            $dataRewardSpeed    = $this->salaryBonusSpeed($dataInWorkShift, $infoCod); // Thưởng tốc độ giao hàng trung bình trên ca
            $dataRewardDeliver  = $this->salaryBonusDeliver($dataInWorkShift, $infoCod, $salaryMonth, $dataOverAll); // Thưởng chất lượng giao
            $dataRewardPick     = $this->salaryBonusPick($dataInWorkShift, $infoCod, $salaryMonth); // Thưởng chất lượng lấy
            $dataRewardReturn   = $this->salaryBonusReturn($dataInWorkShift, $infoCod, $salaryMonth); // Thưởng chất lượng trả

            // Thưởng làm ca 3
            if ($workShift == 'ws_3') {
                $dataRewardNight = $this->salaryAllowanceOnNight($dataInWorkShift);
                $dataArrShift[$workShift]['kpi_reward_cod_province']['bonus_work_on_night'] = $dataRewardNight;
                $fix['bonus_work_on_night'] = $dataRewardNight;
            }

            // Thưởng làm chủ nhật
            if ($isSunday) {
                $indicatorOverTime = $this->calSalaryOverTimeSunday($dataInWorkShift);
                if (!empty($indicatorOverTime)) {
                    $dataArrShift[$workShift]['over_time'] = $indicatorOverTime;
                    $fix['over_time'] += $indicatorOverTime;
                }
            }

            $dataArrShift[$workShift]['kpi_reward_cod_province'] = [
                //deliver speed
                'd_reward_speed_salary'     => floor($dataRewardSpeed['total_salary'] * 100) / 100,
                'd_pkg_reward_speed'        => floor($dataRewardSpeed['reward_deliver_speed'] * 100) / 100,
                'd_speed_area_status'       => $dataRewardSpeed['area_status'],
                'd_speed_average'           => $dataRewardSpeed['average_speed_deliver'],
                // deliver quality
                'd_reward_quality_salary'   => floor($dataRewardDeliver['total_salary'] * 100) / 100,
                'd_pkg_reward_quality'      => floor($dataRewardDeliver['reward_deliver_quantity'] * 100) / 100,
                'route_clean_percent'       => $dataRewardDeliver['route_clean_percent'],
                'fine_clean_package'        => floor($dataRewardDeliver['fine_clean_package'] * 100) / 100,
                // pickup quality
                'p_reward_quality_salary'   => floor($dataRewardPick['total_salary'] * 100) / 100,
                'p_pkg_reward_quality'      => floor($dataRewardPick['reward_pick_quantity'] * 100) / 100,
                // return quality
                'r_reward_quality_salary'   => floor($dataRewardReturn['total_salary'] * 100) / 100,
                'r_pkg_reward_quality'      => floor($dataRewardReturn['reward_return_quantity'] * 100) /100,
            ];

            $fix['d_reward_speed_salary']   += floor($dataRewardSpeed['total_salary'] * 100) / 100;
            $fix['d_pkg_reward_speed']      += floor($dataRewardSpeed['reward_deliver_speed'] * 100) / 100;
            // deliver quality
            $fix['d_reward_quality_salary'] += floor($dataRewardDeliver['total_salary'] * 100) / 100;
            $fix['d_pkg_reward_quality']    += floor($dataRewardDeliver['reward_deliver_quantity'] * 100) / 100;
            $fix['fine_clean_salary']       += $dataRewardDeliver['fine_clean_salary'] ?? 0;
            $fix['fine_clean_package']      += $dataRewardDeliver['fine_clean_package'] ?? 0;
            // pickup quality
            $fix['p_reward_quality_salary'] += floor($dataRewardPick['total_salary'] * 100) / 100;
            $fix['p_pkg_reward_quality']    += floor($dataRewardPick['reward_pick_quantity'] * 100) / 100;
            // return quality
            $fix['r_reward_quality_salary'] += floor($dataRewardReturn['total_salary'] * 100) / 100;
            $fix['r_pkg_reward_quality']    += floor($dataRewardReturn['reward_return_quantity'] * 100) /100;
            $fix['total_workshift_work']    = (int)($dataRewardDeliver['total_workshift_work'] ?? 0);
        }
        if (empty($fix)) return true;
        foreach ($dataArrShift as $workShift => $value) {
            $dataSave[$workShift] = json_encode(array_filter($value));
        }
        $dataSave['fix'] = json_encode($fix);
        try {
            $this->ErpSgwCodSalaryKpi->deleteByCodIdDate($codId, $date);
            if (!empty($this->ErpSgwCodSalaryKpi->save($dataSave))) return true;
        } catch (Throwable $ex) {
            report("Error when save data to sgw_cod_salary_kpi" . $ex->getMessage());
            return false;
        }
    }

    // todo: Thưởng tuần
    public function salaryRewardPerWeek ($dataByWeek, $salaryMonth) {
        $dataReturn = [
            1 => ['total' => 0, 'success' => 0, 'percent' => 0, 'salary' => 0, 'from' => null, 'to' => null],
            2 => ['total' => 0, 'success' => 0, 'percent' => 0, 'salary' => 0, 'from' => null, 'to' => null ],
            3 => ['total' => 0, 'success' => 0, 'percent' => 0, 'salary' => 0, 'from' => null, 'to' => null],
            4 => ['total' => 0, 'success' => 0, 'percent' => 0, 'salary' => 0, 'from' => null, 'to' => null],
            'total_salary' => 0,
            'total_reward'  => 0
        ];

        if (empty($dataByWeek)) return $dataReturn;

        foreach ($dataByWeek as $week => $dataInWeek) {
            $dataInWeek = $dataInWeek['BigDataDbStationProcessCodsV5SuccessByWeek'];
            $week++;

            $dataInWeek['to_at'] = date('Y-m-d', strtotime('-1 days', strtotime($dataInWeek['to_at'])));
            $allDateOfWeek       = $this->Calendar->getAllDate($dataInWeek['from_at'], $dataInWeek['to_at']);
            $dataSalaryDeliver   = [];

            $dataForCal          = $this->BigDataDbStationProcessCod->getDataForCalSalary(
                $salaryMonth['start_date'], $allDateOfWeek, $this->COD_ORDER
            );

            foreach ($dataForCal as $dataArea) {
                $area = $dataArea['BigDataDbStationProcessCod']['area'] ?? 1;
                if (!isset($dataSalaryDeliver[$area])) $dataSalaryDeliver[$area] = $dataArea[0]['d_success'];
                else $dataSalaryDeliver[$area] += $dataArea[0]['d_success'];
            }

            $kpiDeliver = $this->salaryDeliver($dataSalaryDeliver);

            if (empty($dataInWeek['total'])) continue;
            if (empty($dataReturn[$week])) continue;

            $dataReturn[$week]['from']      = $dataInWeek['from_at'];
            $dataReturn[$week]['to']        = $dataInWeek['to_at'];
            $dataReturn[$week]['total']     = $dataInWeek['total'];
            $dataReturn[$week]['success']   = $dataInWeek['success'];
            $dataReturn[$week]['percent']   = floor($dataInWeek['success'] / $dataInWeek['total'] * 100 * 100) / 100;

            $dataReturn[$week]['success_real']   = array_sum($dataSalaryDeliver);

            $reward = getValueMapToTable($dataReturn[$week]['percent'], ErpSalaryTable::$F1000_DTEAM_REWARD_BY_WEEK);
            $dataReturn[$week]['reward']    = floor(array_sum($dataSalaryDeliver) * $reward * 100) / 100;
            $dataReturn[$week]['salary']    = floor($reward * $kpiDeliver * 100) / 100;

            $dataReturn[$week]['kpi_deliver'] = $kpiDeliver;
            $dataReturn['total_salary'] += $dataReturn[$week]['salary'];
            $dataReturn['total_reward'] += $dataReturn[$week]['reward'];
        }
        $dataReturn['total_reward'] = floor($dataReturn['total_reward'] * 100) / 100;
        return $dataReturn;
    }

    //todo: Thù lao theo điểm
    public function salaryByScore ($dataSalaryScore, $session, $contractType, $listOfSession, $workTypeId, $overTimeSession, $requireWorkDate) {
        $maxSessionByType = self::$MAX_SESSION_BY_WORK_TYPE[$workTypeId] ?? 2;
        //Tổng điểm tháng = Tổng đơn giao thành công + Điểm lấy & trả thành công
        $salaryCodProvinceByScore = 0;
        $totalScoreOfMonth = array_sum($dataSalaryScore);
        $listSalaryByScore = getValueMapToTable($totalScoreOfMonth, ErpSalaryTable::$F1000_SALARY_BY_SCORE);
        foreach ($dataSalaryScore as $area => $totalScoreByRegion) {
            // Mức lương áp dụng cho miền
            $salaryApplied = getValueMapToTable($area, $listSalaryByScore);
            $salary = $salaryApplied * $totalScoreByRegion;
            $salaryCodProvinceByScore += $salary;
        }
        $totalSalary = 0;
        if (empty($salaryCodProvinceByScore) || empty($totalScoreOfMonth) || empty($session['on_session'])) return 0;
        if (in_array($contractType, ErpEmpContract::$danh_sach_hop_dong_lao_dong)) {
            if (strtotime(min(array_keys($listOfSession))) >= strtotime('2020-05-21')) {
                $totalSalary = $salaryCodProvinceByScore * $session['on_session'] / ($totalScoreOfMonth * ($session['standard'] + $overTimeSession));
            } else {
                $totalSalary = $salaryCodProvinceByScore * $session['on_session'] / ($totalScoreOfMonth * $session['standard']);
            }
        }
        if ($contractType == 'service_contract' || empty($contractType)) {
            $holidaySession = 0;
            foreach ($this->LIST_HOLIDAY_IN_MONTH as $dateHoliday) {
                $holidaySession += $this->LIST_SESSION[$dateHoliday] ?? 0;
            }
            $totalSalary = $salaryCodProvinceByScore * (1 - $session['off_session'] * 0.025) * ($requireWorkDate * $maxSessionByType + $overTimeSession) / (($session['standard'] + $overTimeSession - $holidaySession) * $totalScoreOfMonth);
        }
        if ($totalSalary < 0) $totalSalary = 0;
        return floor($totalSalary * 100) / 100;
    }

    //todo:Thù lao giao theo vùng lấy theo d_success của mỗi vùng. Thù lao năng suất giao= (Số đơn V1*Thù lao V1 + Số đơn V2*Thù lao V2 +... + Số đơn V8*Thù lao V8)
    public function salaryDeliver ($dataSalaryDeliver) {
        $salaryCodProvinceByDeliver = 0;
        foreach ($dataSalaryDeliver as $area => $numDeliverSuccess) {
            $salaryApplied = getValueMapToTable($area, ErpSalaryTable::$F1000_SALARY_BY_DELIVER);
            $salaryCodProvinceByDeliver += $salaryApplied * $numDeliverSuccess;
        }
        if (empty($salaryCodProvinceByDeliver)) return 0;
        return floor($salaryCodProvinceByDeliver * 100) / 100;
    }

    ///todo: Thù lao lấy =  (p_success - p_pkg_bonus - p_truck) cả tháng của mỗi vùng * Thù Lao Vùng tương ứng/ Tổng (p_success - p_pkg_bonus - p_truck) các vùng +(p_pkg_bonus+p_truck)*500
    public function salaryPick ($dataSalaryPick) {
        $salaryCodProvinceByPick = 0;
        foreach ($dataSalaryPick as $area => $numPickReturnSuccess) {
            $salaryApplied = getValueMapToTable($area, ErpSalaryTable::$F1000_SALARY_BY_PICK_RETURN);
            $salaryCodProvinceByPick += $salaryApplied * $numPickReturnSuccess;
        }
        if (empty($salaryCodProvinceByPick)) return 0;
        return floor($salaryCodProvinceByPick * 100) / 100;
    }

    //todo: Thù lao trả=  (r_success - r_pkg_bonus) cả tháng của mỗi vùng * Thù Lao Vùng tương ứng/ Tổng (r_success - r_pkg_bonus ) các vùng +(r_pkg_bonus)*500
    public function salaryReturn ($dataSalaryReturn) {
        $salaryCodProvinceByReturn = 0;
        foreach ($dataSalaryReturn as $area => $numPickReturnSuccess) {
            $salaryApplied = getValueMapToTable($area, ErpSalaryTable::$F1000_SALARY_BY_PICK_RETURN);
            $salaryCodProvinceByReturn += $salaryApplied * $numPickReturnSuccess;
        }
        if (empty($salaryCodProvinceByReturn)) return 0;
        return floor($salaryCodProvinceByReturn * 100) / 100;
    }

    private $LIST_DATE_BP_SPEED_WS_1_T8_N2020 = [
        '2020-07-21', '2020-08-04', '2020-08-07', '2020-08-10', '2020-08-18'
    ];

    private $LIST_DATE_BP_SPEED_WS_2_T8_N2020 = [
        '2020-07-21', '2020-08-04', '2020-08-11', '2020-08-12', '2020-08-13', '2020-08-17'
    ];
    //todo: 2.2 Thưởng tốc độ giao hàng trung bình ca
    public function salaryBonusSpeed ($dataInWorkShift, $infoCod) {
        // Thưởng tốc độ giao hàng trung bình ca
        $max    = 0;
        $min    = 0;
        $dTotal = 0; // Tính tăng giảm vùng dùng tổng các d_total các vùng của một ca
        $dSpeed = 0;
        $sumDeliver = 0;
        $workShift  = 0;
        $dataReturn = [
            'average_speed_deliver' => 0,
            'reward_deliver_speed'  => 0,
            'total_salary'          => 0,
            'area_status'           => 0
        ];
        $totalDeliverSuccess = 0; // Tổng đơn giao

        // Từ ngày 21/10 bắt đầu áp dụng cơ chế Dteam bỏ cơ chế tính tốc độ
        if (strtotime($this->DATE) >= strtotime($this->dateStartDTeam)) return $dataReturn;

        $isCheat  = 2;
        $numSpeed = 0;
        foreach ($dataInWorkShift as $area => $detail) {
            if (empty($workShift)) $workShift = $detail['workshift'] ?? 0;
            $maxOderByArea = getValueMapToTable($area, ErpSalaryTable::$F1000_MAX_ORDER_BY_AREA);

            $max += $detail['d_total'] * $maxOderByArea['max']; // Mốc max = (A*50+B*40+C*30)/(A+B+C)
            $min += $detail['d_total'] * $maxOderByArea['min']; // Mốc min = (A*40+B*30+C*20)/(A+B+C)

            $sumDeliver += $detail['d_total'];
            $dTotal     += $detail['d_total'];
            $totalDeliverSuccess += $detail['d_success'];
            $numSpeed   += ($detail['d_speed'] > -1) ? 1 : 0;
            $dSpeed     += ($detail['d_speed'] > -1) ? $detail['d_speed'] * $detail['d_total'] : 0;

            if ($isCheat == 2) $isCheat = $this->validateCheat($detail['cod_order'], $detail['data_date_key'], $detail['cur_date']);
        }
        if (empty($sumDeliver)) return $dataReturn;
        if ($isCheat) {
            $max *= 2;
            $min *= 2;
        }
        $max = $max / $sumDeliver + 10;
        $min = $min / $sumDeliver - 10;
        if ($dTotal) $dataReturn['average_speed_deliver'] = $dSpeed / $dTotal;
        if ($dTotal >= $max) {
            $dataReturn['area_status'] = 1 + floor(($dTotal - $max) / 10);
        } elseif ($dTotal <= $min) {
            $dataReturn['area_status'] = -1 + -floor(($min - $dTotal) / 10);
        }
        foreach ($dataInWorkShift as $area => $detail) {
            if (empty($totalDeliverSuccess)) continue;
            if ($area < 1) $area = 1;
            $listRewardSpeedByArea = getValueMapToTable($area, ErpSalaryTable::$F1000_SALARY_REWARD_SPEED_PER_WORK_SHIFT);
            // mảng tạm lưu mức thưởng theo position
            $tmpListRewardSpeedByArea = array_values($listRewardSpeedByArea);
            // Mức thưởng hiện tại chưa có tăng hay giảm
            $reward = getValueMapToTable($detail['d_speed'], $listRewardSpeedByArea, 'more');
            // Tìm ra vị trí mức thưởng hiện tại
            foreach ($tmpListRewardSpeedByArea as $positionReward => $tmpReward) {
                if ($reward == $tmpReward) {
                    $currentPositionReward = $positionReward;
                    break;
                }
            }
            if (
                isset($currentPositionReward) &&
                isset($tmpListRewardSpeedByArea[$currentPositionReward - $dataReturn['area_status']])
            ) {
                // Nếu tồn tại vị trí mức thưởng và sau khi đã tăng hoặc giảm mốc thưởng giá trị đó tồn tại trong mảng thưởng tạm thì sẽ lấy giá trị đó
                $reward = $tmpListRewardSpeedByArea[$currentPositionReward - $dataReturn['area_status']];
            } else {
                // Ngược lại được tăng mức thưởng lên cao nhất nếu vùng tăng hoặc sẽ là mốc thưởng thấp nhất nếu vùng giảm
                $reward = $dataReturn['area_status'] > 0 ? current($tmpListRewardSpeedByArea) : end($tmpListRewardSpeedByArea);
            }
            if ($reward < 0) {

                // Hệ thống bỏ phạt
                if (isset($this->CONFIG_BP['speed'])) {
                    if (empty($this->CONFIG_BP['speed'])) $reward = 0;
                    if (!empty($this->CONFIG_BP['speed']) && $this->CONFIG_BP['speed'] == $workShift) $reward = 0;
                }
                // Bỏ phạt theo điều kiện bigdata
                if (!empty($detail['d_bo_phat'])) $reward = 0;

                // Bỏ phạt theo những ngày hệ thống gặp sự cố
                if (in_array($this->DATE, $this->LIST_DATE_EXCLUDE_T6_2020_V1)) $reward = 0;

                // Bỏ phạt 1 tháng đối với cod mới
                if (strtotime($this->DATE_START_PUNISH) > strtotime($this->DATE)) $reward = 0;

                // Bỏ phạt đối với các tỉnh và trong kỳ lương tháng 6 năm 2020
                if (in_array($infoCod['province_id'], $this->LIST_PROVINCE_EXCLUDE_V1) && strtotime($this->DATE) < strtotime('2020-06-21')) $reward = 0;

                // Bo phat thang 8
                if (in_array($this->DATE, $this->LIST_DATE_BP_SPEED_WS_1_T8_N2020) && $detail['workshift'] == 1) $reward = 0;
                if (in_array($this->DATE, $this->LIST_DATE_BP_SPEED_WS_2_T8_N2020) && $detail['workshift'] == 2) $reward = 0;

                if (
                    in_array($infoCod['province_id'], $this->LIST_PROVINCE_EXCLUDE_V1) &&
                    strtotime($this->DATE) < strtotime('2020-08-21') &&
                    strtotime($this->DATE) > strtotime('2020-07-20') &&
                    !in_array($infoCod['alias'], $this->LIST_COD_NOT_BP_T8_N2020)
                ) $reward = 0;
            }
            $dataReturn['reward_deliver_speed'] += $reward * $detail['d_success'];
            $salaryApplied = getValueMapToTable($area, ErpSalaryTable::$F1000_SALARY_BY_DELIVER);
            $dataReturn['total_salary'] += $reward * $detail['d_success'] * $salaryApplied;
        }
        // tháng 5 năm 2020 bỏ phạt tốc độ
        if (
            ($dataReturn['total_salary'] < 0 || $dataReturn['reward_deliver_speed'] < 0) &&
            strtotime($this->DATE) < strtotime('2020-05-21')
        ) {
            $dataReturn['total_salary'] = 0;
            $dataReturn['reward_deliver_speed'] = 0;
        }
        return $dataReturn;
    }

    //todo: 2.3 Thưởng tích lũy cân nặng giao
    public function salaryBonusWeightDeliver ($dataSalaryBonusWeightDeliver) {
        $totalWeightOfMonth = array_sum($dataSalaryBonusWeightDeliver);
        $salaryRewardWeightDeliver = 0;
        foreach ($dataSalaryBonusWeightDeliver as $area => $overWeight) {
            $listSalaryForOverWeight = getValueMapToTable($totalWeightOfMonth, ErpSalaryTable::$F1000_REWARD_WEIGHT_DELIVER_BY_AREA);
            if (empty($listSalaryForOverWeight)) continue;
            $salaryApplied = getValueMapToTable($area, $listSalaryForOverWeight);
            $salaryRewardWeightDeliver += $salaryApplied * $overWeight;
        }
        if (empty($salaryRewardWeightDeliver) || empty($totalWeightOfMonth)) return 0;
        return floor($salaryRewardWeightDeliver / $totalWeightOfMonth * 100) / 100;
    }

    //todo: Thưởng tích lũy cân nặng lấy trả
    public function salaryBonusWeightPickReturn($dataSalaryBonusWeightPickReturn) {
        $totalWeightOfMonth = array_sum($dataSalaryBonusWeightPickReturn);
        $salaryRewardPickReturnWeight = 0;
        foreach ($dataSalaryBonusWeightPickReturn as $area => $overWeight) {
            $listSalaryForOverWeight = getValueMapToTable($totalWeightOfMonth, ErpSalaryTable::$F1000_REWARD_WEIGHT_PICK_RETURN_BY_AREA);
            if (empty($listSalaryForOverWeight)) continue;
            $salaryApplied = getValueMapToTable($area, $listSalaryForOverWeight);
            $salaryRewardPickReturnWeight += $salaryApplied * $overWeight;
        }
        if (empty($salaryRewardPickReturnWeight) || empty($totalWeightOfMonth)) return 0;
        return floor($salaryRewardPickReturnWeight / $totalWeightOfMonth * 100) / 100;
    }

    private $LIST_COD_BO_PHAT_HIEU_SUAT_GIAO_T5 = [
        'T358925', 'T335627', 'T154355', 'T101332', 'T208842', 'T337835', 'T351389', 'T176088', 'T346922', 'T346886', 'T123499',
        'T347432', 'T347423', 'T155390', 'T158786', 'T129795', 'T364973', 'T250771', 'T297053', 'T89368', 'T312050', 'T244640',
        'T368477', 'T255309', 'T236672', 'T329558', 'T369389', 'T181092', 'T7449', 'T276442', 'T129971', 'T332723', 'T336056',
        'T179440', 'T192124', 'T321734', 'T263190', 'T5590', 'T28412', 'T269967', 'T39082', 'T233104', 'T323129', 'T337979',
        'T337973', 'T368450', 'T6638', 'T271950', 'T240852', 'T92690', 'T145522', 'T150539', 'T5080', 'T79297', 'T316307',
        'T10552', 'T366425', 'T368492', 'T363521', 'T355607', 'T300017', 'T315002', 'T123963', 'T356759', 'T350519', 'T346550',
        'T95402', 'T356345', 'T346544', 'T343718', 'T88945', 'T335954', 'T31962', 'T53361', 'T365450', 'T335777'
    ];

    private $LIST_COD_BO_PHAT_PHIEN_T7_N2020 = [
        'T157274', 'T364271', 'T28958', 'T74692', 'T133224', 'T359987', 'T14632', 'T184416', 'T141824', 'T149735', 'T109428'
    ];

    private $LIST_COD_BO_PHAT_NANG_SUAT_T7_N2020 = [
        'T60134', 'T374048', 'T375302', 'T320153', 'T240932', 'T33722', 'T46558', 'T327527', 'T404251', 'T149735', 'T352880',
        'T386350', 'T376556', 'T273810', 'T9222', 'T299222', 'T234164', 'T295343', 'T109428'
    ];

    private $LIST_KHO_BP_HS_T8_N2020_V1 = [
        'dien_ban'  => 120, 'dai_loc'   => 677, 'hoi_an'    => 965, 'duy_xuyen' => 6189
    ];

    private $LIST_KHO_BP_HS_T8_N2020_V2 = [
        'thang_binh' => 5304,
    ];

    private $LIST_COD_BP_NS_T8_N2020 = [
        'T404797', 'T403630', 'T327527', 'T404251', 'T103840', 'T141824', 'T149735', 'T352880', 'T377855', 'T342656',
        'T71072', 'T359852', 'T408502', 'T419194', 'T411118', 'T391045', 'T105932', 'T95430', 'T152211', 'T416590', 'T403816',
        'T295343', 'T295256', 'T406507', 'T149543', 'T145326', 'T179228', 'T335663', 'T231576', 'T135756', 'T374048', 'T405046',
        'T320153', 'T60134', 'T375302', 'T395482', 'T240932'
    ];

    public $LIST_COD_BP_NS_T8_N2020_V2 = ['T404086']; // Từ ngày 3/8/2020 - 18/8/2020
    public $LIST_COD_BP_NS_T8_N2020_V3 = ['T389269']; // Từ ngày 12/8-20/8
    public $LIST_COD_BP_NS_T8_N2020_V4 = ['T185124', 'T158990', 'T274188', 'T178516']; // Từ ngày 10/8-20/8

    //todo: Thù lao thưởng chất lượng giao
    public function salaryBonusDeliver ($dataInWorkShift, $infoCod, $salaryMonth, $dataOverWorkShift) {
        $totalWorkShift      = 0;
        foreach ($dataOverWorkShift as $dOverWorkShift) {
            if (!empty($dOverWorkShift['d_distribute']))
                $totalWorkShift++;
        }
        $totalWorkShift      = max(1, $totalWorkShift);
        // Bỏ max 2 phiên toàn bộ các tháng theo y/c vh ngày 2020-12-09
//        if ($totalWorkShift > 2) $totalWorkShift = 2;
        $this->ErpCheckinLog = getInstance('ErpCheckinLog');
        $stationCheckin = $this->ErpCheckinLog->getStationCheckinOnDate($infoCod['user_id'], $this->DATE);
        if (empty($stationCheckin)) $stationCheckin = $infoCod['station_id'];

        $this->BigDataConfigWorkShiftSachTuyen = getInstance('BigDataConfigWorkShiftSachTuyen');
        $configMaxWorkShift = $this->BigDataConfigWorkShiftSachTuyen->getConfigWorkShiftByStationId($stationCheckin);

        if (!empty($configMaxWorkShift)) $configMaxWorkShift = $configMaxWorkShift['sum_workshift'];
        else $configMaxWorkShift = 2;

        $totalWorkShift = min($totalWorkShift, $configMaxWorkShift);

        $totalDeliverSuccess = 0;
        $totalDeliver        = 0;
        $dSuccess            = 0;
        $salaryDeliver       = 0;
        $workShift           = 0;
        $salaryTable         = ErpSalaryTable::$F1000_REWARD_QUANTITY_DELIVER;
        $dataRoute           = [
            1 => ['success' => 0, 'total' => 0],
            2 => ['success' => 0, 'total' => 0],
            3 => ['success' => 0, 'total' => 0],
        ];

        foreach ($dataOverWorkShift as $dataSession) {
            if ($dataSession['workshift'] < 3) {
                $dataRoute[1]['success'] += $dataSession['success'];
                $dataRoute[1]['total'] += $dataSession['total'];
                continue;
            }

            if ($dataSession['workshift'] < 5) {
                $dataRoute[2]['success'] += $dataSession['success'];
                $dataRoute[2]['total'] += $dataSession['total'];
                continue;
            }

            $dataRoute[3]['success'] += $dataSession['success'];
            $dataRoute[3]['total'] += $dataSession['total'];
        }

        $dataReturn = [
            'reward_deliver_quantity'   => 0, // Tổng đơn thưởng gốc
            'deliver_quantity_percent'  => "",  // Phần trăm thưởng
            'total_deliver_salary'      => 0, // Tổng tiền thưởng tiền gốc
            'total_workshift_work'      => $totalWorkShift, // Tổng ca phát sinh trong ngày
            'route_clean_percent'       => "", // Phần trăm sạch phiên
            'fine_clean_package'        => 0, // Số đơn phạt sạch phiên
            'clear_level_area'          => 100,
            'total_deliver_pkg_clean'   => 0,
            'total_deliver_clean_success' => 0,
            'fine_clean_salary'         => 0, // Số tiền phạt sạch phiên
            'total_salary'              => 0 // tiền chốt
        ];

        foreach ($dataInWorkShift as $area => $detail) {
            $workShift           = $detail['workshift'] ?? 0;
            $totalDeliverSuccess += $detail['d_success'];
            $totalDeliver        += $detail['d_total'];
            $dSuccess            += $detail['d_success'];
            $salaryApplied       = getValueMapToTable($area, ErpSalaryTable::$F1000_SALARY_BY_DELIVER);
            $salaryDeliver       += $detail['d_success'] * $salaryApplied;

            if (!empty($detail['clear_level_area'])) $dataReturn['clear_level_area'] = $detail['clear_level_area'];
        }

        if (empty($totalDeliver)) return $dataReturn;

        // Logic tính mới từ 2020-10-21 của dteam
        if (strtotime($this->DATE) >= strtotime($this->dateStartDTeam)) {
            $tmpTotalWorkShift = $totalWorkShift;
            if ($infoCod['work_type_id'] != 1) $tmpTotalWorkShift++;
            $salaryTable = getValueMapToTable($tmpTotalWorkShift, ErpSalaryTable::$F1000_REWARD_QUANTITY_DELIVER_DTEAM);
        }

        $dataReturn['deliver_quantity_percent'] = $totalDeliverSuccess / $totalDeliver * 100;
        $reward = getValueMapToTable($dataReturn['deliver_quantity_percent'], $salaryTable);
        $dataReturn['deliver_quantity_percent'] = (string)floor($dataReturn['deliver_quantity_percent'] * 100) / 100 . '%';

        if ($reward < 0) {
            // Hệ thống bỏ phạt
            if (isset($this->CONFIG_BP['quality_deliver'])) {
                if (empty($this->CONFIG_BP['quality_deliver'])) $reward = 0;
                if (!empty($this->CONFIG_BP['quality_deliver']) && $this->CONFIG_BP['quality_deliver'] == $workShift) $reward = 0;
            }
            // Bỏ phạt 1 tháng đối với cod mới
            if (strtotime($this->DATE_START_PUNISH) > strtotime($this->DATE) && strtotime($this->DATE) < strtotime('2020-07-21')) $reward = 0;

            // Bỏ phạt đối với các cod trong kỳ lương tháng 6 và trước đó năm 2020
            if (in_array($infoCod['alias'], $this->LIST_COD_BO_PHAT_HIEU_SUAT_T6_2020) && in_array($salaryMonth['month'], [5, 6]) && $salaryMonth['year'] == 2020) $reward = 0;

            // Bỏ phạt đối với các tỉnh trong kỳ lương tháng 7 và trước đó năm 2020
            if (in_array($infoCod['province_id'], $this->LIST_PROVINCE_EXCLUDE_V1) && in_array($salaryMonth['month'], [5, 6, 7]) && $salaryMonth['year'] == 2020) $reward = 0;

            // Bỏ phạt đối với các cod trong kỳ lương tháng 7 xin qua mail được chị Minh duyệt
            if (in_array($infoCod['alias'], $this->LIST_COD_BO_PHAT_NANG_SUAT_T7_N2020) && $salaryMonth['month'] == 7 && $salaryMonth['year'] == 2020) $reward = 0;

            // Từ ngày 8/8/2020-20/8/2020
            if (in_array($infoCod['station_id'], $this->LIST_KHO_BP_HS_T8_N2020_V1) && strtotime($this->DATE) > strtotime('2020-08-07') && strtotime($this->DATE) < strtotime('2020-08-21')) $reward = 0;

            // Từ ngày 3/8/2020 - 18/8/2020
            if (in_array($infoCod['station_id'], $this->LIST_KHO_BP_HS_T8_N2020_V2) && strtotime($this->DATE) > strtotime('2020-08-02') && strtotime($this->DATE) < strtotime('2020-08-19')) $reward = 0;

            if (in_array($infoCod['alias'], $this->LIST_COD_BP_NS_T8_N2020) && $salaryMonth['month'] == 8 && $salaryMonth['year'] == 2020) $reward = 0;

            // Từ ngày 3/8/2020 - 18/8/2020
            if (in_array($infoCod['alias'], $this->LIST_COD_BP_NS_T8_N2020_V2) && strtotime($this->DATE) > strtotime('2020-08-02') && strtotime($this->DATE) < strtotime('2020-08-19')) $reward = 0;

            // Từ ngày 12/8-20/8
            if (in_array($infoCod['alias'], $this->LIST_COD_BP_NS_T8_N2020_V3) && strtotime($this->DATE) > strtotime('2020-08-11') && strtotime($this->DATE) < strtotime('2020-08-21')) $reward = 0;

            // Từ ngày 10/8-20/8
            if (in_array($infoCod['alias'], $this->LIST_COD_BP_NS_T8_N2020_V4) && strtotime($this->DATE) > strtotime('2020-08-09') && strtotime($this->DATE) < strtotime('2020-08-21')) $reward = 0;

            // Bo phat Tien Giang 2020-07-26 -> 2020-09-26
            if ($infoCod['province_id'] == 824 && strtotime($this->DATE) > strtotime('2020-07-25') && strtotime($this->DATE) < strtotime('2020-09-27') && $workShift == 3) $reward = 0;

            if (
                in_array($infoCod['province_id'], $this->LIST_PROVINCE_EXCLUDE_V1) &&
                in_array($salaryMonth['month'], [8]) &&
                $salaryMonth['year'] == 2020 &&
                !in_array($infoCod['alias'], $this->LIST_COD_NOT_BP_T8_N2020)
            ) $reward = 0;

        }

        $dataReturn['reward_deliver_quantity'] = $reward * $dSuccess;
        $dataReturn['total_salary'] = $reward * $salaryDeliver;

        if (strtotime($this->DATE) >= strtotime($this->dateStartDTeam) && !empty($dataRoute[$workShift]['total'])) {
            $dataReturn['route_clean_percent']  = $dataRoute[$workShift]['success'] / $dataRoute[$workShift]['total'] * 100;
            $dataReturn['route_clean_percent']  = floor($dataReturn['route_clean_percent'] * 100) / 100;
            $dataReturn['total_deliver_salary'] = $dataReturn['total_salary'];
            $dataReturn['total_deliver_pkg_clean'] += $dataRoute[$workShift]['total'];
            $dataReturn['total_deliver_clean_success'] += $dataRoute[$workShift]['success'];

            if (
                $dataReturn['route_clean_percent'] < $dataReturn['clear_level_area'] &&
                strtotime($this->DATE) >= strtotime($this->dateStartFine) &&
                strtotime($this->DATE) >= strtotime($this->DATE_START_PUNISH)
            ) {
                $indicatorNotClean = ErpSalaryTable::$F1000_DTEAM_ROUTE_NOT_CLEAN;
                if ($this->DATE >= '2020-11-21' && $reward < 0) $indicatorNotClean = 0;
                if (isset($this->CONFIG_BP['clean'])) {
                    if (empty($this->CONFIG_BP['clean'])) $indicatorNotClean = 0;
                    if (!empty($this->CONFIG_BP['clean']) && $this->CONFIG_BP['clean'] == $workShift) $indicatorNotClean = 0;
                }
                $dataReturn['fine_clean_package'] = $indicatorNotClean * $dSuccess;
                $dataReturn['fine_clean_salary'] = $indicatorNotClean * $salaryDeliver;
                $dataReturn['total_salary'] += $dataReturn['fine_clean_salary'];
                $dataReturn['reward_deliver_quantity'] += $dataReturn['fine_clean_package'];
            }
        }
        // tháng 5 năm 2020 bỏ phạt chất lượng giao đối với các cod thuộc tỉnh 'Bình Dương', 'Đồng Nai', 'Long An'
        if (
            ($dataReturn['total_salary'] < 0 || $dataReturn['reward_deliver_quantity'] < 0) &&
            (strtotime($this->DATE) < strtotime('2020-05-21')) &&
            (in_array($infoCod['province_id'], $this->LIST_PROVINCE_EXCLUDE_V1) || in_array($infoCod['alias'], $this->LIST_COD_BO_PHAT_HIEU_SUAT_GIAO_T5))
        ) {
            $dataReturn['total_salary'] = 0;
            $dataReturn['reward_deliver_quantity'] = 0;
        }
        return $dataReturn;
    }

    //todo: 3.2 Thù lao thưởng chất lượng lấy
    public function salaryBonusPick ($dataInWorkShift, $infoCod, $salaryMonth) {
        $totalTpSuccess = 0;
        $totalTpTotal   = 0;
        $totalSpeed     = 0;
        $totalPTrue     = 0;
        $totalBonus     = 0;
        $totalPick      = 0; // Tính thù lao lấy
        $salaryPick     = 0; // Thù lao lấy
        $numSpeed       = 0;
        $workShift      = 0;
        $dataReturn     = [
            'reward_pick_quantity'  => 0,
            'pick_quantity_percent' => "",
            'average_pick_speed'    => 0,
            'total_salary'          => 0
        ];
        foreach ($dataInWorkShift as $area => $detail) {
            if (empty($workShift)) $workShift = $detail['workshift'] ?? 0;

            $numSpeed       += ($detail['pick_speed'] > -1) ? 1 : 0;
            $totalTpSuccess += $detail['tp_success'];
            $totalTpTotal   += $detail['tp_total'];
            $totalSpeed     += ($detail['pick_speed'] > -1) ? $detail['pick_speed'] : 0;
            $totalPTrue     += $detail['p_true'];
            $totalPick      += $detail['p_success'] -  $detail['p_pkg_bonus'] - $detail['p_truck'];
            $totalBonus     += $detail['p_truck'] + $detail['p_pkg_bonus'];
            $salaryApplied  = getValueMapToTable($area, ErpSalaryTable::$F1000_SALARY_BY_PICK_RETURN);
            $salaryPick     += $salaryApplied * ($detail['p_success'] -  $detail['p_pkg_bonus'] - $detail['p_truck']) + ($detail['p_truck'] + $detail['p_pkg_bonus']) * ErpSalaryTable::$F1000_SALARY_OVER_PICK_RETURN_DELIVER;
        }

        if (empty($totalTpTotal) || empty($numSpeed)) return $dataReturn;

        $percent             = $totalTpSuccess / $totalTpTotal * 100;
        $averageSpeed        = $totalSpeed / $numSpeed;
        $listRewardByPercent = getValueMapToTable($percent, ErpSalaryTable::$F1000_REWARD_QUANTITY_PICK);

        if (empty($listRewardByPercent)) $reward = -0.1;
        else $reward = getValueMapToTable($averageSpeed, $listRewardByPercent);

        if ($reward < 0) {
            // Hệ thống bỏ phạt
            if (isset($this->CONFIG_BP['quality_pick'])) {
                if (empty($this->CONFIG_BP['quality_pick'])) $reward = 0;
                if (!empty($this->CONFIG_BP['quality_pick']) && $this->CONFIG_BP['quality_pick'] == $workShift) $reward = 0;
            }
            // Bỏ phạt 1 tháng đối với cod mới
            if (strtotime($this->DATE_START_PUNISH) > strtotime($this->DATE) && strtotime($this->DATE) < strtotime('2020-07-21')) $reward = 0;

            // Bỏ phạt đối với các cod trong kỳ lương tháng 6 và trước đó năm 2020
            if (in_array($infoCod['alias'], $this->LIST_COD_BO_PHAT_HIEU_SUAT_T6_2020) && in_array($salaryMonth['month'], [5, 6]) && $salaryMonth['year'] == 2020) $reward = 0;

            // Bỏ phạt đối với các tỉnh trong kỳ lương tháng 7 và trước đó năm 2020
            if (in_array($infoCod['province_id'], $this->LIST_PROVINCE_EXCLUDE_V1) && in_array($salaryMonth['month'], [5, 6, 7]) && $salaryMonth['year'] == 2020) $reward = 0;

            // Bỏ phạt đối với các cod trong kỳ lương tháng 7 xin qua mail được chị Minh duyệt
            if (in_array($infoCod['alias'], $this->LIST_COD_BO_PHAT_NANG_SUAT_T7_N2020) && $salaryMonth['month'] == 7 && $salaryMonth['year'] == 2020) $reward = 0;

            // Từ ngày 8/8/2020-20/8/2020
            if (in_array($infoCod['station_id'], $this->LIST_KHO_BP_HS_T8_N2020_V1) && strtotime($this->DATE) > strtotime('2020-08-07') && strtotime($this->DATE) < strtotime('2020-08-21')) $reward = 0;

            // Từ ngày 3/8/2020 - 18/8/2020
            if (in_array($infoCod['station_id'], $this->LIST_KHO_BP_HS_T8_N2020_V2) && strtotime($this->DATE) > strtotime('2020-08-02') && strtotime($this->DATE) < strtotime('2020-08-19')) $reward = 0;

            if (in_array($infoCod['alias'], $this->LIST_COD_BP_NS_T8_N2020) && $salaryMonth['month'] == 8 && $salaryMonth['year'] == 2020) $reward = 0;

            // Từ ngày 3/8/2020 - 18/8/2020
            if (in_array($infoCod['alias'], $this->LIST_COD_BP_NS_T8_N2020_V2) && strtotime($this->DATE) > strtotime('2020-08-02') && strtotime($this->DATE) < strtotime('2020-08-19')) $reward = 0;

            // Từ ngày 12/8-20/8
            if (in_array($infoCod['alias'], $this->LIST_COD_BP_NS_T8_N2020_V3) && strtotime($this->DATE) > strtotime('2020-08-11') && strtotime($this->DATE) < strtotime('2020-08-21')) $reward = 0;

            // Từ ngày 10/8-20/8
            if (in_array($infoCod['alias'], $this->LIST_COD_BP_NS_T8_N2020_V4) && strtotime($this->DATE) > strtotime('2020-08-09') && strtotime($this->DATE) < strtotime('2020-08-21')) $reward = 0;

            if (
                in_array($infoCod['province_id'], $this->LIST_PROVINCE_EXCLUDE_V1) &&
                in_array($salaryMonth['month'], [8]) &&
                $salaryMonth['year'] == 2020 &&
                !in_array($infoCod['alias'], $this->LIST_COD_NOT_BP_T8_N2020)
            ) $reward = 0;
        }

        if (empty($totalPick + $totalBonus)) return $dataReturn;
        $dataReturn['reward_pick_quantity']  += $reward * $totalPTrue;
        $dataReturn['pick_quantity_percent'] = (string)floor($percent * 100) / 100 . '%';
        $dataReturn['average_pick_speed']    = $averageSpeed;
        $dataReturn['total_salary']          = $reward * $totalPTrue * $salaryPick / ($totalBonus + $totalPick);
        // tháng 5 năm 2020 bỏ phạt chất lượng lấy
        if (
            ($dataReturn['total_salary'] < 0 || $dataReturn['reward_pick_quantity'] < 0) &&
            (strtotime($this->DATE) < strtotime('2020-05-21'))
        ) {
            $dataReturn['total_salary'] = 0;
            $dataReturn['reward_pick_quantity'] = 0;
        }
        return $dataReturn;
    }

    //todo: 3.3 Thù lao thưởng chất lượng trả
    public function salaryBonusReturn ($dataInWorkShift, $infoCod, $salaryMonth) {
        $totalRSuccess          = 0;
        $totalRTotal            = 0;
        $totalDistributeSuccess = 0;
        $totalDistributeTotal   = 0;
        $totalRTrue             = 0;
        $salaryReturn           = 0;
        $totalRBonus            = 0;
        $workShift              = 0;

        $dataReturn = [
            'reward_return_quantity'    => 0,
            'return_quantity_percent'   => "",
            'return_quality_percent'    => "",
            'total_salary'              => 0
        ];
        foreach ($dataInWorkShift as $area => $detail) {
            if (empty($workShift)) $workShift = $detail['workshift'] ?? 0;

            $totalRTrue             += $detail['r_true'];
            $totalDistributeSuccess += $detail['r_success_distribute'];
            $totalDistributeTotal   += $detail['r_total_distribute'];
            $totalRSuccess          += $detail['r_success'];
            $totalRTotal            += $detail['r_total'];
            $totalRBonus            += $detail['r_pkg_bonus'];
            $salaryApplied          = getValueMapToTable($area, ErpSalaryTable::$F1000_SALARY_BY_PICK_RETURN);
            $salaryReturn           += $salaryApplied * $detail['r_success'] + $detail['r_pkg_bonus'] * ErpSalaryTable::$F1000_SALARY_OVER_PICK_RETURN_DELIVER;
        }

        if (empty($totalDistributeTotal) || empty($totalRTotal)) return $dataReturn;

        $percentReturnSuccess = $totalRSuccess / $totalRTotal * 100; // Trả thành công
        $percentOverallReturn = $totalDistributeSuccess / $totalDistributeTotal * 100; // Toàn trình trả
        $listPercent          = getValueMapToTable($percentOverallReturn, ErpSalaryTable::$F1000_REWARD_QUANTITY_RETURN);

        if (empty($listPercent)) $reward = -0.1;
        else $reward = getValueMapToTable($percentReturnSuccess, $listPercent);

        if ($reward < 0) {
            // Hệ thống bỏ phạt
            if (isset($this->CONFIG_BP['quality_return'])) {
                if (empty($this->CONFIG_BP['quality_return'])) $reward = 0;
                if (!empty($this->CONFIG_BP['quality_return']) && $this->CONFIG_BP['quality_return'] == $workShift) $reward = 0;
            }
            // Bỏ phạt 1 tháng đối với cod mới
            if (strtotime($this->DATE_START_PUNISH) > strtotime($this->DATE) && strtotime($this->DATE) < strtotime('2020-07-21')) $reward = 0;

            // Bỏ phạt riêng đối với các cod trong các kỳ lương tháng 6 và trước đó năm 2020
            if (in_array($infoCod['alias'], $this->LIST_COD_BO_PHAT_HIEU_SUAT_T6_2020) && in_array($salaryMonth['month'], [5, 6]) && $salaryMonth['year'] == 2020) $reward = 0;

            // Bỏ phạt các tỉnh trong kỳ lương tháng 7 và trước đó năm 2020
            if (in_array($infoCod['province_id'], $this->LIST_PROVINCE_EXCLUDE_V1) && in_array($salaryMonth['month'], [5, 6]) && $salaryMonth['year'] == 2020) $reward = 0;

            // Bỏ phạt đối với các cod trong kỳ lương tháng 7 xin qua mail được chị Minh duyệt
            if (in_array($infoCod['alias'], $this->LIST_COD_BO_PHAT_NANG_SUAT_T7_N2020) && $salaryMonth['month'] == 7 && $salaryMonth['year'] == 2020) $reward = 0;

            // Từ ngày 8/8/2020-20/8/2020
            if (in_array($infoCod['station_id'], $this->LIST_KHO_BP_HS_T8_N2020_V1) && strtotime($this->DATE) > strtotime('2020-08-07') && strtotime($this->DATE) < strtotime('2020-08-21')) $reward = 0;

            // Từ ngày 3/8/2020 - 18/8/2020
            if (in_array($infoCod['station_id'], $this->LIST_KHO_BP_HS_T8_N2020_V2) && strtotime($this->DATE) > strtotime('2020-08-02') && strtotime($this->DATE) < strtotime('2020-08-19')) $reward = 0;

            if (in_array($infoCod['alias'], $this->LIST_COD_BP_NS_T8_N2020) && $salaryMonth['month'] == 8 && $salaryMonth['year'] == 2020) $reward = 0;

            // Từ ngày 3/8/2020 - 18/8/2020
            if (in_array($infoCod['alias'], $this->LIST_COD_BP_NS_T8_N2020_V2) && strtotime($this->DATE) > strtotime('2020-08-02') && strtotime($this->DATE) < strtotime('2020-08-19')) $reward = 0;

            // Từ ngày 12/8-20/8
            if (in_array($infoCod['alias'], $this->LIST_COD_BP_NS_T8_N2020_V3) && strtotime($this->DATE) > strtotime('2020-08-11') && strtotime($this->DATE) < strtotime('2020-08-21')) $reward = 0;

            // Từ ngày 10/8-20/8
            if (in_array($infoCod['alias'], $this->LIST_COD_BP_NS_T8_N2020_V4) && strtotime($this->DATE) > strtotime('2020-08-09') && strtotime($this->DATE) < strtotime('2020-08-21')) $reward = 0;

            if (
                in_array($infoCod['province_id'], $this->LIST_PROVINCE_EXCLUDE_V1) &&
                in_array($salaryMonth['month'], [8]) &&
                $salaryMonth['year'] == 2020 &&
                !in_array($infoCod['alias'], $this->LIST_COD_NOT_BP_T8_N2020)
            ) $reward = 0;
        }

        if (empty($totalRBonus + $totalRSuccess)) return $dataReturn;
        $dataReturn['reward_return_quantity']   += $totalRTrue * $reward;
        $dataReturn['return_quantity_percent']  = (string)floor($percentReturnSuccess * 100) / 100 . "%";
        $dataReturn['return_quality_percent']   = (string)floor($percentOverallReturn * 100) / 100 . "%";
        $dataReturn['total_salary']             = $totalRTrue * $reward * $salaryReturn / ($totalRSuccess + $totalRBonus);
        // kỳ lương tháng 5 năm 2020 bỏ phạt chất lương trả
        if (
            ($dataReturn['total_salary'] < 0 || $dataReturn['reward_return_quantity'] < 0) &&
            (strtotime($this->DATE) < strtotime('2020-05-21'))
        ) {
            $dataReturn['total_salary'] = 0;
            $dataReturn['reward_return_quantity'] = 0;
        }
        return $dataReturn;
    }

    //todo: Phụ cấp cod chạy linh động
    public function calSalaryAllowance ($infoCod, $dataForCalSalPoint, $session, $workTypeId, $overTimeSession, $salaryMonth) {
        if ($workTypeId != 1) return 0;
        if (!in_array($infoCod['position_job'], [66, 67, 68, 98])) return 0;
        $totalSalaryAllArea = 0;
        $totalOrder = array_sum($dataForCalSalPoint);
        foreach ($dataForCalSalPoint as $area => $num) {
            $salaryApplied = getValueMapToTable($area, ErpSalaryTable::$F1000_ALLOWANCE_SALARY);
            $totalSalaryAllArea += $salaryApplied * $num;
        }
        if (empty($totalSalaryAllArea) || empty($totalOrder) || empty($session['on_session'])) return 0;
        if (strtotime($salaryMonth['start_date']) > strtotime('2020-08-20')) {
            $salary = $totalSalaryAllArea * ($session['allowance_session'] ?? $session['on_session']) / ($totalOrder * ($session['standard'] + $overTimeSession));
        } else {
            $salary = $totalSalaryAllArea * $session['on_session'] / ($totalOrder * ($session['standard'] + $overTimeSession));
        }
        return floor($salary * 100) / 100;
    }

    //todo: Phụ cấp cod chạy tối
    public function salaryAllowanceOnNight ($dataInWorkShift) {
        $totalOrder = 0;
        foreach ($dataInWorkShift as $area => $detail) {
            $totalOrder += $detail['d_success'] + $detail['p_score'] + $detail['r_score'];
        }
        if ($totalOrder < 7) return 0;
        return getValueMapToTable($totalOrder, ErpSalaryTable::$F1000_ALLOWANCE_SALARY_ON_NIGHT);
    }

    //todo: Hàm tính hệ số tăng ca chủ nhật của 1 ca
    public function calSalaryOverTimeSunday ($dataInWorkShift) {
        $totalOrder = 0;
        $isAreaMoreThan4 = 0;
        forEach ($dataInWorkShift as $area => $detail) {
            // Có ít nhất một đơn vùng 5 trở lên mới được xét điều kiện x2 phiên tăng ca
            if ($area >= 5) $isAreaMoreThan4 = 1;
            $totalOrder += $detail['d_success'] + $detail['p_score'] + $detail['r_score'];
        }
        if ($totalOrder >= 20 && !empty($isAreaMoreThan4)) return 2; // Chị Hiền chốt ngày 2020-06-27
        if ($totalOrder >= 10) return 1;
        return 0;
    }
    /*-----------------------------------------------SHOW DATA--------------------------------------------------------*/
    //todo: Hàm phụ trợ api hiển thị data theo ngày
    public function showDataInDate ($date, $codId) {
        $this->DATE = $date;
        $infoCod = $this->ErpCod->getInfoCod($codId);
        $configFine  = $this->ErpConfigSalaryCod->getConfigFineForCal($date, $infoCod['region'], $infoCod['province_id'], $infoCod['station_id']);
        $this->extractConfigFineForDate($configFine, $infoCod, $date);
        $this->DATE_START_PUNISH = date('Y-m-d', strtotime('+30 days', strtotime($infoCod['active_date'])));
        $salaryMonth = $this->TimeSheets->getSalaryMonthByDate($date);
        $data = $this->BigDataDbStationProcessCod->getAllDataCodProvinceByListDate($salaryMonth['from'], $date, $infoCod['cod_order']);
        $dataResponse = $dataWorkShift = $dataCal = [];
        $this->BigDataDbStationProcessCodsV5SuccessByWorkShift = getInstance('BigDataDbStationProcessCodsV5SuccessByWorkShift');
        $dataOverAll  = $this->BigDataDbStationProcessCodsV5SuccessByWorkShift->getDataInDate($salaryMonth['from'], $date, $infoCod['cod_order']);

        foreach ($data as $detailData) {
            $area = $detailData['BigDataDbStationProcessCod']['area'];
            $workShift = 'ws_' . $detailData['BigDataDbStationProcessCod']['workshift'];
            if (empty($dataResponse[$workShift])) {
                $dataResponse[$workShift] = [
                    'deliver_packages' => $detailData['BigDataDbStationProcessCod']['d_total'],
                    'deliver_packages_success' => $detailData['BigDataDbStationProcessCod']['d_success'],
                    'pick_packages_success' => $detailData['BigDataDbStationProcessCod']['p_success'],
                    'return_packages_success' => $detailData['BigDataDbStationProcessCod']['r_success'],
                    'weight_deliver_accumulation' => $detailData['BigDataDbStationProcessCod']['d_weight'] > 0 ? $detailData['BigDataDbStationProcessCod']['d_weight'] : 0,
                    'weight_pick_accumulation' => $detailData['BigDataDbStationProcessCod']['p_weight'] > 0 ? $detailData['BigDataDbStationProcessCod']['p_weight'] : 0,
                    'return_accumulation_weight' => $detailData['BigDataDbStationProcessCod']['r_weight'] >0 ? $detailData['BigDataDbStationProcessCod']['r_weight'] : 0,
                    'return_place_success' => $detailData['BigDataDbStationProcessCod']['r_score'],
                    'pick_place_success' => $detailData['BigDataDbStationProcessCod']['p_score'],
                    'integration_index' => $detailData['BigDataDbStationProcessCod']['integration_index'] ?? 0
                ];
            } else {
                $dataResponse[$workShift]['deliver_packages'] += $detailData['BigDataDbStationProcessCod']['d_total'];
                $dataResponse[$workShift]['deliver_packages_success'] += $detailData['BigDataDbStationProcessCod']['d_success'];
                $dataResponse[$workShift]['pick_packages_success'] += $detailData['BigDataDbStationProcessCod']['p_success'];
                $dataResponse[$workShift]['return_packages_success'] += $detailData['BigDataDbStationProcessCod']['r_success'];
                $dataResponse[$workShift]['weight_deliver_accumulation'] += $detailData['BigDataDbStationProcessCod']['d_weight'] > 0 ? $detailData['BigDataDbStationProcessCod']['d_weight'] : 0;
                $dataResponse[$workShift]['weight_pick_accumulation'] += $detailData['BigDataDbStationProcessCod']['p_weight'] > 0 ? $detailData['BigDataDbStationProcessCod']['p_weight'] : 0;
                $dataResponse[$workShift]['return_accumulation_weight'] += $detailData['BigDataDbStationProcessCod']['r_weight'] > 0 ? $detailData['BigDataDbStationProcessCod']['r_weight'] : 0;
                $dataResponse[$workShift]['return_place_success'] += $detailData['BigDataDbStationProcessCod']['r_score'];
                $dataResponse[$workShift]['pick_place_success'] += $detailData['BigDataDbStationProcessCod']['p_score'];
                $dataResponse[$workShift]['integration_index'] += $detailData['BigDataDbStationProcessCod']['integration_index'] ?? 0;
            }
            // Gom data theo ca, vùng
            $dataWorkShift[$workShift][$area] = $detailData['BigDataDbStationProcessCod'];
        }
        foreach ($dataWorkShift as $workShift => $dataInWorkShift) {
            $dataRewardSpeed = $this->salaryBonusSpeed($dataInWorkShift, $infoCod); // Thưởng tốc độ giao hàng trung bình trên ca
            $dataRewardDeliver = $this->salaryBonusDeliver($dataInWorkShift, $infoCod, $salaryMonth, $dataOverAll);
            $dataRewardPick = $this->salaryBonusPick($dataInWorkShift, $infoCod, $salaryMonth);
            $dataRewardReturn = $this->salaryBonusReturn($dataInWorkShift, $infoCod, $salaryMonth);
            $dataResponse[$workShift]['kpi_reward_cod_province'] = [
                'deliver_average_speed' => floor($dataRewardSpeed['average_speed_deliver'] * 100) / 100,
                'deliver_reward_speed' => floor($dataRewardSpeed['reward_deliver_speed'] * 100) / 100,
                'deliver_reward_quantity' => floor($dataRewardDeliver['reward_deliver_quantity'] * 100) / 100,
                'deliver_quantity_percent' => $dataRewardDeliver['deliver_quantity_percent'],
                'total_deliver_salary'      => floor($dataRewardDeliver['total_salary'] * 100) / 100,
                'total_workshift_work'      => floor($dataRewardDeliver['total_workshift_work'] * 100) / 100,
                'clear_level_area'          => $dataRewardDeliver['clear_level_area'] ?? 0,
                'route_clean_percent'       => $dataRewardDeliver['route_clean_percent'] ?? '',
                'total_deliver_pkg_clean'   => $dataRewardDeliver['total_deliver_pkg_clean'] ?? 0,
                'total_deliver_clean_success' => $dataRewardDeliver['total_deliver_clean_success'] ?? 0,
                'fine_clean_package'        => floor($dataRewardDeliver['fine_clean_package'] * 100) / 100,
                'fine_clean_salary'         => floor($dataRewardDeliver['fine_clean_salary'] * 100) / 100,
                'pick_reward_quantity' => floor($dataRewardPick['reward_pick_quantity'] * 100) / 100,
                'pick_quantity_percent' => $dataRewardPick['pick_quantity_percent'],
                'pick_average_speed' => floor($dataRewardPick['average_pick_speed'] * 100) / 100,
                'return_reward_quantity' => floor($dataRewardReturn['reward_return_quantity'] * 100) / 100,
                'return_quantity_percent' => $dataRewardReturn['return_quantity_percent'],
                'return_quality_percent' => $dataRewardReturn['return_quality_percent']
            ];
            $dataResponse[$workShift]['kpi_reward_cod_province']['reward_deliver_quantity_no_clean'] =
                ($dataResponse[$workShift]['kpi_reward_cod_province']['deliver_reward_quantity'] ?? 0) -
                ($dataResponse[$workShift]['kpi_reward_cod_province']['fine_clean_package'] ?? 0);
            $dataResponse[$workShift]['area_status'] = $dataRewardSpeed['area_status'];
        }
        foreach ($dataResponse as $workShift => $dataWorkShift) {
            $dataWorkShift['kpi_reward_cod_province'] = array_filter($dataWorkShift['kpi_reward_cod_province']);
            $dataWorkShift = array_filter($dataWorkShift);
            if (empty($dataWorkShift)) unset($dataResponse[$workShift]);
        }
        $this->ErpCodPenalty = getInstance('ErpCodPenalty');
        $penalty = $this->ErpCodPenalty->getDataCodPenalty($infoCod['user_id'], 1000, $salaryMonth['from'], $this->DATE);
        if (!empty($dataResponse)) $dataResponse['cod_penalty'] = $penalty;
        return $dataResponse;
    }

    private $listUserSeeAll = ['binhvt', 'toanlv34', 'linhnt75'];
    private $convertRegionToAlias = [10 => 'MB', '20' => 'MT', 30 => 'MN', 1 => 'NULL'];
    //todo hàm validate HR cod được xem lương của cod hay không
    public function validatePermission ($infoCod) {
        return true;
        $userIdChecking = AuthComponent::user('id');
        $userCheckinInfo = $this->ErpUser->getInfoUser($userIdChecking);
        if (in_array($userCheckinInfo['username'], $this->listUserSeeAll)) return true;
        if ($userCheckinInfo['group_id'] != 3) return true;
        $permission = $this->Permissions->getAreaManagerOfUser($userIdChecking);
        if (is_array($permission)) {
            if (in_array($infoCod['region'], $permission['region']) || in_array($infoCod['province_id'], $permission['province']) || in_array($infoCod['station_id'], $permission['station'])) return true;
        }
        $regionAlias = $this->convertRegionToAlias[$infoCod['region'] ?? 1] ?? 'NULL';
        if (in_array($regionAlias, $permission['region'])) return true;
        return false;
    }

    //todo: Hàm phụ trợ api hiển thị lương theo tháng
    private $LIST_COD_BP_PHIEN_T8_N2020 = [
        'T103840',
        'T141824',
        'T149735',
        'T352880',
        'T133224',
        'T184416',
        'T74692',
        'T14632',
        'T28958',
        'T359987',
        'T95430',
        'T416590',
        'T403816',
        'T295343',
        'T295256',
        'T406507',
        'T415072',
        'T415069',
        'T389575',
        'T396955'
    ];
    private $IS_NEW_COD = false; // Cod mới hay không
    private $LIST_HOLIDAY_IN_MONTH = [];
    private $LIST_SESSION = [];

    public function extractConfigFineForMonth ($configFines, $codProvinceID, $codStationId) {
        $configFineSession = [];
        foreach ($configFines as $configFine) {
            $configFine = $configFine['ErpConfigSalaryCod'];

            if (!empty($configFine['province_id']) && $codProvinceID != $configFine['province_id']) continue;
            if (!empty($configFine['station_id']) && $codStationId != $configFine['station_id']) continue;

            $fine = json_decode($configFine['fine'], true);

            foreach ($fine as $f) {
                if (in_array($f['id'] ?? null, ['session_2_2', 'session_0_2', 'session_1_1', 'session_0_1']))
                    $configFineSession[] = [
                        'option'            => $f['id'],
                        'from_date'         => $configFine['from_date'],
                        'to_date'           => $configFine['to_date'],
                        'alias_apply'       => $fine['alias_apply']     ?? [],
                        'alias_not_apply'   => $fine['alias_not_apply'] ?? []
                    ];
            }
        }
        return $configFineSession;
    }


    public function showSalaryByMonth ($codId, $month, $year, $formula) {
        $salaryMonth        = $this->TimeSheets->getRangeTimeSalaryMonth($month, $year);
        $infoCod            = $this->ErpCod->getInfoCod($codId);

        if (empty($infoCod)) return null;

        $this->COD_ORDER    = $infoCod['cod_order'];
        $configFines        = $this->ErpConfigSalaryCod->getConfigFineForMonth($salaryMonth['start_date'], $salaryMonth['end_date'], $infoCod['region'], $infoCod['province_id'], $infoCod['station_id']);
        $configFineSession  = $this->extractConfigFineForMonth($configFines, $infoCod['province_id'], $infoCod['station_id']);

        if (!$this->validatePermission($infoCod)) return null;

        $isMainUser      = $this->ErpUser->checkMainUser($infoCod['user_id']);
        $currentContract = $this->ErpEmpContract->getContractByUserId($infoCod['user_id']);

        $maxSessionMinusByWorkType = self::$MAX_SESSION_BY_WORK_TYPE[$infoCod['work_type_id']] ?? 2; // Part time max phiên là 1
        $dataWork = $this->TimeSheets->getTimeSheetData($infoCod['user_id'], $salaryMonth['start_date'], $salaryMonth['end_date']);
        $to = !empty($dataWork['disable_date']) ? min([$salaryMonth['end_date'], $dataWork['disable_date']]) : $salaryMonth['end_date'];
        if (strtotime($to) > strtotime($salaryMonth['end_date'])) $to = $salaryMonth['end_date'];
        $from = max($salaryMonth['start_date'], $dataWork['active_date']);
        $this->LIST_HOLIDAY_IN_MONTH = $this->ErpSgwHolidayWorkshiftConfig->getListHolidayByRangTime($salaryMonth['start_date'], $salaryMonth['end_date']);
        $listDate = $this->Calendar->getListDateWithoutSundayAndHoliday($from, $to);
        $this->IS_NEW_COD = strtotime($from) > strtotime($salaryMonth['start_date']);
        // Số ngày cod cần đi làm
        $listOfSession = array_fill_keys(array_merge($listDate, $this->LIST_HOLIDAY_IN_MONTH), 0);
        $requireWorkDate = count($listDate);
        $session = [
            'standard' => $dataWork['require_workshift_in_month'] * $maxSessionMinusByWorkType,
            'off_session' => $dataWork['require_workshift_in_month'] * $maxSessionMinusByWorkType - count($this->LIST_HOLIDAY_IN_MONTH) * $maxSessionMinusByWorkType, // Không tính thiếu phiên đối với ngày lễ
            'allowance_session' => 0
        ];
        $listWorkShiftByDate = [];

        if (strtotime($from) > strtotime($salaryMonth['start_date'])) {
            $session['off_session'] = count($listDate) * $maxSessionMinusByWorkType;
        }

        if (strtotime($to) < strtotime($salaryMonth['end_date']) && $month > 8 && $year > 2019) {
            $session['off_session'] = count($listDate) * $maxSessionMinusByWorkType;
        }

        // Logic tính lương đối với các loại lương cần tính theo từng ca của từng vùng trong ngày
        $data = $this->ErpSgwCodSalaryKpi->getAllDataByRangeTime($salaryMonth['start_date'], $salaryMonth['end_date'], $codId);

        $listOverTimeSalary = [];

        $dataReturn = [
            // lương
            'fine_clean_salary' => 0, // Phạt không sạch phiên
            'fine_clean_package' => 0,
            'd_reward_speed_salary' => 0, // tổng lương thưởng tốc độ giao
            'd_reward_quality_salary' => 0, // tổng lương thưởng chất lượng giao
            'd_reward_quality_salary_no_clean' => 0, // tổng lương thưởng chất lượng giao chưa cộng sạch phiên
            'd_pkg_reward_no_clean' => 0, // Tổng đơn thưởng phiên không phạt sạch tuyến
            'p_reward_quality_salary' => 0, // tổng lương chất lượng lấy
            'r_reward_quality_salary' => 0, // tổng lương chất lượng trả
            'r_add_salary' => 0, // tổng lương vượt 20 đơn trả
            'p_add_salary' => 0, // tổng lương vượt 20 đơn lấy hoặc lấy bằng xe tải
            'bonus_work_on_night' => 0, // tổng lương phụ cấp tối
            'bonus_work_on_sunday' => 0, // phụ cấp chủ nhật
            'holiday_salary' => round($currentContract['salary'] / ($dataWork['require_workshift_in_month'] ?? 1) * $dataWork['holiday'], 2), // lương lễ
            'leave_salary' => 0, // lương phép
            'weight_salary' => 0, // lương tích lũy kg
            'pl_salary' => 0, // lương theo điểm
            'd_p_r_salary' => 0, // Lương thù lao giao lấy trả
            'allowance_type_salary' => 0, // Phụ cấp chạy linh động
            'detail_coupon' => [], // cơ chế thưởng coupon kỳ lương tháng 7 năm 2020
            'tip_cod_salary' => 0,
            // sản lượng
            'night_session' => 0, // phiên tối
            'overtime_session' => 0, // phiên tăng ca
            'overtime_normal_session' => 0, // phiên tăng ca ngày thường
            'overtime_holiday_session' => 0, // phiên tăng ca ngày lễ
            'leave' => $dataWork['count_on_leave'] ?? 0, // công phép
            'holiday' => $dataWork['holiday'] ?? 0, // công lễ
            'd_pkg_reward' => 0, // Tổng đơn thưởng tuần + đơn thưởng CL giao - Đơn phạt sạch phiên
            'd_pkg_reward_speed' => 0, // tổng đơn thưởng tốc độ giao
            'd_pkg_reward_quality' => 0, // tổng đơn thưởng chất lượng giao
            'p_pkg_reward_quality' => 0, // tổng đơn thưởng chất lượng lấy
            'r_pkg_reward_quality' => 0, // tổng đơn thưởng chất lượng trả
            'd_pkg_weight' => 0, // tổng cân nặng giao tích lũy
            'p_r_pkg_weight' => 0, // tổng cân nặng lấy trả tích lũy
            'd_pkg_success' => 0, // tổng đơn giao thành công
            'p_pkg_success' => 0, // tổng đơn lấy thành công
            'r_pkg_success' => 0, // tổng đơn trả thành công
            'p_pl_success' => 0, // điểm lấy thành công
            'r_pl_success' => 0, // điểm trả thành công
            'reward_salary' => 0, // tổng thưởng
            'integration_index' => 0,
            'detail_pkg_by_area' => []
        ];
        $listWorkShift = ['ws_1', 'ws_2', 'ws_3'];
        foreach ($data as $dataInDate) {
            $maxSessionMinus = $maxSessionMinusByWorkType;
            $allowanceSession = 0;
            $date = $dataInDate['ErpSgwCodSalaryKpi']['date'];
            if (in_array($date, $this->LIST_DATE_NOT_CAL)) continue;
            $isOverTime = $dataWork['list_sunday_holiday_workshift'][$date]['over_time'] ?? 0;
            foreach ($listWorkShift as $workShift) {
                if (!empty($dataInDate['ErpSgwCodSalaryKpi'][$workShift])) {
                    if ($workShift == 'ws_3') $dataReturn['night_session']++;
                    $dataWorkShift = json_decode($dataInDate['ErpSgwCodSalaryKpi'][$workShift], true);
                    if (!empty($dataWorkShift['validate_allowance'])) {
                        $allowanceSession += $dataWorkShift['validate_allowance'] ?? 0;
                        $allowanceSession = min($maxSessionMinusByWorkType, $allowanceSession);
                    }
                    if (!isset($listOfSession[$date])) $listOfSession[$date] = 0;
                    if (!empty($dataWorkShift['validate_session'])) {
                        // Cộng số phiên theo ngày
//                        if (!isset($listOfSession[$date])) $listOfSession[$date] = $dataWorkShift['validate_session'];
                        $listOfSession[$date] += $dataWorkShift['validate_session'];
                        // Nếu số phiên vượt qua số phiên tiêu chuẩn của cod thì sẻ gán bằng số phiên chuẩn
                        if ($listOfSession[$date] > $maxSessionMinusByWorkType) $listOfSession[$date] = $maxSessionMinusByWorkType;

                        if (in_array($date, $this->LIST_HOLIDAY_IN_MONTH)) continue;
                        if ($maxSessionMinus > 0 && empty($isOverTime)) {
                            $session['off_session'] -= min($maxSessionMinus, $dataWorkShift['validate_session']); // Đảm bảo rằng 1 ngày tối đa chỉ được trừ theo số phiên yêu cầu làm của cod
                            $maxSessionMinus -= $dataWorkShift['validate_session']; // Số phiên còn lại được trừ
                            $maxSessionMinus = max($maxSessionMinus, 0); // Xử lý case âm
                        }
                    }
                }
            }
            $session['allowance_session'] += $allowanceSession;
            $fix = json_decode($dataInDate['ErpSgwCodSalaryKpi']['fix'], true);
            $listWorkShiftByDate[$date] = $fix['total_workshift_work'] ?? 0;
            $dataReturn['fine_clean_salary'] += $fix['fine_clean_salary'] ?? 0;
            $dataReturn['fine_clean_package'] += $fix['fine_clean_package'] ?? 0;
            $dataReturn['d_reward_speed_salary'] += $fix['d_reward_speed_salary'] ?? 0;
            $dataReturn['d_reward_quality_salary'] += $fix['d_reward_quality_salary'] ?? 0;
            $dataReturn['p_reward_quality_salary'] += $fix['p_reward_quality_salary'] ?? 0;
            $dataReturn['r_reward_quality_salary'] += $fix['r_reward_quality_salary'] ?? 0;
            $dataReturn['bonus_work_on_night'] += $fix['bonus_work_on_night'] ?? 0;
            $dataReturn['r_add_salary'] += $fix['r_add_salary'] ?? 0;
            $dataReturn['p_add_salary'] += $fix['p_add_salary'] ?? 0;
            $dataReturn['d_pkg_reward_speed'] += $fix['d_pkg_reward_speed'] ?? 0;
            $dataReturn['d_pkg_reward_no_clean'] += ($fix['d_pkg_reward_quality'] ?? 0) - ($fix['fine_clean_package'] ?? 0);
            $dataReturn['d_pkg_reward_quality'] += $fix['d_pkg_reward_quality'] ?? 0;
            $dataReturn['p_pkg_reward_quality'] += $fix['p_pkg_reward_quality'] ?? 0;
            $dataReturn['r_pkg_reward_quality'] += $fix['r_pkg_reward_quality'] ?? 0;
            // nếu làm chủ nhật tính công tăng ca với điều kiện chấm công tăng ca và làm full time
            $bonusOverTimeOnHoliday = null;
            $holidayConfig = null;
            if (!empty($fix['over_time']) && !empty($isOverTime) && $infoCod['work_type_id'] == 1) {
                $dataReturn['overtime_session'] += min($fix['over_time'], $maxSessionMinusByWorkType);
                // tối đa được tính lương cho 2 ca
                if (in_array($date, $this->LIST_HOLIDAY_IN_MONTH)) $holidayConfig = $this->ErpSgwHolidayWorkshiftConfig->getConfigHoliday($date);
                // Lấy mức config ot ngày lễ
                if (!empty($holidayConfig['overtime_workshift'][$currentContract['has_kpi']])) $bonusOverTimeOnHoliday = $holidayConfig['overtime_workshift'][$currentContract['has_kpi']];
                // Hệ số quy đổi công ot (1 phiên = bao nhiêu công ot)
                $coefficient = max($maxSessionMinusByWorkType, ($bonusOverTimeOnHoliday ?? 0)) / $maxSessionMinusByWorkType;
                // Quy đổi thực phiên thành công ot
                $coefficientOverTime = min($maxSessionMinusByWorkType, $fix['over_time']) * $coefficient;
                if ($currentContract['type_contract'] == 'service_contract') {
                    $overTimeSalary = ErpSalaryTable::$F1000_SALARY_ALLOWANCE_SUNDAY * $coefficientOverTime;
                    $dataReturn['bonus_work_on_sunday'] += $overTimeSalary;
                    if (!isset($listOverTimeSalary[$date])) $listOverTimeSalary[$date] = $overTimeSalary;
                    else $listOverTimeSalary[$date] += $overTimeSalary;
                }
                if (in_array($currentContract['type_contract'], ErpEmpContract::$danh_sach_hop_dong_lao_dong)) {
                    $overTimeSalary = round($currentContract['salary'] / $dataWork['require_workshift_in_month'] * $coefficientOverTime, 2);
                    $dataReturn['bonus_work_on_sunday'] += $overTimeSalary;
                    if (!isset($listOverTimeSalary[$date])) $listOverTimeSalary[$date] = $overTimeSalary;
                    else $listOverTimeSalary[$date] += $overTimeSalary;
                }
            } elseif (in_array($date, $this->LIST_HOLIDAY_IN_MONTH) && $infoCod['work_type_id'] == 1) { // Tăng ca ngày lễ khác chủ nhật
                $overTimeSession = $this->isCalOverTimeHoliday($infoCod['cod_order'], $salaryMonth, $date);
                if (empty($overTimeSession)) continue;
                $holidayConfig = $this->ErpSgwHolidayWorkshiftConfig->getConfigHoliday($date);
                if (!empty($holidayConfig['overtime_workshift'][$currentContract['has_kpi']])) $bonusOverTimeOnHoliday = $holidayConfig['overtime_workshift'][$currentContract['has_kpi']];
                // Hệ số quy đổi công ot (1 phiên = bao nhiêu công ot)
                $coefficient = max($maxSessionMinusByWorkType, ($bonusOverTimeOnHoliday ?? 0)) / $maxSessionMinusByWorkType;
                // Quy đổi thực phiên thành công ot
                $coefficientOverTime = min($maxSessionMinusByWorkType, $overTimeSession) * $coefficient;
                $dataReturn['overtime_session'] += $overTimeSession;
                if ($currentContract['type_contract'] == 'service_contract') {
                    $overTimeSalary = ErpSalaryTable::$F1000_SALARY_ALLOWANCE_SUNDAY * $coefficientOverTime;
                    $dataReturn['bonus_work_on_sunday'] += $overTimeSalary;
                    if (!isset($listOverTimeSalary[$date])) $listOverTimeSalary[$date] = $overTimeSalary;
                    else $listOverTimeSalary[$date] += $overTimeSalary;
                }
                if (in_array($currentContract['type_contract'], ErpEmpContract::$danh_sach_hop_dong_lao_dong)) {
                    $overTimeSalary = round($currentContract['salary'] / $dataWork['require_workshift_in_month'] * $coefficientOverTime, 2);
                    $dataReturn['bonus_work_on_sunday'] += $overTimeSalary;
                    if (!isset($listOverTimeSalary[$date])) $listOverTimeSalary[$date] = $overTimeSalary;
                    else $listOverTimeSalary[$date] += $overTimeSalary;
                }
            }
        }
        $dataReturn['fine_clean_package'] = floor( $dataReturn['fine_clean_package'] * 100) / 100;
        $dataReturn['d_reward_speed_salary'] = floor($dataReturn['d_reward_speed_salary'] * 100) / 100;
        $dataReturn['d_pkg_reward_speed'] = floor($dataReturn['d_pkg_reward_speed'] * 100) / 100;
        $dataReturn['d_pkg_reward_quality'] = floor($dataReturn['d_pkg_reward_quality'] * 100) / 100;
        foreach ($this->LIST_HOLIDAY_IN_MONTH as $dateHoliday) {
            $dataReturn['overtime_holiday_session'] += $listOfSession[$dateHoliday] ?? 0;
        }
        $dataReturn['overtime_normal_session'] = $dataReturn['overtime_session'] - $dataReturn['overtime_holiday_session'];

        // Các logic bỏ phạt hard code tháng cũ không cần care ở source code hiện tại
        $this->excludeFineT5T6V1($session, $listOfSession, $maxSessionMinusByWorkType, $month, $year, $infoCod['alias'], $infoCod['province_id']);
        $this->excludeFineT8N2020($session, $listOfSession, $maxSessionMinusByWorkType, $infoCod['alias'], $month, $year);
        $this->excludeFineT6ByErrorSystem($session, $listOfSession, $maxSessionMinusByWorkType, $infoCod['work_type_id'], $infoCod['alias'], $month, $year);

        if (!empty($configFineSession)) {
            $optionConfig = [
                'session_2_2' => [2, 2], // Giảm 2 phiên thiếu, Tăng 2 phiên on
                'session_1_1' => [1, 1],
                'session_0_2' => [0, 2],
                'session_0_1' => [0, 1]
            ];
            foreach ($listOfSession as $dateCheck => $sessionOfDate) {
                if ($sessionOfDate == $maxSessionMinusByWorkType) continue;

                foreach ($configFineSession as $configFine) {
                    $option = $optionConfig[$configFine['option']] ?? null;

                    // Yêu cầu cod đi làm tối thiểu 1 phiên đối với config x2 phiên
                    if (($option[0] == 1 || $option[1] == 1) && empty($sessionOfDate)) continue;

                    if (empty($option)) continue;

                    // Không trong thời gian bỏ phạt
                    if (strtotime($configFine['from_date']) > strtotime($dateCheck)) continue;
                    if (strtotime($configFine['to_date']) < strtotime($dateCheck)) continue;

                    // Cod nằm trong list cod không được bỏ phạt
                    if (!empty($configFine['alias_not_apply']) && in_array($infoCod['alias'], $configFine['alias_not_apply'])) continue;

                    // Không nằm trong danh sách chỉ những cod mới được bỏ phạt
                    if (!empty($configFine['alias_apply']) && !in_array($infoCod['alias'], $configFine['alias_apply'])) continue;

                    // TH đã đủ phiên
                    if ($listOfSession[$dateCheck] == $maxSessionMinusByWorkType) break;

                    // TH thừa phiên
                    if ($listOfSession[$dateCheck] == 1) {
                        if ($option[0] == 2) $option[0] = 1;
                        if ($option[1] == 2) $option[1] = 1;
                    }

                    $sessionMinus = $option[1]; // Số phiên off được giảm dự kiến
                    $sessionMinus = min($maxSessionMinusByWorkType, $sessionMinus); // Số phiên off được giảm tối đa một ngày thực tế

                    if ($sessionMinus > 0) $session['off_session'] -= $sessionMinus; // Giảm số phiên nghỉ

                    $listOfSession[$dateCheck]  += $option[0]; // Cập nhập lại list phiên
                }
            }
        }

        if ($session['off_session'] < 0) $session['off_session'] = 0;

        $session['on_session'] = array_sum($listOfSession); // Thực phiên

        // logic tính lương bỏ các ngày theo y/c
        $listDateCal = array_keys($listOfSession);
        if (!empty($this->LIST_DATE_NOT_CAL))
            foreach ($listDateCal as $key => $dateCheckForCal) {
                if (in_array($dateCheckForCal, $this->LIST_DATE_NOT_CAL)) unset($listDateCal[$key]);
            }

        //logic tính lương đối với các loại lương tích lũy tính theo tháng
        $dataForCal = $this->BigDataDbStationProcessCod->getDataForCalSalary($salaryMonth['start_date'], $listDateCal, $infoCod['cod_order']);
        if (empty($dataForCal)) return null;
        $dataSalaryScore = $dataSalaryDeliver = $dataSalaryPick = $dataSalaryReturn = $dataSalaryBonusWeightDeliver = $dataSalaryBonusWeightPickReturn = [];
        $invalidDeliverWeight = $this->BigDataDbStationProcessCod->getListInvalidPkgWeight($salaryMonth['start_date'], $listDateCal, $infoCod['cod_order']);

        foreach ($dataForCal as $dataArea) {
            $dataReturn['d_pkg_success'] += $dataArea[0]['d_success'];
            $dataReturn['p_pl_success'] += $dataArea[0]['p_score'];
            $dataReturn['r_pl_success'] += $dataArea[0]['r_score'];
            $dataReturn['p_pkg_success'] += $dataArea[0]['p_success'];
            $dataReturn['r_pkg_success'] += $dataArea[0]['r_success'];
            $dataReturn['d_pkg_weight'] += $dataArea[0]['d_weight'];
            $dataReturn['p_r_pkg_weight'] += $dataArea[0]['p_weight'] + $dataArea[0]['r_weight'];
            $area = $dataArea['BigDataDbStationProcessCod']['area'];
            if (empty($area)) $area = 1;
            $dataReturn['detail_pkg_by_area'][$area]['deliver_success'] = $dataArea[0]['d_success'];
            $dataReturn['detail_pkg_by_area'][$area]['deliver_total'] = $dataArea[0]['d_total'];
            $dataReturn['detail_pkg_by_area'][$area]['pick_success'] = $dataArea[0]['p_score'];
            $dataReturn['detail_pkg_by_area'][$area]['pick_package_success'] = $dataArea[0]['p_success'];
            $dataReturn['detail_pkg_by_area'][$area]['pick_total'] = $dataArea[0]['tp_total'];
            $dataReturn['detail_pkg_by_area'][$area]['return_total'] = $dataArea[0]['r_total'];
            $dataReturn['detail_pkg_by_area'][$area]['return_success'] = $dataArea[0]['r_score'];
            $dataReturn['detail_pkg_by_area'][$area]['pick_truck'] = $dataArea[0]['p_truck'];
            $dataReturn['detail_pkg_by_area'][$area]['pick_add'] = $dataArea[0]['p_pkg_bonus'];
            $dataReturn['detail_pkg_by_area'][$area]['return_add'] = $dataArea[0]['r_pkg_bonus'];
            $dataReturn['detail_pkg_by_area'][$area]['pick_return_weight'] = $dataArea[0]['p_weight'] + $dataArea[0]['r_weight'];
            $dataReturn['detail_pkg_by_area'][$area]['deliver_weight'] = $dataArea[0]['d_weight'];
            $dataReturn['detail_pkg_by_area'][$area]['return_package_success'] = $dataArea[0]['r_success'];
            // thù lao điểm
            if (!isset($dataSalaryScore[$area])) $dataSalaryScore[$area] = $dataArea[0]['d_success'] + $dataArea[0]['p_score'] +$dataArea[0]['r_score'];
            else $dataSalaryScore[$area] += $dataArea[0]['d_success'] + $dataArea[0]['p_score'] +$dataArea[0]['r_score'];
            // thù lao giao
            if (!isset($dataSalaryDeliver[$area])) $dataSalaryDeliver[$area] = $dataArea[0]['d_success'];
            else $dataSalaryDeliver[$area] += $dataArea[0]['d_success'];
            // thù lao lấy
            if (!isset($dataSalaryPick[$area])) $dataSalaryPick[$area] = $dataArea[0]['p_success'] - $dataArea[0]['p_pkg_bonus'] - $dataArea[0]['p_truck'];
            else $dataSalaryPick[$area] += $dataArea[0]['p_success'] - $dataArea[0]['p_pkg_bonus'] - $dataArea[0]['p_truck'];
            // thù lao trả
            if (!isset($dataSalaryReturn[$area])) $dataSalaryReturn[$area] = $dataArea[0]['r_success'] - $dataArea[0]['r_pkg_bonus'];
            else $dataSalaryReturn[$area] += $dataArea[0]['r_success'] - $dataArea[0]['r_pkg_bonus'];
            // tích lũy cân nặng giao
            if (!isset($dataSalaryBonusWeightDeliver[$area])) $dataSalaryBonusWeightDeliver[$area] = $dataArea[0]['d_weight'];
            else $dataSalaryBonusWeightDeliver[$area] += $dataArea[0]['d_weight'];
            // tích lũy cân nặng lấy trả
            if (!isset($dataSalaryBonusWeightPickReturn[$area])) $dataSalaryBonusWeightPickReturn[$area] = ($dataArea[0]['p_weight'] + $dataArea[0]['r_weight']);
            else $dataSalaryBonusWeightPickReturn[$area] += $dataArea[0]['p_weight'] + $dataArea[0]['r_weight'];

        }
        foreach ($invalidDeliverWeight as $invalidWeightByArea) {
            $invalidWeightByArea = $invalidWeightByArea['BigDataDbStationProcessCod'];
            $area = $invalidWeightByArea['area'];
            if (empty($area)) $area = 1;
            if ($invalidWeightByArea['d_weight'] < 0) {
                $dataSalaryBonusWeightDeliver[$area] -= $invalidWeightByArea['d_weight'];
                $dataReturn['d_pkg_weight'] -= $invalidWeightByArea['d_weight'];
                $dataReturn['detail_pkg_by_area'][$area]['deliver_weight'] -= $invalidWeightByArea['d_weight'];
            }
            if ($invalidWeightByArea['p_weight'] < 0) {
                $dataSalaryBonusWeightPickReturn[$area] -= $invalidWeightByArea['p_weight'];
                $dataReturn['p_r_pkg_weight'] -= $invalidWeightByArea['p_weight'];
                $dataReturn['detail_pkg_by_area'][$area]['pick_return_weight'] -= $invalidWeightByArea['p_weight'];
            }
            if ($invalidWeightByArea['r_weight'] < 0) {
                $dataSalaryBonusWeightPickReturn[$area] -= $invalidWeightByArea['r_weight'];
                $dataReturn['p_r_pkg_weight'] -= $invalidWeightByArea['r_weight'];
                $dataReturn['detail_pkg_by_area'][$area]['pick_return_weight'] -= $invalidWeightByArea['r_weight'];
            }
        }
        $this->LIST_SESSION = $listOfSession;
        $dataIntegration = $this->calIntegration($infoCod, $salaryMonth);
        $dataReturn['integration_index'] += $dataIntegration['integration_index'] ?? 0;
        $dataReturn['pl_salary'] = $this->salaryByScore($dataSalaryScore, $session, $currentContract['type_contract'], $listOfSession, $infoCod['work_type_id'], $dataReturn['overtime_session'], $requireWorkDate); // Thù lao theo điểm;
        $kpiDeliver = $this->salaryDeliver($dataSalaryDeliver); //Thù lao giao theo vùng lấy theo d_success của mỗi vùng;
        $dataReturn['integration_salary'] = $dataIntegration['salary_integration'];
        $dataReturn['integration_index']  = $dataIntegration['integration_index'];
        $dataReturn['integration_index_area'] = $dataIntegration['data_area'] ?? [];
        $dataReturn['d_p_r_salary'] += $kpiDeliver;
        $dataReturn['d_p_r_salary'] += $this->salaryPick($dataSalaryPick); //Thù lao lấy =  (p_success - p_pkg_bonus - p_truck) cả tháng của mỗi vùng * Thù Lao Vùng tương ứng/ Tổng (p_success - p_pkg_bonus - p_truck) các vùng +(p_pkg_bonus+p_truck)*500;
        $dataReturn['d_p_r_salary'] += $this->salaryReturn($dataSalaryReturn); //Thù lao trả=  (r_success - r_pkg_bonus) cả tháng của mỗi vùng * Thù Lao Vùng tương ứng/ Tổng (r_success - r_pkg_bonus ) các vùng +(r_pkg_bonus)*500;
        $dataReturn['d_p_r_salary'] += $dataReturn['p_add_salary'] + $dataReturn['r_add_salary'];
        $dataReturn['weight_salary'] += $this->salaryBonusWeightDeliver($dataSalaryBonusWeightDeliver); // Thưởng tích lũy cân nặng giao;
        $dataReturn['d_weight_salary'] = $dataReturn['weight_salary'];
        $dataReturn['weight_salary'] += $this->salaryBonusWeightPickReturn($dataSalaryBonusWeightPickReturn); // Thưởng tích lũy cân nặng lấy trả;
        $dataReturn['p_r_weight_salary'] = $dataReturn['weight_salary'] - $dataReturn['d_weight_salary'];
        $dataReturn['allowance_type_salary'] = $this->calSalaryAllowance($infoCod, $dataSalaryScore, $session, $infoCod['work_type_id'], $dataReturn['overtime_session'], $salaryMonth); // Phụ cấp chạy linh động
        $dataReturn['reward_salary'] += $dataReturn['d_reward_speed_salary'] + $dataReturn['d_reward_quality_salary'] + $dataReturn['p_reward_quality_salary'] + $dataReturn['r_reward_quality_salary'];
        $dataReturn['tip_cod_salary'] = $this->calTipSalary($infoCod, $salaryMonth);

        if (strtotime($salaryMonth['start_date']) >= strtotime($this->dateStartDTeam)) {
            $this->BigDataDbStationProcessCodsV5SuccessByWeek = getInstance('BigDataDbStationProcessCodsV5SuccessByWeek');
            $dataByWeek = $this->BigDataDbStationProcessCodsV5SuccessByWeek->getDataByWeek($salaryMonth['start_date'], $infoCod['cod_order']);
            $dataReturn['reward_by_week'] = $this->salaryRewardPerWeek($dataByWeek, $salaryMonth);
            $dataReturn['total_pkg_week_reward'] = $dataReturn['reward_by_week']['total_reward'];
            $dataReturn['total_salary_week_reward'] = $dataReturn['reward_by_week']['total_salary'];
            $dataReturn['reward_salary'] += $dataReturn['reward_by_week']['total_salary'];
            $dataReturn['d_pkg_reward'] += $dataReturn['reward_by_week']['total_reward'];
            $dataReturn['d_pkg_reward'] = floor($dataReturn['d_pkg_reward'] * 100) / 100;
        }

        $dataReturn['d_pkg_reward'] += $dataReturn['d_pkg_reward_no_clean'] + $dataReturn['fine_clean_package'];

        // Lương phép đối với các cod có hợp đồng lao động
        if (in_array($currentContract['type_contract'], ErpEmpContract::$danh_sach_hop_dong_lao_dong) && !empty($dataReturn['leave'])) $dataReturn['leave_salary'] += round($currentContract['salary'] / $dataWork['require_workshift_in_month'] * $dataReturn['leave'], 2); // lương phép
        if (in_array($infoCod['work_type_id'], [11, 12, 2])) {
            $dataReturn['pl_salary'] = $dataReturn['pl_salary'] / 2;
            $isPartTime =  1;
        }

        $this->couponT7N2020V1($dataReturn, $listDateCal, $salaryMonth, $infoCod['province_id'], $infoCod['position_job'], $infoCod['work_type_id'], $month, $year);

        // Phạt quá cân
        $overWeightSalary = $this->ErpPackageOverWeights->getOverWeightSalary($infoCod['user_id'], $from, $to);

        // Phat the cod
        $this->ErpCodPenalty = getInstance('ErpCodPenalty');
        $codPenalties = $this->ErpCodPenalty->getDataCodPenalty($infoCod['user_id'], 1000, $salaryMonth['start_date']);
        $dataReturn['cod_penalty'] = $codPenalties;

        $typeSalary = in_array($currentContract['type_contract'], ErpEmpContract::$danh_sach_hop_dong_lao_dong) ? 'luong_cung' : 'thu_lao';
        $session['require_session'] = $requireWorkDate * $maxSessionMinusByWorkType;
        $dataReturn['d_p_r_salary'] += $dataReturn['integration_salary'];
        $dataReturn['d_reward_quality_salary_no_clean'] = $dataReturn['d_reward_quality_salary'] - $dataReturn['fine_clean_salary'];
        // Sắp xếp lại mảng sau khi thêm ngày CN vào
        ksort($listOfSession);
        $dataReturn = [
            'fullname' => $infoCod['fullname'],
            'station_name' => $infoCod['station_name'] ?? '',
            'province_name' => $infoCod['province_name'] ?? '',
            'region_id' => $infoCod['region'] ?? null,
            'formula' => $formula,
            'contract' => $currentContract,
            'start_date' => $dataWork['start_date'],
            'join_date' => $dataWork['join_date'],
            'resign_date' => $dataWork['resign_date'],
            'active_date' => $dataWork['active_date'],
            'disable_date' => $dataWork['disable_date'],
            'type_salary' => $typeSalary,
            'main_user' => $isMainUser,
            'count_session' => $session,
            'list_off_sessions' => $listOfSession,
            'list_work_shift_by_date' => $listWorkShiftByDate,
            'list_over_time_salary' => $listOverTimeSalary,
            'salary_cod_province' => $dataReturn,
            'kpi_over_weight' => $overWeightSalary,
            'total_salary' => (
                $dataReturn['reward_salary'] +
                $dataReturn['bonus_work_on_night'] +
                $dataReturn['bonus_work_on_sunday'] +
                $dataReturn['holiday_salary'] +
                $dataReturn['leave_salary'] +
                $dataReturn['weight_salary'] +
                $dataReturn['pl_salary'] +
                $dataReturn['d_p_r_salary'] +
                $dataReturn['allowance_type_salary'] +
                $dataReturn['total_coupon'] +
                $overWeightSalary['salary'] ?? 0
            ),
            'salary_month' => $salaryMonth,
            'month' => $month,
            'year' => $year,
            'cod_alias' => $infoCod['alias'],
            'is_part_time_emp' => $isPartTime ?? 0
        ];
        return $dataReturn;
    }

    public function isCalOverTimeHoliday ($codOrder, $salaryMonth, $date) {
        $dataForCal = $this->BigDataDbStationProcessCod->getAllDataCodProvinceByListDate($salaryMonth['start_date'], $date, $codOrder);

        if (empty($dataForCal)) return false;

        $dataWorkShift   = [];
        $overTimeSession = 0;

        foreach ($dataForCal as $data) {
            $data       = $data['BigDataDbStationProcessCod'];
            $area       = $data['area'] ?? 1;
            $workShift  = $data['workshift'] ?? 1;

            $dataWorkShift[$workShift][$area] = $data;
        }

        foreach ($dataWorkShift as $wsData) {
            $overTimeSession += $this->calSalaryOverTimeSunday($wsData);
        }

        return $overTimeSession;
    }

    public function calIntegration($infoCod, $salaryMonth) {
        $dataReturn = [
            'active_date'           => null,
            'date_own_integration'  => null,
            'data_area'             => null,
            'salary_integration'    => 0,
            'integration_index'     => 0
        ];
        if (empty($infoCod['active_date'])) return $dataReturn;

        // Lấy active nhỏ nhất
        $listOwnUsers = $this->ErpUser->getListUsersHasSameMainProfile($infoCod['user_id']);
        foreach ($listOwnUsers as $user) {
            if ($user['work_type_id'] != 1) continue;
            if ($user['active_date'] < $infoCod['active_date']) $infoCod['active_date'] = $user['active_date'];
        }

        // Ngày chỉ số hội nhập bắt đầu có hiệu lực
        if (strtotime($salaryMonth['start_date']) < strtotime('2020-10-21')) return $dataReturn;

        $dataReturn['active_date']          = $infoCod['active_date'];
        if (strtotime($infoCod['active_date']) < strtotime('2020-11-01')) return $dataReturn;
        $dataReturn['date_own_integration'] = date('Y-m-d', strtotime('+2 months', strtotime($infoCod['active_date'])));

        if (strtotime($dataReturn['date_own_integration']) < strtotime($salaryMonth['start_date'])) return $dataReturn;

        $from       = max('2020-11-01', $salaryMonth['start_date']);
        $to         = min($salaryMonth['end_date'], $dataReturn['date_own_integration']);
        $listDate   = $this->Calendar->getAllDate($from, $to);

        if (empty($listDate)) return $dataReturn;

        $dataForCal = $this->BigDataDbStationProcessCod->getDataForCalSalary($salaryMonth['start_date'], $listDate, $infoCod['cod_order']);
        if (empty($dataForCal)) return $dataReturn;

        foreach ($dataForCal as $data) {
            $area = $data['BigDataDbStationProcessCod']['area'] ?? 1;
            $dataReturn['data_area'][$area]     = $data[0]['integration_index'];
            $dataReturn['integration_index']    += $data[0]['integration_index'];
            $salaryApplied = getValueMapToTable($area, ErpSalaryTable::$F1000_SALARY_BY_DELIVER);
            $dataReturn['salary_integration']   += $salaryApplied * $dataReturn['data_area'][$area] ?? 0;
        }
        return $dataReturn;
    }

    public function calTipSalary ($infoCod, $salaryMonth) {
        $this->ErpBill = getInstance('ErpBill');
        $listBillTip  = $this->ErpBill->getBillTip($infoCod['cod_id'], $salaryMonth['start_date'], $salaryMonth['end_date']);
        if (empty($listBillTip)) return 0;
        $this->ErpPrepaymentTransaction = getInstance('ErpPrepaymentTransaction');
        $tipSalary = $this->ErpPrepaymentTransaction->getTipCodByBill($listBillTip, ['SUM(money) as total_tip']);
        return  $tipSalary[0][0]['total_tip'] ?? 0;
    }

    public function calCouponT7N2020 ($salartMonth, $listDate) {
        $data = $this->BigDataDbStationProcessCod->getDataCountCouponT7N2020($salartMonth['start_date'], $this->COD_ORDER, $listDate);
        if (empty($data)) return [];

        $dataReturn = [];
        foreach ($data as $dataWorkShift) {
            $date = $dataWorkShift['BigDataDbStationProcessCod']['cur_date'];
            if ($dataWorkShift['BigDataDbStationProcessCod']['workshift'] == 3) continue;

            if (!isset($dataReturn[$date])) $dataReturn[$date] = 0;
            $deliverSuccess    = $dataWorkShift[0]['d_success'] ?? 0;
            $dataReturn[$date] += getValueMapToTable($deliverSuccess, ErpSalaryTable::$F1000_COUPON_T7_2020);
        }

        return $dataReturn;
    }

    // todo: Hàm phụ trợ api giao lấy trả thành công
    public function showListPkgSuccess ($codOrder, $date, $workShift, $type, $page, $limit) {
        $salaryMonth = $this->TimeSheets->getSalaryMonthByDate($date);
        if ($type == 'deliver') {
            $totalData = $this->DataErpCodsPackageOrderDeliverV5->countTotalData($salaryMonth['from'], $codOrder, $date, $workShift);
            $data = $this->DataErpCodsPackageOrderDeliverV5->getListPkgByDate($salaryMonth['from'], $codOrder, $date, $workShift, $page, $limit);
            $dataIntegration = $this->DataErpCodsPackageOrderDeliverV5->getListIntegration($salaryMonth['from'], $codOrder, $date, $workShift, $page, $limit);
        } elseif ($type == 'pick') {
            $type = 'pickup';
            $totalData = $this->DataErpCodsPackageOrderPickV5->countTotalData($salaryMonth['from'], $codOrder, $date, $workShift);
            $data = $this->DataErpCodsPackageOrderPickV5->getListPkgByDate($salaryMonth['from'], $codOrder, $date, $workShift, $page, $limit);
        } elseif ($type == 'return') {
            $totalData = $this->DataErpCodsPackageOrderReturnV5->countTotalData($salaryMonth['from'], $codOrder, $date, $workShift);
            $data = $this->DataErpCodsPackageOrderReturnV5->getListPkgByDate($salaryMonth['from'], $codOrder, $date, $workShift, $page, $limit);
        } else return null;
        if (empty($totalData) || empty($data)) return null;
        $listPkg = array_keys($data);
        $listInfoOfPkg = $this->ErpPackageArchive->getPkgInfoByListPkgOrder($listPkg);
        $listAddressPkg = $this->ErpPackageAddress->getAddressOfListPkg($type, $listPkg);
        foreach ($listInfoOfPkg as $pkgInfo) {
            $pkgOrder = $pkgInfo['ErpPackageArchive']['order'];
            if (isset($data[$pkgOrder])) {

                $data[$pkgOrder] = [
                    'alias' => $pkgInfo['ErpPackageArchive']['alias'],
                    'area' => $data[$pkgOrder],
                    'weight' => (empty($pkgInfo['ErpPackageArchive']['weight'])) ? 0 : $pkgInfo['ErpPackageArchive']['weight'],
                    'address' => ''
                ];
                if (isset($dataIntegration[$pkgOrder])) {
                    $data[$pkgOrder]['integration_index'] = $dataIntegration[$pkgOrder];
                    $data[$pkgOrder]['integration_converted'] = self::$CONVERT_INTEGRATION[$dataIntegration[$pkgOrder]] ?? $dataIntegration[$pkgOrder];
                }
                if ($data[$pkgOrder]['weight'] > 1) $data[$pkgOrder]['save_weight'] = (string)($data[$pkgOrder]['weight'] - 1);
                else $data[$pkgOrder]['save_weight'] = (string)0;
            }
        }
        foreach ($listAddressPkg as $pkgAddress) {
            $pkgOrder = $pkgAddress['ErpPackageAddress']['package_order'];
            if (!isset($data[$pkgOrder])) continue;
            $address = $pkgAddress['ErpPackageAddress']['first_address'] . ' - ' . $pkgAddress['ErpPackageAddress']['ward'] . ' - ' . $pkgAddress['ErpPackageAddress']['district'] . ' - ' . $pkgAddress['ErpPackageAddress']['province'];
            $data[$pkgOrder]['address'] = $address;
        }
        return [
            'data' => $data,
            'total_row' => (int)$totalData[0]['numRow']
        ];
    }

    //todo: Hàm lấy thông tin lấy trả group theo điểm
    public function listPickReturnByGroup ($codOrder, $date, $workShift, $type) {
        $salaryMonth = $this->TimeSheets->getSalaryMonthByDate($date);

        if ($type == 'pick') {
            $type = 'pickup';
            $data = $this->DataErpCodsPackageOrderPickV5->getListPkgByCondition($salaryMonth['from'], $codOrder, $date, $workShift);
        }

        if ($type == 'return') $data = $this->DataErpCodsPackageOrderReturnV5->getListPkgByCondition($salaryMonth['from'], $codOrder, $date, $workShift);

        if (empty($data)) return null;

        $alias              = ($type == 'pickup') ? 'DataErpCodsPackageOrderPickV5' : 'DataErpCodsPackageOrderReturnV5';
        $dataReturn         = [];
        $listPkgByGroup     = [];
        foreach ($data as $d) {
            $keyIndex = $d[$alias]['shop_code'] . $d[$alias]['pick_tel'];
            if (empty($keyIndex)) $keyIndex = 'empty_shop_tel';

            $dataReturn[$keyIndex][$d[$alias]['pkg_order']] = $d[$alias];
            $listPkgByGroup[$keyIndex][] = $d[$alias]['pkg_order'];
        }

        foreach ($listPkgByGroup as $key => $listPkg) {
            $listInfoOfPkg  = $this->ErpPackageArchive->getPkgInfoByListPkgOrder($listPkg);

            foreach ($listInfoOfPkg as $pkgInfo) {

                $pkgOrder = $pkgInfo['ErpPackageArchive']['order'];

                if (in_array($pkgOrder, $listPkg)) {
                    $dataReturn[$key][$pkgOrder] = [
                        'alias' => $pkgInfo['ErpPackageArchive']['alias'],
                        'shop_code' => $dataReturn[$key][$pkgOrder]['shop_code'],
                        'pick_tel' => $dataReturn[$key][$pkgOrder]['pick_tel'],
                        'area' => $dataReturn[$key][$pkgOrder]['area'],
                        'weight' => (empty($pkgInfo['ErpPackageArchive']['weight'])) ? 0 : $pkgInfo['ErpPackageArchive']['weight'],
                        'address' => ''
                    ];
                    if ($dataReturn[$key][$pkgOrder]['weight'] > 1) $dataReturn[$key][$pkgOrder]['save_weight'] = (string)($dataReturn[$key][$pkgOrder]['weight'] - 1);
                    else $dataReturn[$key][$pkgOrder]['save_weight'] = (string)0;
                }
            }

            $listAddressPkg = $this->ErpPackageAddress->getAddressOfListPkg($type, $listPkg);

            foreach ($listAddressPkg as $pkgAddress) {
                $pkgOrder = $pkgAddress['ErpPackageAddress']['package_order'];
                if (!in_array($pkgOrder, $listPkg)) continue;

                $address = $pkgAddress['ErpPackageAddress']['first_address'] . ' - ' . $pkgAddress['ErpPackageAddress']['ward'] . ' - ' . $pkgAddress['ErpPackageAddress']['district'] . ' - ' . $pkgAddress['ErpPackageAddress']['province'];
                $dataReturn[$key][$pkgOrder]['address'] = $address;
            }
        }

        // Unset key of array
        $dataConvert = [];
        foreach ($dataReturn as $pkgByPlace) {
            $dataConvert[] = array_values($pkgByPlace);
        }
        return $dataConvert;
    }

    // todo: hàm phụ trợ api tốc độ
    public function showListPkgSpeed ($codOrder, $date, $workShift, $page, $limit, $infoCod) {
        $salaryMonth = $this->TimeSheets->getSalaryMonthByDate($date);
        $data = $this->DataErpCodsPackageOrderDeliverV5->getDataSpeedPkg($salaryMonth['from'], $codOrder, $date, $workShift, $page, $limit);
        $dataForCalSpeed = $this->BigDataDbStationProcessCod->getAllDataCodProvinceByListDate($salaryMonth['from'], $date, $codOrder, $workShift);
        $totalData = 0;
        $dataArea = [];
        if (empty($data) || empty($dataForCalSpeed)) return null;
        foreach ($dataForCalSpeed as $detailData) {
            $area = $detailData['BigDataDbStationProcessCod']['area'];
            $dataInDate[$area] = $detailData['BigDataDbStationProcessCod'];
            $totalData += $detailData['BigDataDbStationProcessCod']['d_total'];
            if ($detailData['BigDataDbStationProcessCod']['d_speed'] > 0)
                $dataArea[$area] = [
                    'total_pkg' => $detailData['BigDataDbStationProcessCod']['d_total'],
                    'average_speed' => $detailData['BigDataDbStationProcessCod']['d_speed']
                ];
        }
        $this->DATE = $date;
        $dataDeliver = $this->salaryBonusSpeed($dataInDate, $infoCod);
        $dataReturn = [];
        foreach ($data as $detailData) {
            $pkgOrder = $detailData['DataErpCodsPackageOrderDeliverV5']['pkg_order'];
            $dataReturn[$pkgOrder] = [
                'area' => $detailData['DataErpCodsPackageOrderDeliverV5']['area'],
                'deliver_speed' => $detailData['DataErpCodsPackageOrderDeliverV5']['d_speed'],
                'area_status' => $dataDeliver['area_status']
            ];
        }
        $listPkg = array_keys($dataReturn);
        $infoPkg = $this->ErpPackageArchive->getPkgInfoByListPkgOrder($listPkg);
        foreach ($infoPkg as $pkgInfo) {
            $pkgOrder = $pkgInfo['ErpPackageArchive']['order'];
            if (isset($dataReturn[$pkgOrder])) $dataReturn[$pkgOrder]['alias'] = $pkgInfo['ErpPackageArchive']['alias'];
        }
        return [
            'data_area' => $dataArea,
            'data' => $dataReturn,
            'total_row' => $totalData
        ];
    }

    // todo: Hàm phụ trợ api thưởng lấy
    public function showPickReward ($codOrder, $date, $workShift, $page, $limit) {
        $salaryMonth = $this->TimeSheets->getSalaryMonthByDate($date);
        $totalData = $this->DataErpCodsPackageOrderPickV5->countTotalData($salaryMonth['from'], $codOrder, $date, $workShift, null, -1);
        $data = $this->DataErpCodsPackageOrderPickV5->geInfoPickPackage($salaryMonth['from'], $codOrder, $date, $workShift, $page, $limit, -1);
        if (empty($totalData[0]['numRow']) || empty($data)) return null;
        $totalData = (int)$totalData[0]['numRow'];
        $dataReturn = [];
        foreach ($data as $pkgInfo) {
            $pkgOrder = $pkgInfo['DataErpCodsPackageOrderPickV5']['pkg_order'];
            if ($pkgInfo['DataErpCodsPackageOrderPickV5']['p_true'] == -1) {
                $totalData--;
                continue;
            }
            $dataReturn[$pkgOrder] = [
                'package_true' => $pkgInfo['DataErpCodsPackageOrderPickV5']['p_true'],
                'pick_speed' => ($pkgInfo['DataErpCodsPackageOrderPickV5']['pick_speed'] == -1) ? '' : $pkgInfo['DataErpCodsPackageOrderPickV5']['pick_speed']
            ];
        }
        $listPkg = array_keys($dataReturn);
        $infoPkg = $this->ErpPackageArchive->getPkgInfoByListPkgOrder($listPkg);
        foreach ($infoPkg as $pkgInfo) {
            $pkgOrder = $pkgInfo['ErpPackageArchive']['order'];
            if (isset($dataReturn[$pkgOrder])) $dataReturn[$pkgOrder]['alias'] = $pkgInfo['ErpPackageArchive']['alias'];
        }
        return [
            'data' => $dataReturn,
            'total_row' => $totalData
        ];
    }

    public function showReturnReward ($codOrder, $date, $workShift, $page, $limit) {
        $salaryMonth = $this->TimeSheets->getSalaryMonthByDate($date);
        $totalData = $this->DataErpCodsPackageOrderReturnV5->countTotalData($salaryMonth['from'], $codOrder, $date, $workShift, null);
        $data = $this->DataErpCodsPackageOrderReturnV5->geInfoReturnPackage($salaryMonth['from'], $codOrder, $date, $workShift, $page, $limit);
        if (empty($totalData) || empty($data)) return null;
        $dataReturn = [];
        foreach ($data as $pkgInfo) {
            $pkgOrder = $pkgInfo['DataErpCodsPackageOrderReturnV5']['pkg_order'];
            $dataReturn[$pkgOrder] = [
                'package_true' => $pkgInfo['DataErpCodsPackageOrderReturnV5']['r_true'],
            ];
        }
        $listPkg = array_keys($dataReturn);
        $infoPkg = $this->ErpPackageArchive->getPkgInfoByListPkgOrder($listPkg);
        foreach ($infoPkg as $pkgInfo) {
            $pkgOrder = $pkgInfo['ErpPackageArchive']['order'];
            if (isset($dataReturn[$pkgOrder])) $dataReturn[$pkgOrder]['alias'] = $pkgInfo['ErpPackageArchive']['alias'];
        }
        return [
            'data' => $dataReturn,
            'total_row' => (int)$totalData[0]['numRow']
        ];
    }

    //todo: Hàm chống gian lận thưởng tốc độ, đi làm > 1 ca nhưng chốt 1 ca.
    public function validateCheat ($codOrder, $dataDateKey, $date) {
        $data = $this->BigDataDbStationProcessCod->validateCheat($codOrder, $dataDateKey, $date);
        $dataCheck = [];
        foreach ($data as $detailData) {
            $workShift = $detailData['BigDataDbStationProcessCod']['workshift'];
            if (!isset($dataCheck[$workShift])) {
                $dataCheck[$workShift] = [
                    'd_total' => $detailData['BigDataDbStationProcessCod']['d_total'],
                    'd_is_active' => $detailData['BigDataDbStationProcessCod']['d_is_active']
                ];
            } else {
                $dataCheck[$workShift]['d_total'] += $detailData['BigDataDbStationProcessCod']['d_total'];
                $dataCheck[$workShift]['d_is_active'] += $detailData['BigDataDbStationProcessCod']['d_is_active'];
            }
            // Nếu vùng từ 5 trở lên thì sẽ cộng thêm d_total
            if ($detailData['BigDataDbStationProcessCod']['area'] > 4) $dataCheck[$workShift]['d_is_active'] += $detailData['BigDataDbStationProcessCod']['d_total'];
        }
        if (empty($data)) return null;
        $countValidDeliver = 0; // Ca có d_total > 0 coi là hợp lệ => cộng 1
        $countValidActive = 0; // Ca có d_is_active > 0 coi là hợp lệ => cộng 1
        foreach ($dataCheck as $dataInWorkShift) {
            if ($dataInWorkShift['d_total'] > 0) $countValidDeliver++;
            if ($dataInWorkShift['d_is_active'] > 0) $countValidActive++;
        }
        if ($countValidDeliver == 1 && $countValidActive > 1) return true;
        return false;
    }

    //todo:  Hàm lấy thông tin chi data chi tiết của cod theo ngày
    public function getDetailDataByDate ($codAlias, $date, $workShift) {
        $cod = $this->ErpCod->getInfoCodByCodAlias($codAlias);
        $salaryMonth = $this->TimeSheets->getSalaryMonthByDate($date);
        $data = [];

        $dataDeliver = $this->DataErpCodsPackageOrderDeliverV5->getAllDataByDate($salaryMonth['from'], $cod['ErpCod']['order'], $date, $workShift);
        $this->addExternalData($data, $dataDeliver, 'deliver');

        $dataPick = $this->DataErpCodsPackageOrderPickV5->getAllDataByDate($salaryMonth['from'], $cod['ErpCod']['order'], $date, $workShift);
        $this->addExternalData($data, $dataPick, 'pickup');

        $dataReturn = $this->DataErpCodsPackageOrderReturnV5->getAllDataByDate($salaryMonth['from'], $cod['ErpCod']['order'], $date, $workShift);
        $this->addExternalData($data, $dataReturn, 'return');

        return $data;

    }
    public function addExternalData (&$data, $dataForCal, $type) {
        if (empty($dataForCal)) return null;
        $listInFoPkg = Hash::extract($dataForCal, '{n}.{*}');
        $listPkgOrder = [];
        foreach ($listInFoPkg as $pkgInfo) {
            $data[$type][$pkgInfo['pkg_order']] = [
                'area' => $pkgInfo['area'],
                'workshift'  => $pkgInfo['workshift'],
                'pkg_order'  => $pkgInfo['pkg_order'],
                'weight'     => $pkgInfo['weight'] ?? null,
                'd_speed'    => $pkgInfo['d_speed'] ?? null,
                'd_success'  => $pkgInfo['d_success'] ?? null,
                'pick_speed' => $pkgInfo['pick_speed'] ?? null,
                'p_success'  => $pkgInfo['p_success'] ?? null,
                'p_true'     => $pkgInfo['p_true'] ?? null,
                'r_success'  => $pkgInfo['r_success'] ?? null,
                'r_true'     => $pkgInfo['r_true'] ?? null,
                'cod_bao'    => $pkgInfo['cod_bao'],
                'chot_ca'    => $pkgInfo['chot_ca'],
                'address'    => 'Đang cập nhật',
                'dang_don'   => $pkgInfo['dang_don'] ?? null,
                'f_hen_lay'  => $pkgInfo['ngay_hen_lay_dau_tien'] ?? null,
                'ca_hen_lay' => $pkgInfo['ca_hen_lay'] ?? null,
                'xuat_giao'  => $pkgInfo['xuat_giao'] ?? null,
                'xuat_tra'   => $pkgInfo['xuat_tra'] ?? null
            ];
            $listPkgOrder[] = $pkgInfo['pkg_order'];
        }

        $listAddressPkg = $this->ErpPackageAddress->getAddressOfListPkg($type, $listPkgOrder);
        foreach ($listAddressPkg as $pkgAddress) {
            $address = $pkgAddress['ErpPackageAddress']['ward'] . ' - ' . $pkgAddress['ErpPackageAddress']['district'] . ' - ' . $pkgAddress['ErpPackageAddress']['province'];
            if (isset($data[$type][$pkgAddress['ErpPackageAddress']['package_order']]))
                $data[$type][$pkgAddress['ErpPackageAddress']['package_order']]['address'] = $address;
        }
    }

    /*---------------------------------------Tối ưu thu nhập----------------------------------------------------------*/
    public function calDataOptimizeSalary ($codId, $salaryMonth, $date) {
        $dataSalary = $this->showSalaryByMonth($codId, $salaryMonth['month'], $salaryMonth['year'], 1000);

        if (empty($dataSalary)) return true;
        if (empty($dataSalary['contract']['user_id'])) return true;

        $listWorkDate       = $this->Calendar->getListDateWithoutSundayAndHoliday($salaryMonth['from'], $date);
        $workDate           = 0; // Thực công
        $requireWorkDate    = 0; // Công chuẩn

        // Tính thực công và công chuẩn đến ngày đang check
        array_walk($dataSalary['list_off_sessions'], function ($session, $dateSession) use (&$workDate, &$requireWorkDate, $date, $listWorkDate) {
            if (strtotime($date) >= strtotime($dateSession)) {
                if ($session > 0) $workDate++;
                if (in_array($dateSession, $listWorkDate)) $requireWorkDate++;
            }
        });

        $dataSave   = [
            'data_date_key' => $salaryMonth['from'],
            'formula'       => 1000,
            'user_id'       => $dataSalary['contract']['user_id'],
            'date'          => $date,
            'indicator'     => json_encode(['work_date' => $workDate, 'require_work_date' => $requireWorkDate]),
            'salary'        => $dataSalary['total_salary']
        ];

        try {
            $this->ErpOptimizeSalary = getInstance('ErpOptimizeSalary');
            $this->ErpOptimizeSalary->deleteAll(['data_date_key' => $salaryMonth['from'], 'user_id' => $dataSalary['contract']['user_id'], 'date' => $date]);
            $this->ErpOptimizeSalary->clear();
            $result = $this->ErpOptimizeSalary->save($dataSave);
            if ($result) return true;
        } catch (Exception $exception) {
            $this->log($exception->getMessage());
        }
        return false;
    }

    /*---------------------------------------Tính chi phí-------------------------------------------------------------*/
    //todo: Hàm tính chi phí đơn theo ngày của từng cod (Chỉ bao gồm các loại lương theo ngày)
    private $WORK_ON_NIGHT_COST = 0;
    private $DATA_SAVE_COST     = [];   // Mảng lưu thông tin chi phí của từng package
    private $listPackageWeight  = [];
    private $COD_ID             = null; // Id Cod
    private $COD_ORDER          = null;
    private $SALARY_MONTH       = null;

    // Phục vụ check
    public static $check = [
        // Chi phí ngày
        'speed'         => 0, // Tốc độ giao
        'deliver'       => 0, // Chất lượng giao
        'pick'          => 0, // Chất lượng lấy
        'return'        => 0, // Chất lượng trả
        'work_shift_3'  => 0, // Làm ca 3
        'kpi'           => 0, // Năng suất giao lấy trả

        // Chi phí tháng
        'place'         => 0, // Thù lao điểm
        'over_time'     => 0, // Tăng ca
        'weight'        => 0, // Cân nặng
    ];
    // Hàm check kết quả chạy chi phí cod, cho biết chênh lệch cụ thể ở đâu
    public function getResultCost () {
        return self::$check;
    }

    //todo: Chi phí ngày cod tỉnh (Chi phí chất lượng giao lấy trả, chi phí tốc độ giao, chi phí làm tối
    public function calCostPackageByDate ($date, $codId) {
        $this->DATA_SAVE_COST   = [];
        $this->COD_ID           = $codId;
        $this->SALARY_MONTH     = $this->TimeSheets->getSalaryMonthByDate($date);
        $infoCod                = $this->ErpCod->getInfoCod($this->COD_ID);
        $this->COD_ORDER        = $infoCod['cod_order'];
        $dataSalaryByDate       = $this->ErpSgwCodSalaryKpi->getSalaryCodByDate(1000, $date, $this->COD_ID);

        if (empty($dataSalaryByDate)) return true;

        $salaryWorkShift[]  = !empty($dataSalaryByDate['ErpSgwCodSalaryKpi']['ws_1']) ? json_decode($dataSalaryByDate['ErpSgwCodSalaryKpi']['ws_1'], true) : null;
        $salaryWorkShift[]  = !empty($dataSalaryByDate['ErpSgwCodSalaryKpi']['ws_2']) ? json_decode($dataSalaryByDate['ErpSgwCodSalaryKpi']['ws_2'], true) : null;
        $salaryWorkShift[]  = !empty($dataSalaryByDate['ErpSgwCodSalaryKpi']['ws_3']) ? json_decode($dataSalaryByDate['ErpSgwCodSalaryKpi']['ws_3'], true) : null;
        $salaryOverAll      = !empty($dataSalaryByDate['ErpSgwCodSalaryKpi']['fix'])  ? json_decode($dataSalaryByDate['ErpSgwCodSalaryKpi']['fix'], true)  : null;
        $salaryWorkShift    = array_filter($salaryWorkShift);

        // lương thưởng theo ngày trong mỗi ca
        foreach ($salaryWorkShift as $index => $salaryInWorkShift) {
            $workShift = $index + 1;
            if (empty($salaryInWorkShift)) continue;

            // Tổng hợp thông tin theo ngày, ca
            $dataDetailInDate         = $this->BigDataDbStationProcessCod->getDataForCalSalary($this->SALARY_MONTH['from'], $date, $this->COD_ORDER, $workShift);

            // Tổng hợp chi phí đơn 500đ theo số lượng đơn của ca
            $dataDetailInDate['p_add_salary'] = $dataDetailInDate[0][0]['p_pkg_bonus'] * ErpSalaryTable::$F1000_SALARY_OVER_PICK_RETURN_DELIVER;
            $dataDetailInDate['r_add_salary'] = $dataDetailInDate[0][0]['r_pkg_bonus'] * ErpSalaryTable::$F1000_SALARY_OVER_PICK_RETURN_DELIVER;

            // Chi phí thưởng chất lượng giao và tốc độ
            $dataRewardDeliverSalary  = $this->DataErpCodsPackageOrderDeliverV5->getListPkgByDate($this->SALARY_MONTH['from'], $this->COD_ORDER, $date, $workShift, 1, 10000);
            // Chi phí thưởng chất lượng lấy
            $dataRewardPickSalary     = $this->DataErpCodsPackageOrderPickV5->getListPkgByDate($this->SALARY_MONTH['from'], $this->COD_ORDER, $date, $workShift, 1, 10000);
            // Chi phí thưởng chất lượng trả
            $dataRewardReturnSalary   = $this->DataErpCodsPackageOrderReturnV5->getListPkgByDate($this->SALARY_MONTH['from'], $this->COD_ORDER, $date, $workShift, 1, 10000);
            // Chi phí cod làm tối
            $this->WORK_ON_NIGHT_COST = ($workShift == 3) ? $salaryOverAll['bonus_work_on_night'] ?? 0 : 0;

            $totalAllPkg = count($dataRewardDeliverSalary) + count($dataRewardPickSalary) + count($dataRewardReturnSalary);
            if (!empty($this->WORK_ON_NIGHT_COST)) $this->WORK_ON_NIGHT_COST = $this->WORK_ON_NIGHT_COST / $totalAllPkg;

            $listPkgDeliver = array_keys($dataRewardDeliverSalary);
            $listPkgPick    = array_keys($dataRewardPickSalary);
            $listPkgReturn  = array_keys($dataRewardReturnSalary);

            $this->listPackageWeight = array_merge($listPkgDeliver, $listPkgPick, $listPkgReturn);
            $this->listPackageWeight = $this->ErpPackageArchive->getWeightFromListPackages($this->listPackageWeight);

            $costSpeed   = $salaryInWorkShift['kpi_reward_cod_province']['d_reward_speed_salary'];
            $costDeliver = $salaryInWorkShift['kpi_reward_cod_province']['d_reward_quality_salary'];
            $costPick    = $salaryInWorkShift['kpi_reward_cod_province']['p_reward_quality_salary'];
            $costReturn  = $salaryInWorkShift['kpi_reward_cod_province']['r_reward_quality_salary'];

            $this->calCostDeliverSpeed($dataRewardDeliverSalary, $workShift, $date, $costSpeed);
            $this->calCostDeliverQuantity($dataRewardDeliverSalary, $workShift, $date, $costDeliver);
            $this->calEachTypeRewardSalary('pick', $dataRewardPickSalary, $workShift, $date, $costPick, $dataDetailInDate);
            $this->calEachTypeRewardSalary('return', $dataRewardReturnSalary, $workShift, $date, $costReturn, $dataDetailInDate);
        }
        if (empty($this->DATA_SAVE_COST)) return true;
        try {
            if (strtotime($date) > strtotime('2020-06-20')) {

                $allPkgCost      = $this->ErpCodCost->getAllPkgCostByDate($this->COD_ID, $this->SALARY_MONTH['from'], $date, 1000);

                foreach ($allPkgCost as $packageCost) {
                    $id          = $packageCost['ErpCodCost']['id'];
                    $index       = $packageCost['ErpCodCost']['pkg_order'] . $packageCost['ErpCodCost']['type'] . $packageCost['ErpCodCost']['workshift'];
                    $costNow     = $this->DATA_SAVE_COST[$index]['cost_by_date']    ?? 0;
                    $costBefore  = $packageCost['ErpCodCost']['cost_by_date']       ?? 0;

                    if (abs($costBefore - $costNow) < 1 && !empty($this->DATA_SAVE_COST[$index])) {
                        unset($this->DATA_SAVE_COST[$index]);
                        continue;
                    }

                    // Update package thay đổi
                    $this->DATA_SAVE_COST[$index]['id'] = $id;
                }

                if (empty($this->DATA_SAVE_COST)) return true;

                $externalData = [
                    'formula'       => 1000,
                    'data_date_key' => $this->SALARY_MONTH['from']
                ];
                $this->DATA_SAVE_COST = array_map(function ($costRow) use ($externalData) {
                    return array_merge($costRow, $externalData);
                }, $this->DATA_SAVE_COST);

                // Logic lưu khi có hơn 500 records để giảm tải cho db
                if (count($this->DATA_SAVE_COST) > 500) {
                    $finalResult = true;
                    while (!empty($this->DATA_SAVE_COST)) {
                        $tmpArray = array_splice($this->DATA_SAVE_COST, 0, 500);
                        $this->ErpCodCost->clear();
                        $result = $this->ErpCodCost->saveMany($tmpArray);
                        if (empty($result)) $finalResult = false;
                    }
                    return $finalResult;
                }

                $this->ErpCodCost->clear();
                if ($this->ErpCodCost->saveMany($this->DATA_SAVE_COST)) return true;
                $this->log(__FILE__ . " " . __FUNCTION__ . " " . __LINE__);
            } else {
                $this->ErpSgwCodWorkShiftCostPackage->deleteAll(['cod_id' => $this->COD_ID, 'date' => $date]);
                if ($this->ErpSgwCodWorkShiftCostPackage->saveMany($this->DATA_SAVE_COST)) return true;
                $this->log(__FILE__ . " " . __FUNCTION__ . " " . __LINE__);
            }
        } catch (Exception $exception) {
            $this->log(__FILE__ . " " . __FUNCTION__ . " " . __LINE__ . " " . $exception->getMessage());
        }
        return false;
    }

    //todo: Hàm tính toán chi phí tốc độ giao
    public function calCostDeliverSpeed ($listPackage, $workShift, $date, $salaryReward) {
        $totalPkg = count($listPackage);

        if (empty($totalPkg) || empty($salaryReward)) return true;

        // Chi phí trên mỗi package
        $salaryPerPkg = $salaryReward / $totalPkg;

        foreach ($listPackage as $pkgOrder => $area) {
            // Chi phí năng suất giao lấy trả
            $tmpIndex       = $pkgOrder . 'deliver' . $workShift;

//            self::$check['speed']        += $salaryPerPkg;
//            self::$check['work_shift_3'] += $this->WORK_ON_NIGHT_COST;

            if (isset($this->DATA_SAVE_COST[$tmpIndex])) {
                $this->DATA_SAVE_COST[$tmpIndex]['cost_by_date'] += $salaryPerPkg;
                continue;
            }

            $this->DATA_SAVE_COST[$tmpIndex] = [
                'pkg_order'     => $pkgOrder,
                'cod_id'        => $this->COD_ID,
                'date'          => $date,
                'workshift'     => $workShift,
                'type'          => 'deliver',
                'cost_by_date'  => $salaryPerPkg,
                'cost_by_month' => 0,
                'area'          => $area,
                'weight'        => $this->listPackageWeight[$pkgOrder] ?? 0
            ];
        }
    }

    // todo: Hàm tính toán chi phí thưởng chất lượng giao và năng suất giao
    public function calCostDeliverQuantity ($listPackage, $workShift, $date, $salaryReward) {
        $totalPkg = count($listPackage);

        if (empty($totalPkg)) return true;
        // Chi phí trên mỗi package
        $salaryPerPkg = !empty($salaryReward) ? $salaryReward / $totalPkg : 0;

        foreach ($listPackage as $pkgOrder => $area) {
            // Chi phí năng suất
            $costKpiByArea  = getValueMapToTable($area, ErpSalaryTable::$F1000_SALARY_BY_DELIVER);
            $salaryPerDate  = $salaryPerPkg + $costKpiByArea + $this->WORK_ON_NIGHT_COST;
            $tmpIndex       = $pkgOrder . 'deliver' . $workShift;

//            self::$check['deliver']      += $salaryPerPkg;
//            self::$check['work_shift_3'] += $this->WORK_ON_NIGHT_COST;
//            self::$check['kpi']          += $costKpiByArea;

            if (isset($this->DATA_SAVE_COST[$tmpIndex])) {
                $this->DATA_SAVE_COST[$tmpIndex]['cost_by_date'] += $salaryPerDate;
                continue;
            }

            $this->DATA_SAVE_COST[$tmpIndex] = [
                'pkg_order'     => $pkgOrder,
                'cod_id'        => $this->COD_ID,
                'date'          => $date,
                'workshift'     => $workShift,
                'type'          => 'deliver',
                'cost_by_date'  => $salaryPerDate,
                'cost_by_month' => 0,
                'area'          => $area,
                'weight'        => $this->listPackageWeight[$pkgOrder] ?? 0
            ];
        }
    }

    //todo: Hàm tính toán chi phí chất lương lấy trả và năng suất lấy trả
    public function calEachTypeRewardSalary ($type, $dataReward, $workShift, $date, $salaryReward, $dataDetailInDate) {
        $totalPkg = count($dataReward);

        if (empty($totalPkg)) return true;

        $dataSalaryPick     = [];
        $dataSalaryReturn   = [];

        foreach ($dataDetailInDate as $dataArea) {
            $area = $dataArea['BigDataDbStationProcessCod']['area'];
            if (empty($area)) $area = 1;

            // thù lao lấy
            if (!isset($dataSalaryPick[$area])) {
                $dataSalaryPick[$area] = $dataArea[0]['p_success'] - $dataArea[0]['p_pkg_bonus'] - $dataArea[0]['p_truck'];
            } else {
                $dataSalaryPick[$area] += $dataArea[0]['p_success'] - $dataArea[0]['p_pkg_bonus'] - $dataArea[0]['p_truck'];
            }

            // thù lao trả
            if (!isset($dataSalaryReturn[$area])) {
                $dataSalaryReturn[$area] = $dataArea[0]['r_success'] - $dataArea[0]['r_pkg_bonus'];
            } else {
                $dataSalaryReturn[$area] += $dataArea[0]['r_success'] - $dataArea[0]['r_pkg_bonus'];
            }
        }

        $costPerPkg         = 0;
        $isCalAverAge       = false; // Nếu có đơn 500đ thì tính theo logic trung bình, ngược lại tính theo vùng đơn
        $pickSalary         = $this->salaryPick($dataSalaryPick);
        $returnSalary       = $this->salaryReturn($dataSalaryReturn);
        $pickBonusSalary    = $dataDetailInDate['p_add_salary']; // đơn lấy 500đ
        $returnBonusSalary  = $dataDetailInDate['r_add_salary']; // đơn trả 500đ

        // Chi phí chất lượng mỗi package
        $salaryPerPkg = !empty($salaryReward) ? $salaryReward / $totalPkg : 0;

        if ($type == 'pick' && !empty($pickBonusSalary)) {
            $isCalAverAge = true;
            $costPerPkg   = ($pickSalary + $pickBonusSalary) / $totalPkg;
        }

        if ($type == 'return' && !empty($returnBonusSalary)) {
            $isCalAverAge = true;
            $costPerPkg   = ($returnSalary + $returnBonusSalary) / $totalPkg;
        }

        foreach ($dataReward as $pkgOrder => $area) {
            if (empty($isCalAverAge)) $costPerPkg = getValueMapToTable($area, ErpSalaryTable::$F1000_SALARY_BY_PICK_RETURN);
            // Chi phí năng suất giao lấy trả
            $salaryPerDate  = $salaryPerPkg + $costPerPkg + $this->WORK_ON_NIGHT_COST;
            $tmpIndex       = $pkgOrder . $type . $workShift;

//            if ($type == 'pick') self::$check['pick']     += $salaryPerPkg;

//            if ($type == 'return') self::$check['return']     += $salaryPerPkg;

//            self::$check['work_shift_3'] += $this->WORK_ON_NIGHT_COST;
//            self::$check['kpi']          += $costPerPkg;

            if (isset($this->DATA_SAVE_COST[$tmpIndex])) {
                $this->DATA_SAVE_COST[$tmpIndex]['cost_by_date'] += $salaryPerDate;
                continue;
            }

            $this->DATA_SAVE_COST[$tmpIndex] = [
                'pkg_order'     => $pkgOrder,
                'cod_id'        => $this->COD_ID,
                'date'          => $date,
                'workshift'     => $workShift,
                'type'          => $type,
                'cost_by_date'  => $salaryPerDate,
                'cost_by_month' => 0,
                'area'          => $area,
                'weight'        => $this->listPackageWeight[$pkgOrder] ?? 0
            ];
        }
    }

    //todo: Hàm group đơn hàng theo điểm
    public function groupPlacePkg ($type, $workShift, $date, $dataReward) {
        $listInfoPkg = $this->ErpPackageAddress->getAddressOfListPkg($type, array_keys($dataReward));
        $groupPlace = [];
        $group = 0;
        foreach ($listInfoPkg as $pkgAddress) {
            // Index của mảng lưu chi phí
            $tmpIndexCost = $pkgAddress['ErpPackageAddress']['package_order'] . $type . $workShift;
            // Index của mảng group theo điểm
            $tmpIndexPlace = $pkgAddress['ErpPackageAddress']['province_id'] . '_' . $pkgAddress['ErpPackageAddress']['district_id'] . '_' . $pkgAddress['ErpPackageAddress']['ward_id'];
            if (!isset($groupPlace[$tmpIndexPlace])) {
                $groupPlace[$tmpIndexPlace] = [
                    'cod_id' => $this->COD_ID,
                    'date' => $date,
                    'workshift' => $workShift,
                    'type' => $type,
                    'group_place' => ++$group,
                    'province_id' => $pkgAddress['ErpPackageAddress']['province_id'],
                    'district_id' => empty($pkgAddress['ErpPackageAddress']['district_id']) ? 0 : $pkgAddress['ErpPackageAddress']['district_id'],
                    'ward_id' => empty($pkgAddress['ErpPackageAddress']['ward_id']) ? 0 : $pkgAddress['ErpPackageAddress']['ward_id'],
                    'tel' => $pkgAddress['ErpPackageAddress']['tel'],
                    'street_id' => empty($pkgAddress['ErpPackageAddress']['street_id']) ? 0 : $pkgAddress['ErpPackageAddress']['street_id'],
                    'first_address' => $pkgAddress['ErpPackageAddress']['first_address']
                ];
            }
            $this->DATA_SAVE_COST[$tmpIndexCost]['place'] = $groupPlace[$tmpIndexPlace]['group_place'];
        }
        try {
            $this->ErpSgwCodWorkShiftPlace->deleteAll(['cod_id' => $this->COD_ID, 'date' => $date, 'type' => $type, 'workshift' => $workShift]);
            if (!$this->ErpSgwCodWorkShiftPlace->saveMany($groupPlace)) $this->log('ErpSgwCodWorkShiftPlace: Error while insert data');
        } catch (Exception $exception) {
            $this->log('ErpSgwCodWorkShiftPlace: Error while insert data ' . $exception->getMessage());
        }
    }

    //todo: Hàm tính chi phí của 1 cod theo tháng
    public function calCostPackageByMonth ($month, $year, $codId) {
        $this->COD_ID = $codId;
        $dataSalaryInMonth = $this->showSalaryByMonth($codId, $month, $year, 1000);
        $dataUpdate = [];
        // Chi phí thù lao điểm
        $costPl = [];
        $costPl['total_pl'] = $dataSalaryInMonth['salary_cod_province']['d_pkg_success'] + $dataSalaryInMonth['salary_cod_province']['p_pl_success'] + $dataSalaryInMonth['salary_cod_province']['r_pl_success'];
        $costPl['cost_per_pkg'] = $dataSalaryInMonth['salary_cod_province']['pl_salary'] / max(1, $costPl['total_pl']);
        $costPl['deliver'] = $costPl['cost_per_pkg'] *  $dataSalaryInMonth['salary_cod_province']['d_pkg_success'];
        $costPl['pick'] = $costPl['cost_per_pkg'] * $dataSalaryInMonth['salary_cod_province']['p_pl_success'];
        $costPl['return']  = $costPl['cost_per_pkg'] * $dataSalaryInMonth['salary_cod_province']['r_pl_success'];

        $costCheck = [];

        foreach ($costPl as $typeCostPl => $costPlByType) {
            if (empty($costPlByType) || !in_array($typeCostPl, ['deliver', 'pick', 'return'])) continue;
            if ($year >= 2021 || ($year >= 2020 && $month >= 7))
                $listCosts = $this->ErpCodCost->getAllPkgCostOfCostByMonth($codId, $dataSalaryInMonth['salary_month']['start_date'], $dataSalaryInMonth['salary_month']['end_date'], 1000, $typeCostPl);
            else
                $listCosts = $this->ErpSgwCodWorkShiftCostPackage->getAllPkgCostOfCostByMonth($codId, $dataSalaryInMonth['salary_month']['start_date'], $dataSalaryInMonth['salary_month']['end_date'], $typeCostPl);
            if (empty($listCosts)) continue;

            $totalPlace = count($listCosts);
            $costPlPerPkg = $costPlByType / $totalPlace;
            foreach ($listCosts as $pkgCostPl) {
                // to make sure that not affect to old logic
                if (!isset($pkgCostPl['ErpSgwCodWorkShiftCostPackage'])) $pkgCostPl['ErpSgwCodWorkShiftCostPackage'] = $pkgCostPl['ErpCodCost'];

                $tmpIndexCost = $pkgCostPl['ErpSgwCodWorkShiftCostPackage']['workshift'] . '_' . $pkgCostPl['ErpSgwCodWorkShiftCostPackage']['pkg_order'] . '_' . $typeCostPl;
                $costCheck[$tmpIndexCost] = $pkgCostPl['ErpSgwCodWorkShiftCostPackage']['cost_by_month'];
                if ($pkgCostPl['ErpSgwCodWorkShiftCostPackage']['cost_by_month'] > 0) $pkgCostPl['ErpSgwCodWorkShiftCostPackage']['cost_by_month'] = 0;

//                self::$check['place'] += $costPlPerPkg;

                if (!isset($dataUpdate[$tmpIndexCost])){
                    $dataUpdate[$tmpIndexCost] = [
                        'id' => $pkgCostPl['ErpSgwCodWorkShiftCostPackage']['id'],
                        'cost_by_date' => $pkgCostPl['ErpSgwCodWorkShiftCostPackage']['cost_by_date'],
                        'cost_by_month' => $pkgCostPl['ErpSgwCodWorkShiftCostPackage']['cost_by_month'] + $costPlPerPkg,
                        'modified' => date('Y-m-d H:i:s')
                    ];
                    continue;
                }
                $dataUpdate[$tmpIndexCost]['cost_by_month'] += $costPlPerPkg;
                $dataUpdate[$tmpIndexCost]['modified'] = date('Y-m-d H:i:s');
            }
        }

        // Chi phí tăng ca chủ nhật và ngày lễ
        $costSaveOverTime = array_sum($dataSalaryInMonth['list_over_time_salary'] ?? []);

        // Chi phí quá cân giao
        if (!empty($dataSalaryInMonth['salary_cod_province']['d_weight_salary'])) {
            $costPerOverWeightPkg = $dataSalaryInMonth['salary_cod_province']['d_weight_salary'] / $dataSalaryInMonth['salary_cod_province']['d_pkg_weight'];
            $listPkgDeliverOverWeight = $this->DataErpCodsPackageOrderDeliverV5->getListOverWeight($dataSalaryInMonth['salary_month']['start_date'], $this->COD_ORDER, array_keys($dataSalaryInMonth['list_off_sessions']));
            if (!empty($listPkgDeliverOverWeight)) $this->calCostOverWeight($dataUpdate, $listPkgDeliverOverWeight, $costPerOverWeightPkg, 'deliver');
        }

        // Chi phí quá cân lấy trả
        if (!empty($dataSalaryInMonth['salary_cod_province']['p_r_weight_salary'])) {
            $costPerOverWeightPkg = $dataSalaryInMonth['salary_cod_province']['p_r_weight_salary'] / $dataSalaryInMonth['salary_cod_province']['p_r_pkg_weight'];
            $listPkgPickOverWeight = $this->DataErpCodsPackageOrderPickV5->getListOverWeight($dataSalaryInMonth['salary_month']['start_date'], $this->COD_ORDER, array_keys($dataSalaryInMonth['list_off_sessions']));
            $listPkgReturnOverWeight = $this->DataErpCodsPackageOrderReturnV5->getListOverWeight($dataSalaryInMonth['salary_month']['start_date'], $this->COD_ORDER, array_keys($dataSalaryInMonth['list_off_sessions']));
            if (!empty($listPkgPickOverWeight)) $this->calCostOverWeight($dataUpdate, $listPkgPickOverWeight, $costPerOverWeightPkg, 'pick');
            if (!empty($listPkgReturnOverWeight)) $this->calCostOverWeight($dataUpdate, $listPkgReturnOverWeight, $costPerOverWeightPkg, 'return');
        }

        if (empty($dataUpdate)) return true;
        $totalAllTypePkgCost = count($dataUpdate);

        $costSaveOverTimePerPkg = 0; // Chi phí tăng ca không phát sinh sản lượng nhưng vẫn được tiền
        $costHolidayPerPkg      = 0; // Chi phí lễ
        $costLeavePerPkg        = 0; // Chi phí phép
        $costCouponPerPkg       = 0; // Chi phí coupon
        $costWeekReward         = 0; // Chi phi thuong tuan

        if (!empty($costSaveOverTime)) $costSaveOverTimePerPkg = $costSaveOverTime / $totalAllTypePkgCost;
        if (!empty($dataSalaryInMonth['salary_cod_province']['holiday_salary'])) $costHolidayPerPkg = $dataSalaryInMonth['salary_cod_province']['holiday_salary'] / $totalAllTypePkgCost;
        if (!empty($dataSalaryInMonth['salary_cod_province']['leave_salary'])) $costLeavePerPkg = $dataSalaryInMonth['salary_cod_province']['leave_salary'] / $totalAllTypePkgCost;
        if (!empty($dataSalaryInMonth['salary_cod_province']['detail_coupon'])) $costCouponPerPkg = array_sum($dataSalaryInMonth['salary_cod_province']['detail_coupon']) / $totalAllTypePkgCost;
        if (!empty($dataSalaryInMonth['salary_cod_province']['total_salary_week_reward'])) $costWeekReward = $dataSalaryInMonth['salary_cod_province']['total_salary_week_reward'] / $totalAllTypePkgCost;

        foreach ($dataUpdate as $key => $value) {
//            self::$check['over_time'] += $costSaveOverTimePerPkg + $costHolidayPerPkg;

            $dataUpdate[$key]['cost_by_month']  += $costSaveOverTimePerPkg + $costHolidayPerPkg + $costLeavePerPkg + $costCouponPerPkg + $costWeekReward;
            $costBefore = $costCheck[$key] ?? 0;
            $costNow    = $dataUpdate[$key]['cost_by_month'];

            if (abs($costBefore - $costNow) < 1) unset($dataUpdate[$key]);
        }

        if (empty($dataUpdate)) return true;

        try {
            if ($year >= 2021 || ($year >= 2020 && $month >= 7)) {
                // Logic lưu khi có hơn 500 records để giảm tải cho db
                if (count($dataUpdate) > 500) {
                    $finalResult = true;
                    while (!empty($dataUpdate)) {
                        $tmpArray = array_splice($dataUpdate, 0, 500);
                        $this->ErpCodCost->clear();
                        $result = $this->ErpCodCost->saveMany($tmpArray);
                        if (empty($result)) $finalResult = false;
                    }
                    return $finalResult;
                }

                $this->ErpCod->clear();
                $result = $this->ErpCodCost->saveMany($dataUpdate);
            } else $result = $this->ErpSgwCodWorkShiftCostPackage->saveMany($dataUpdate);
            if ($result) return true;
            $this->log('Error while update cos');
        } catch (Exception $exception) {
            $this->log('Error while update cost: ' . $exception->getMessage());
        }
        return false;
    }

    public function calCostOverWeight (&$dataUpdate, $listPkgOverWeight, $costPerOverWeightPkg, $type) {
        $tableAlias = 'DataErpCodsPackageOrderPickV5';
        if ($type == 'deliver') $tableAlias = 'DataErpCodsPackageOrderDeliverV5';
        if ($type == 'return') $tableAlias = 'DataErpCodsPackageOrderReturnV5';
        foreach ($listPkgOverWeight as $pkgOverWeight) {
            $tmpIndexCost = $pkgOverWeight[$tableAlias]['workshift'] . '_' . $pkgOverWeight[$tableAlias]['pkg_order'] . '_' . $type;

            if (!isset($dataUpdate[$tmpIndexCost])) continue;

//            self::$check['weight'] += $costPerOverWeightPkg * $pkgOverWeight[$tableAlias]['weight'];

            $dataUpdate[$tmpIndexCost]['cost_by_month'] += $costPerOverWeightPkg * $pkgOverWeight[$tableAlias]['weight'];
            $dataUpdate[$tmpIndexCost]['modified'] = date('Y-m-d H:i:s');
        }
    }

    //todo: Hàm xuất các file excel con tạm
    private $headerToValue = [
        'month' => 'Tháng',
        'year' => 'Năm',
        'region_name' => 'Miền',
        'province_name' => 'Tỉnh',
        'station_name' => 'Kho',
        'cod_alias' => 'Cod alias',
        'full_name' => 'Họ và tên',
        'user_name' => 'Tên tài khoản',
        'user_id' => 'User id',
        'cmt' => 'Số cmt',
        'work_type' => 'Loại hình làm việc',
        'position_job_name' => 'Vị trí cod',
        'total_deliver_success' => 'Tổng đơn giao thành công',
        'total_pick_place_success' => 'Tổng điểm lấy thành công',
        'total_return_place_success' => 'Tổng điểm trả thành công',
        'total_pick_success' => 'Tổng đơn lấy thành công',
        'total_return_success' => 'Tổng đơn trả thành công',
        'total_deliver_success_area_1' => 'Tổng đơn giao thành công vùng 1',
        'total_deliver_success_area_2' => 'Tổng đơn giao thành công vùng 2',
        'total_deliver_success_area_3' => 'Tổng đơn giao thành công vùng 3',
        'total_deliver_success_area_4' => 'Tổng đơn giao thành công vùng 4',
        'total_deliver_success_area_5' => 'Tổng đơn giao thành công vùng 5',
        'total_deliver_success_area_6' => 'Tổng đơn giao thành công vùng 6',
        'total_deliver_success_area_7' => 'Tổng đơn giao thành công vùng 7',
        'total_deliver_success_area_8' => 'Tổng đơn giao thành công vùng 8',
        'total_pick_return_place_success_area_1' => 'Tổng điểm lấy trả thành công vùng 1',
        'total_pick_return_place_success_area_2' => 'Tổng điểm lấy trả thành công vùng 2',
        'total_pick_return_place_success_area_3' => 'Tổng điểm lấy trả thành công vùng 3',
        'total_pick_return_place_success_area_4' => 'Tổng điểm lấy trả thành công vùng 4',
        'total_pick_return_place_success_area_5' => 'Tổng điểm lấy trả thành công vùng 5',
        'total_pick_return_place_success_area_6' => 'Tổng điểm lấy trả thành công vùng 6',
        'total_pick_return_place_success_area_7' => 'Tổng điểm lấy trả thành công vùng 7',
        'total_pick_return_place_success_area_8' => 'Tổng điểm lấy trả thành công vùng 8',
        'total_integration_area_1' => 'Tổng chỉ số hội nhập vùng 1',
        'total_integration_area_2' => 'Tổng chỉ số hội nhập vùng 2',
        'total_integration_area_3' => 'Tổng chỉ số hội nhập vùng 3',
        'total_integration_area_4' => 'Tổng chỉ số hội nhập vùng 4',
        'total_integration_area_5' => 'Tổng chỉ số hội nhập vùng 5',
        'total_integration_area_6' => 'Tổng chỉ số hội nhập vùng 6',
        'total_integration_area_7' => 'Tổng chỉ số hội nhập vùng 7',
        'total_integration_area_8' => 'Tổng chỉ số hội nhập vùng 8',
        'total_pick_success_area_1' => 'Tổng đơn lấy thành công vùng 1',
        'total_pick_success_area_2' => 'Tổng đơn lấy thành công vùng 2',
        'total_pick_success_area_3' => 'Tổng đơn lấy thành công vùng 3',
        'total_pick_success_area_4' => 'Tổng đơn lấy thành công vùng 4',
        'total_pick_success_area_5' => 'Tổng đơn lấy thành công vùng 5',
        'total_pick_success_area_6' => 'Tổng đơn lấy thành công vùng 6',
        'total_pick_success_area_7' => 'Tổng đơn lấy thành công vùng 7',
        'total_pick_success_area_8' => 'Tổng đơn lấy thành công vùng 8',
        'total_pick_truck_area_1' => 'Tổng đơn lấy xe tải vùng 1',
        'total_pick_truck_area_2' => 'Tổng đơn lấy xe tải vùng 2',
        'total_pick_truck_area_3' => 'Tổng đơn lấy xe tải vùng 3',
        'total_pick_truck_area_4' => 'Tổng đơn lấy xe tải vùng 4',
        'total_pick_truck_area_5' => 'Tổng đơn lấy xe tải vùng 5',
        'total_pick_truck_area_6' => 'Tổng đơn lấy xe tải vùng 6',
        'total_pick_truck_area_7' => 'Tổng đơn lấy xe tải vùng 7',
        'total_pick_truck_area_8' => 'Tổng đơn lấy xe tải vùng 8',
        'total_pick_add_area_1' => 'Tổng đơn lấy bổ sung vùng 1',
        'total_pick_add_area_2' => 'Tổng đơn lấy bổ sung vùng 2',
        'total_pick_add_area_3' => 'Tổng đơn lấy bổ sung vùng 3',
        'total_pick_add_area_4' => 'Tổng đơn lấy bổ sung vùng 4',
        'total_pick_add_area_5' => 'Tổng đơn lấy bổ sung vùng 5',
        'total_pick_add_area_6' => 'Tổng đơn lấy bổ sung vùng 6',
        'total_pick_add_area_7' => 'Tổng đơn lấy bổ sung vùng 7',
        'total_pick_add_area_8' => 'Tổng đơn lấy bổ sung vùng 8',
        'total_return_success_area_1' => 'Tổng đơn trả thành công vùng 1',
        'total_return_success_area_2' => 'Tổng đơn trả thành công vùng 2',
        'total_return_success_area_3' => 'Tổng đơn trả thành công vùng 3',
        'total_return_success_area_4' => 'Tổng đơn trả thành công vùng 4',
        'total_return_success_area_5' => 'Tổng đơn trả thành công vùng 5',
        'total_return_success_area_6' => 'Tổng đơn trả thành công vùng 6',
        'total_return_success_area_7' => 'Tổng đơn trả thành công vùng 7',
        'total_return_success_area_8' => 'Tổng đơn trả thành công vùng 8',
        'total_return_add_area_1' => 'Tổng đơn trả bổ sung vùng 1',
        'total_return_add_area_2' => 'Tổng đơn trả bổ sung vùng 2',
        'total_return_add_area_3' => 'Tổng đơn trả bổ sung vùng 3',
        'total_return_add_area_4' => 'Tổng đơn trả bổ sung vùng 4',
        'total_return_add_area_5' => 'Tổng đơn trả bổ sung vùng 5',
        'total_return_add_area_6' => 'Tổng đơn trả bổ sung vùng 6',
        'total_return_add_area_7' => 'Tổng đơn trả bổ sung vùng 7',
        'total_return_add_area_8' => 'Tổng đơn trả bổ sung vùng 8',
        'total_pick_return_weight_area_1' => 'Tổng cân nặng tích lấy trả vùng 1',
        'total_pick_return_weight_area_2' => 'Tổng cân nặng tích lấy trả vùng 2',
        'total_pick_return_weight_area_3' => 'Tổng cân nặng tích lấy trả vùng 3',
        'total_pick_return_weight_area_4' => 'Tổng cân nặng tích lấy trả vùng 4',
        'total_pick_return_weight_area_5' => 'Tổng cân nặng tích lấy trả vùng 5',
        'total_pick_return_weight_area_6' => 'Tổng cân nặng tích lấy trả vùng 6',
        'total_pick_return_weight_area_7' => 'Tổng cân nặng tích lấy trả vùng 7',
        'total_pick_return_weight_area_8' => 'Tổng cân nặng tích lấy trả vùng 8',
        'total_deliver_weight_area_1' => 'Tổng cân nặng tích lũy giao vùng 1',
        'total_deliver_weight_area_2' => 'Tổng cân nặng tích lũy giao vùng 2',
        'total_deliver_weight_area_3' => 'Tổng cân nặng tích lũy giao vùng 3',
        'total_deliver_weight_area_4' => 'Tổng cân nặng tích lũy giao vùng 4',
        'total_deliver_weight_area_5' => 'Tổng cân nặng tích lũy giao vùng 5',
        'total_deliver_weight_area_6' => 'Tổng cân nặng tích lũy giao vùng 6',
        'total_deliver_weight_area_7' => 'Tổng cân nặng tích lũy giao vùng 7',
        'total_deliver_weight_area_8' => 'Tổng cân nặng tích lũy giao vùng 8',
        'fine_clean_package' => 'Tổng đơn phạt sạch phiên',
        'total_pkg_week_reward' => 'Tổng đơn thưởng tuần',
        'deliver_speed_salary' => 'Lương thưởng tốc độ',
        'deliver_quality_salary' => 'Lương thưởng chất lượng giao',
        'pick_quality_salary' => 'Lương thưởng chất lượng lấy',
        'return_quality_salary' => 'Lương thưởng chất lượng trả',
        'work_on_night_salary' => 'Lương phụ cấp tối',
        'over_time_salary' => 'Lương tăng ca',
        'weight_salary' => 'Lương tích lũy cân nặng',
        'place_salary' => 'Lương thù lao điểm',
        'productivity_salary' => 'Lương năng suất',
        'cod_type_salary' => 'Lương phụ cấp cod linh động',
        'total_coupon' => 'Tổng lương coupon',
        'tip_cod_salary' => 'Tiền tip cod',
        'integration_salary' => 'Phụ cấp hội nhập',
        'over_weight_salary' => 'Phạt quá cân',
        'holiday_salary' => 'Lương lễ',
        'leave_salary' => 'Lương phép',
        'holiday_salary_applied' => 'Mức lương nghỉ lễ',
        'leave_salary_applied' => 'Mức lương nghỉ phép',
        'overtime_salary_applied' => 'Mức lương tăng ca',
        'fine_clean_salary' => 'Phạt sạch phiên',
        'total_salary_week_reward' => 'Lương thưởng tuần',
        'leave' => 'Công phép',
        'holiday' => 'Công lễ',
        'night_session' => 'Phiên tối',
        'over_time_session' => 'Phiên tăng ca',
        'general_session' => 'Phiên chuẩn',
        'off_session' => 'Phiên thiếu',
        'on_session' => 'Thực phiên',
        'require_session' => 'Số phiên yêu cầu',
        'holiday_overtime_session' => 'Số phiên tăng ca ngày lễ',
        'normal_overtime_session' => 'Số phiên tăng ca ngày thường',
        'join_date' => 'Ngày vào',//
        'start_date_day' => 'Ngày lương chuẩn',//
        'resign_date' => 'Ngày nghỉ',//
        'active_date' => 'Ngày active',
        'disable_date' => 'Ngày tài khoản ngừng hoạt động',
        'contract_number' => 'Số HĐ',
        'type_contract' => 'Loại HĐ',
        'contract_start_date' => 'Ngày bắt đầu HĐ',
        'contract_end_date' => 'Ngày kết thúc HĐ',
        'is_main_user' => 'User chính',
        'total_salary' => 'Tổng lương'//
    ];

    public function generateTempDirectionToExport($userId){
        $exportedPath = 'files' . DS . 'exported' . DS . 'salary/cod_province' . DS . 'export_tool' . DS . date('Y/m/d') . DS . 'temp_export_' . $userId;
        $folderWebroot = CakePlugin::path('Admin') . WEBROOT_DIR . DS;
        $folderFilePath = $folderWebroot . $exportedPath;
        if (!is_dir($folderFilePath)) {
            mkdir($folderFilePath, 0777, true);
        }
        return [
            'folderFilePath' => $folderFilePath,
            'relativeFilePath' => $exportedPath,
            'realPath' => realpath($folderFilePath)
        ];
    }
    CONST REGION_NAME = [
        10 => 'Miền Bắc',
        20 => 'Miền Trung',
        30 => 'Miền Nam'
    ];
    public function exportTmpFile ($listCod, $listDateNotCal, $director, $month, $year, $fileName, $isExportToExcel = false) {
        $this->EmpPosition = getInstance('EmpPosition');
        if (!empty($listDateNotCal)) $this->LIST_DATE_NOT_CAL = explode(',', str_replace(' ', '', $listDateNotCal));
        $dataExport = [];
        foreach ($listCod as $codId => $formula) {
            $infoCod = $this->ErpCod->getInfoCod($codId);
            $dataInMonth = $this->showSalaryByMonth($codId, $month, $year, 1000);
            $salaryApplied = in_array($dataInMonth['contract']['type_contract'], ErpEmpContract::$danh_sach_hop_dong_lao_dong) ?
                $dataInMonth['contract']['salary'] : '';
            if (empty($dataInMonth)) continue;
            $positionName = $this->EmpPosition->getNameById($infoCod['position_job']);
            $dataRow = [
                'month' => $month,
                'year' => $year,
                'region_name' => self::REGION_NAME[$infoCod['region'] ?? ''] ?? null,
                'province_name' => $infoCod['province_name'],
                'station_name' => $infoCod['station_name'],
                'cod_alias' => $infoCod['alias'],
                'full_name' => $infoCod['fullname'],
                'user_name' => $infoCod['username'],
                'user_id' => $infoCod['user_id'],
                'cmt' => $infoCod['personal_id_number'],
                'work_type' => $infoCod['work_type_description'],
                'position_job_name' => $positionName,
                'is_main_user' => $this->ErpUser->checkMainUser($infoCod['user_id']),
                'night_session' => $dataInMonth['salary_cod_province']['night_session'],
                'over_time_session' => $dataInMonth['salary_cod_province']['overtime_session'],
                'general_session' => $dataInMonth['count_session']['standard'],
                'off_session' => $dataInMonth['count_session']['off_session'],
                'on_session' => $dataInMonth['count_session']['on_session'],
                'holiday_overtime_session' => $dataInMonth['salary_cod_province']['overtime_holiday_session'],
                'normal_overtime_session' => $dataInMonth['salary_cod_province']['overtime_normal_session'],
                'require_session' => $dataInMonth['count_session']['require_session'],
                'join_date' => $dataInMonth['join_date'],//
                'start_date_day' => $dataInMonth['start_date'],//
                'resign_date' => $dataInMonth['resign_date'],//
                'active_date' => $dataInMonth['active_date'],
                'disable_date' => $infoCod['disable_date'] ?? '',
                'contract_number' => $dataInMonth['contract']['contract_number'],
                'type_contract' => $dataInMonth['contract']['type_name'],
                'contract_start_date' => $dataInMonth['contract']['start_date'],
                'contract_end_date' => $dataInMonth['contract']['end_date'],
                'total_deliver_success' => $dataInMonth['salary_cod_province']['d_pkg_success'] ?? 0,
                'total_pick_place_success' => $dataInMonth['salary_cod_province']['p_pl_success'],
                'total_return_place_success' => $dataInMonth['salary_cod_province']['r_pl_success'],
                'total_pick_success' => 0,
                'total_return_success' => 0,
                'deliver_speed_salary' => $dataInMonth['salary_cod_province']['d_reward_speed_salary'],
                'deliver_quality_salary' => $dataInMonth['salary_cod_province']['d_reward_quality_salary'],
                'pick_quality_salary' => $dataInMonth['salary_cod_province']['p_reward_quality_salary'],
                'return_quality_salary' => $dataInMonth['salary_cod_province']['r_reward_quality_salary'],
                'work_on_night_salary' => $dataInMonth['salary_cod_province']['bonus_work_on_night'],
                'over_time_salary' => $dataInMonth['salary_cod_province']['bonus_work_on_sunday'],
                'weight_salary' => $dataInMonth['salary_cod_province']['weight_salary'],
                'place_salary' => $dataInMonth['salary_cod_province']['pl_salary'],
                'productivity_salary' => $dataInMonth['salary_cod_province']['d_p_r_salary'],
                'cod_type_salary' => $dataInMonth['salary_cod_province']['allowance_type_salary'],
                'holiday_salary' => $dataInMonth['salary_cod_province']['holiday_salary'],
                'leave_salary' => $dataInMonth['salary_cod_province']['leave_salary'],
                'holiday_salary_applied' => $salaryApplied,
                'leave_salary_applied' => $salaryApplied,
                'overtime_salary_applied' => $salaryApplied,
                'leave' => $dataInMonth['salary_cod_province']['leave'],
                'holiday' => $dataInMonth['salary_cod_province']['holiday'],
                'total_coupon' => $dataInMonth['salary_cod_province']['total_coupon'],
                'tip_cod_salary' => $dataInMonth['salary_cod_province']['tip_cod_salary'],
                'integration_salary' => $dataInMonth['salary_cod_province']['integration_salary'] ?? 0,
                'total_pkg_week_reward' => $dataInMonth['salary_cod_province']['total_pkg_week_reward'] ?? 0,
                'total_salary_week_reward' => $dataInMonth['salary_cod_province']['total_salary_week_reward'] ?? 0,
                'fine_clean_salary' => $dataInMonth['salary_cod_province']['fine_clean_salary'] ?? 0,
                'fine_clean_package' => $dataInMonth['salary_cod_province']['fine_clean_package'] ?? 0,
                'over_weight_salary' => $dataInMonth['kpi_over_weight']['salary'] ?? 0,
                'total_salary' => $dataInMonth['total_salary']
            ];
            foreach (range(1, ErpSalaryTable::$MAX_AREA) as $area) {
                $dataRow['total_pick_success'] += $dataInMonth['salary_cod_province']['detail_pkg_by_area'][$area]['pick_package_success'] ?? 0;
                $dataRow['total_return_success'] += $dataInMonth['salary_cod_province']['detail_pkg_by_area'][$area]['return_package_success'] ?? 0;
                $dataRow['total_deliver_success_area_' . $area] = $dataInMonth['salary_cod_province']['detail_pkg_by_area'][$area]['deliver_success'] ?? 0;
                $dataRow['total_pick_success_area_' . $area] = $dataInMonth['salary_cod_province']['detail_pkg_by_area'][$area]['pick_package_success'] ?? 0;
                $dataRow['total_pick_truck_area_' . $area] = $dataInMonth['salary_cod_province']['detail_pkg_by_area'][$area]['pick_truck'] ?? 0;
                $dataRow['total_pick_add_area_' . $area] = $dataInMonth['salary_cod_province']['detail_pkg_by_area'][$area]['pick_add'] ?? 0;
                $dataRow['total_return_success_area_' . $area] = $dataInMonth['salary_cod_province']['detail_pkg_by_area'][$area]['return_package_success'] ?? 0;
                $dataRow['total_return_add_area_' . $area] = $dataInMonth['salary_cod_province']['detail_pkg_by_area'][$area]['return_add'] ?? 0;
                $dataRow['total_pick_return_weight_area_' . $area] = $dataInMonth['salary_cod_province']['detail_pkg_by_area'][$area]['pick_return_weight'] ?? 0;
                $dataRow['total_deliver_weight_area_' . $area] = $dataInMonth['salary_cod_province']['detail_pkg_by_area'][$area]['deliver_weight'] ?? 0;
                $dataRow['total_pick_return_place_success_area_' . $area] = ($dataInMonth['salary_cod_province']['detail_pkg_by_area'][$area]['pick_success'] ?? 0)
                    + ($dataInMonth['salary_cod_province']['detail_pkg_by_area'][$area]['return_success'] ?? 0);
                $dataRow['total_integration_area_' . $area] = $dataInMonth['salary_cod_province']['integration_index_area'][$area] ?? 0;
            }
            $dataExport[] = $dataRow;
        }

        if ($isExportToExcel) {
            return $this->ErpTransitSalary->quickExportDataToExcel($this->headerToValue, $dataExport, 'PROVINCE', $fileName, AuthComponent::user('id'));
        }

        $this->ErpExcelExportProcess = getInstance('ErpExcelExportProcess');
        $fileInfo = $this->ErpExcelExportProcess->checkExistFile($fileName);
        if (empty($fileInfo) || empty($fileInfo['ErpExcelExportProcess']['is_processing'])) return true;

        if (!is_dir($director)) return true;
        $fileName = date('YmdHis').random_string();
        $exportedFilePath = $director . DS . $fileName . '.txt';
        $file = new File($exportedFilePath);
        $file->write(json_encode($dataExport), 'w', true);
        $file->close();
        return $exportedFilePath;
    }

    public function mergeAllSubFileCodProvince ($totalRealFiles, $fileName, $isCancel) {
        $exportedPath = 'files' . DS . 'exported' . DS . 'salary/cod_province' . DS . 'export_tool' . DS . date('Y/m/d') . DS . 'temp_export_' . AuthComponent::user('id');
        $folderWebroot = CakePlugin::path('Admin') . WEBROOT_DIR . DS;
        $folderFilePath = $folderWebroot . $exportedPath;

        $dataExport = [];
        $this->ErpExcelExportProcess = getInstance('ErpExcelExportProcess');

        if (is_dir($folderFilePath)) {
            $listFiles = array_diff(scandir($folderFilePath), array('.', '..'));
            if (!empty($listFiles)) $listFiles = array_map(function ($a) use ($folderFilePath) {return $folderFilePath.'/'.$a;} , $listFiles);
        }
        if ($isCancel && !empty($listFiles)) {
            try {
                foreach ($listFiles as $key => $item) {
                    unlink($item);
                    unset($listFiles[$key]);
                }
                rmdir($folderFilePath);
            } catch (Exception $exception) {
                $this->log($exception->getMessage());
            }
        }
        if (empty($listFiles)) {
            if ($isCancel) {
                $this->ErpExcelExportProcess->setProcessingToStop($fileName);
                return [
                    'success' => false,
                    'isContinueRequest' => false,
                    'message' => 'Hủy bỏ tiến trình thành công!'
                ];
            }

            return [
                'success' => false,
                'isContinueRequest' => false,
                'message' => 'Không tìm thấy các subfile để xuất!'
            ];
        }
        $totalFile = count($listFiles);
        if ($totalFile < $totalRealFiles)
            return [
                'success' => false,
                'isContinueRequest' => true,
                'message' => 'Tiến trình đang diễn ra. Vui lòng chờ!',
                'data' => [
                    'total_file' => $totalRealFiles,
                    'total_file_exported' => $totalFile
                ]
            ];

        foreach ($listFiles as $file) {
            $file = new File($file);
            $item = $file->read(true, 'r');
            $dataExport = array_merge($dataExport, json_decode($item, true));
            $file->close();
        }

        if (empty($dataExport))
            return [
                'success' => false,
                'isContinueRequest' => false,
                'message' => 'Không có dữ liệu để xuất',
            ];

        $result = $this->ErpTransitSalary->quickExportDataToExcel($this->headerToValue, $dataExport, 'PROVINCE', $fileName, AuthComponent::user('id'), true);
        if ($result) {
            try {
                foreach ($listFiles as $item) {
                    unlink($item);
                }
                rmdir($folderFilePath);
            } catch (Exception $exception) {
                $this->log($exception->getMessage());
            }
        }
        $fileInfo = $this->ErpExcelExportProcess->checkExistFile($fileName);
        return [
            'success' => true,
            'isContinueRequest' => false,
            'message' => 'Xuất file thành công',
            'data' => $fileInfo['ErpExcelExportProcess']
        ];
    }

    /*-----------------------------------------------CASE DAC BIET----------------------------------------------------*/
    // todo: bỏ phạt hiệu suất tháng 6 năm 2020
    private $LIST_COD_BO_PHAT_HIEU_SUAT_T6_2020 = [
        'T120891', 'T181240', 'T339167', 'T381095', 'T309959', 'T201120', 'T383494', 'T369239', 'T361550', 'T179568',
        'T306332', 'T147591', 'T5262'  , 'T350234', 'T3390'  , 'T375674', 'T366158', 'T366158', 'T387382', 'T253119',
        'T371765', 'T365045', 'T242808', 'T372590', 'T306773', 'T270333', 'T268815', 'T179892', 'T177116', 'T100904',
        'T63136' , 'T98468' , 'T79561' , 'T377597', 'T377024', 'T349256', 'T280145', 'T280133', 'T375869', 'T366860',
        'T34870' , 'T375092', 'T366020', 'T121747', 'T47882' , 'T368111', 'T368096', 'T52189' , 'T204260', 'T305387',
        'T305360', 'T373895', 'T360872', 'T321458', 'T345809', 'T153627', 'T363314', 'T313955', 'T330731', 'T225390',
        'T146986', 'T358622', 't253599', 't275109', 't377069', 'T175296', 'T376934', 'T38202' , 'T312257', 'T352592',
        'T36394' , 'T157758', 'T312236', 'T171207', 'T812412', 'T321230', 'T298916', 'T281798', 'T378467', 'T354050',
        'T345224', 'T366257', 'T327380', 'T363842', 'T358931', 'T350897', 'T9486'  , 'T251781', 'T320009', 'T315002',
        'T377819', 'T267615', 'T378350', 'T267612', 'T374000', 'T271950', 'T377255', 'T335321', 'T383374', 'T375197',
        'T365366', 'T380132', 'T370487', 'T365450', 'T362969', 'T238776', 'T292478', 'T134672', 'T332660', 'T351389',
        'T381377', 'T303500', 'T337835', 'T115011', 'T123547', 'T123527', 'T259698', 'T52921' , 'T308633', 'T341897',
        'T387649', 'T298280', 'T351119', 'T41790' , 'T220754', 'T361535', 'T115223', 'T95182' , 'T337508', 'T310238',
        'T176968', 'T251407', 'T251035', 'T331271', 'T72076' , 'T264057', 'T384621', 'T386578', 'T46518' , 'T280232',
        'T163126', 'T383254', 'T385237', 'T387451', 'T316235', 'T316247', 'T350270', 'T295745', 'T375089', 'T3437'  ,
        'T290147', 'T255309', 'T346670', 'T7176'  , 'T321734', 'T146774', 'T54553' , 'T263190', 'T329582', 'T246968',
        'T309797', 'T363335', 'T357377', 'T329558', 'T263742', 'T112171', 'T321605', 'T289382', 'T366938', 'T346745',
        'T346733', 'T191548', 'T38422' , 'T307604', 'T346754', 'T358088', 'T329693', 'T377657', 'T346922', 'T334385',
        'T292292', 'T292205', 'T292193', 'T292178', 'T210906', 'T68104' , 'T137488'
    ];

    //todo: Bù lương phiên từ ngày 21/5 - 8/6 năm 2020
    private $LIST_COD_BU_PHIEN_21T8_08T6 = [
        'T159406', 'T312182', 'T67436', 'T160494', 'T293969', 'T342152', 'T352133', 'T366551'
    ];

    //todo: Chốt ca 1 lần + bù lương phiên trong kỳ lương tháng 6 năm 2020
    private $LIST_COD_BU_PHIEN_T6_2020 = [
        'T385912', 'T326516', 'T304544', 'T373910', 'T328187', 'T351653', 'T265056', 'T381771', 'T345455', 'T375011',
        'T347708', 'T370166', 'T61842', 'T76101', 'T315056', 'T80285', 'T132120', 'T317795'
    ];

    /*----------------------------------------Hệ thống bỏ phạt cod tỉnh------------------------------------------------*/
    public function exportConfigFine ($fromDate, $toDate, $regionId, $provinceId, $stationId) {
        $dataConfigFine = $this->ErpConfigSalaryCod->getConfigByCondition($fromDate, $toDate, $regionId, $provinceId, $stationId);

        if (empty($dataConfigFine)) return false;

        $dataReturn = [];

        // group data
        $previousGroupId = null;
        $groupConfig     = null;
        foreach ($dataConfigFine as $config) {
            $groupConfig = $config['ErpConfigSalaryCod']['group_id'];


            $listCodApply    = '';
            $listCodNotApply = '';

            if (empty($previousGroupId)) $previousGroupId = $groupConfig;

            $fine = json_decode($config['ErpConfigSalaryCod']['fine'], true);

            if (!empty($fine['alias_apply'])) {
                $listCodApply = implode(', ' , $fine['alias_apply']);
                unset($fine['alias_apply']);
            }

            if (!empty($fine['alias_not_apply'])) {
                $listCodNotApply = implode(', ' , $fine['alias_not_apply']);
                unset($fine['alias_not_apply']);
            }

            $fine = array_map(function ($f) { return $f['name'] . " " . ($f['work_shift'] ?? ''); }, $fine);
            $fine = implode(', ', $fine);


            if (!isset($dataReturn[$groupConfig])) {
                if ($groupConfig != $previousGroupId) {
                    $dataReturn[$previousGroupId]['region_id']      = implode(', ', $dataReturn[$previousGroupId]['region_id']);
                    $dataReturn[$previousGroupId]['province_name']  = implode(', ', $dataReturn[$previousGroupId]['province_name']);
                    $dataReturn[$previousGroupId]['station_name']   = implode(', ', $dataReturn[$previousGroupId]['station_name']);
                    $previousGroupId = $groupConfig;
                }

                $tmp = [
                    'region_id'       => [$config['ErpConfigSalaryCod']['region_id']],
                    'province_name'   => [$config['ErpAddress']['name']],
                    'station_name'    => [$config['ErpStation']['name']],
                    'group_id'        => $config['ErpConfigSalaryCod']['group_id'],
                    'from_date'       => $config['ErpConfigSalaryCod']['from_date'],
                    'to_date'         => $config['ErpConfigSalaryCod']['to_date'],
                    'fine'            => $fine,
                    'alias_apply'     => $listCodApply,
                    'alias_not_apply' => $listCodNotApply,
                    'formula'         => $config['ErpConfigSalaryCod']['formula'],
                    'created'         => $config['ErpConfigSalaryCod']['created'],
                    'created_by'      => $config['ErpConfigSalaryCod']['created_by'],
                    'is_active'       => $config['ErpConfigSalaryCod']['is_active'],
                ];
                $dataReturn[$groupConfig] = $tmp;
                continue;
            }
            $region   = $config['ErpConfigSalaryCod']['region_id'];
            $province = $config['ErpAddress']['name'];
            $station  = $config['ErpStation']['name'];

            if (!empty($region) && !in_array($region, $dataReturn[$groupConfig]['region_id']))
                $dataReturn[$groupConfig]['region_id'][]     = $region;

            if (!empty($province) && !in_array($region, $dataReturn[$groupConfig]['province_name']))
                $dataReturn[$groupConfig]['province_name'][] = $province;

            if (!empty($station) && !in_array($region, $dataReturn[$groupConfig]['station_name']))
                $dataReturn[$groupConfig]['station_name'][]  = $station;
        }
        $dataReturn[$groupConfig]['region_id']      = implode(', ', $dataReturn[$groupConfig]['region_id']);
        $dataReturn[$groupConfig]['province_name']  = implode(', ', $dataReturn[$groupConfig]['province_name']);
        $dataReturn[$groupConfig]['station_name']   = implode(', ', $dataReturn[$groupConfig]['station_name']);

        $headerToValue = [
            'region_id'         => 'Id Miền',
            'province_name'     => 'Tên Tỉnh',
            'station_name'      => 'Tên khi',
            'group_id'          => 'Group ID',
            'from_date'         => 'Áp Dụng Từ Ngày',
            'to_date'           => 'Đến Hết Ngày',
            'fine'              => 'Config Bỏ Phạt',
            'alias_apply'       => 'Danh Sách Cod Được Áp Dụng',
            'alias_not_apply'   => 'Danh Sách Cod Không Được Áp Dụng',
            'formula'           => 'Công Thức Lương',
            'created'           => 'Ngày Tạo',
            'created_by'        => 'Tạo Bởi User Id',
            'is_active'         => 'Trạng Thái Active',
        ];

        return $this->ErpTransitSalary->quickExportDataToExcel($headerToValue, $dataReturn, 'FINE_PROVINCE', NOW . '.xlsx', AuthComponent::user('id'));
    }

    public function createConfigFine ($formula, $region, $province, $station, $from, $to, $aliasApply, $alNotApply, $fine, $desc, $groupId, $workType) {
        $source = $this->ErpConfigSalaryCod->getDataSource(); $source->begin();

        if (!empty($groupId)) {
            $isDelete = $this->ErpConfigSalaryCod->deleteGroupConfigFine($groupId);
            if (empty($isDelete)) return false;
        }

        $this->ErpStation = getInstance('ErpStation');

        $dataCreate  = [];
        $groupConfig = $this->ErpConfigSalaryCod->getMaxGroupConfig() + 1;

        if (!empty($alNotApply)) $fine['alias_not_apply'] = $alNotApply;
        if (!empty($aliasApply)) $fine['alias_apply'] = $aliasApply;

        if (!empty($station)) {
            foreach ($station as $stationId) {
                $provinceStationId = $this->ErpStation->getProvinceId($stationId);
                if (!in_array($provinceStationId, $province)) continue;

                $dataCreate[] = [
                    'region'        => $region,
                    'group_id'      => $groupConfig,
                    'from_date'     => $from,
                    'to_date'       => $to,
                    'formula'       => $formula,
                    'region_id'     => $region,
                    'province_id'   => $provinceStationId,
                    'station_id'    => $stationId,
                    'fine'          => json_encode($fine),
                    'work_type'     => json_encode($workType),
                    'description'   => $desc ?? '',
                    'created_by'    => AuthComponent::user('id')
                ];
            }
        } else if (!empty($province)) {
            foreach ($province as $provinceId) {
                $dataCreate[] = [
                    'region'        => $region,
                    'group_id'      => $groupConfig,
                    'from_date'     => $from,
                    'to_date'       => $to,
                    'formula'       => $formula,
                    'region_id'     => $region,
                    'province_id'   => $provinceId,
                    'fine'          => json_encode($fine),
                    'work_type'     => json_encode($workType),
                    'description'   => $desc ?? '',
                    'created_by'    => AuthComponent::user('id')
                ];
            }
        } else if (!empty($region)) {
            $dataCreate[] = [
                'region'        => $region,
                'group_id'      => $groupConfig,
                'from_date'     => $from,
                'to_date'       => $to,
                'formula'       => $formula,
                'region_id'     => $region,
                'fine'          => json_encode($fine),
                'work_type'     => json_encode($workType),
                'description'   => $desc ?? '',
                'created_by'    => AuthComponent::user('id')
            ];
        }

        if (empty($dataCreate)) {
            $source->rollback();
            return false;
        }

        try {
            $this->ErpConfigSalaryCod->clear();
            $result = $this->ErpConfigSalaryCod->saveMany($dataCreate);
            if ($result) {
                $source->commit();
                return true;
            }
        } catch (Exception $exception) {
            $this->log($exception->getMessage());
        }

        $source->rollback();
        return false;
    }

    // Todo: Hàm support chạy lương theo danh sách bỏ phạt từ màn config
    public function calSalaryCodProvinceFine ($dataConfigFine) {
        $fromDate   = $dataConfigFine['from_date']   ?? null;
        $toDate     = $dataConfigFine['to_date']     ?? null;
        $listCodRun = $dataConfigFine['alias_apply'] ?? null;

        if (empty($fromDate) || empty($toDate) || !validateDate($fromDate) || !validateDate($toDate)) return false;
        if (empty($listCodRun)) return false;

        $listDays = $this->Calendar->getAllDate($fromDate, $toDate);
        foreach ($listDays as $day) {
            foreach ($listCodRun as $cod) {
                EcomResque::getInstance()->enqueue(
                    'cal_salary',
                    'SosShell',
                    array('runJob', 'calSalaryCodProvince', null, $day, $cod['name'])
                );
            }
        }

        return true;
    }

    public function getWrongPkgRouteClean($date, $codId, $workShift) {
        $salaryMonth = $this->TimeSheets->getSalaryMonthByDate($date);
        $infoCod     = $this->ErpCod->getInfoCod($codId);

        if (empty($infoCod)) return false;

        if ($date >= '2020-11-21') { // Bảng mới
            $this->DataErpCartClearanceProcess = getInstance('DataErpCartClearanceProcess');
            $data = $this->DataErpCartClearanceProcess->getData($salaryMonth['from'], $date, $infoCod['cod_order'], $workShift);
        } else {
            $this->DataErpCodsProcessReportByWorkshiftFailed = getInstance('DataErpCodsProcessReportByWorkshiftFailed');
            $data = $this->DataErpCodsProcessReportByWorkshiftFailed->getData($salaryMonth['from'], $date, $infoCod['cod_order'], $workShift);
        }
        if (empty($data)) return false;

        $dataReturn = [];
        foreach ($data as $datum) {
            $datum = $datum['DataErpCartClearanceProcess'] ?? $datum['DataErpCodsProcessReportByWorkshiftFailed'];

            $dataReturn[$datum['pkg_order']] = [
                'nhap_kho' => $datum['nhap_kho'],
                'xuat_giao' => $datum['xuat_giao'],
                'deadline' => $datum['deadline']
            ];
        }

        $infoPkg = $this->ErpPackageArchive->getPkgInfoByListPkgOrder(array_keys($dataReturn));

        if (empty($infoPkg)) return false;

        foreach ($infoPkg as $pkg) {
            $pkgOrder = $pkg['ErpPackageArchive']['order'];
            $dataReturn[$pkgOrder]['alias'] = $pkg['ErpPackageArchive']['alias'];
        }

        return array_values($dataReturn);
    }

    // Todo: API chi tiết đơn sạch tuyến
    public function getPackageClearanceProcess($cod_id, $date, $shift) {
        $cod = $this->ErpCod->findById($cod_id);
        if (empty($cod['ErpCod']['order'])) return null;
        $cod_order = $cod['ErpCod']['order'];
        if ($shift == 1) $workshift = [1, 2];
        if ($shift == 2) $workshift = [3, 4];
        if ($shift == 3) $workshift = [5];
        if (empty($workshift)) return null;
        $monthSalary = $this->TimeSheets->getSalaryMonthByDate($date);
        $this->DataErpCartClearanceProcess = getInstance('DataErpCartClearanceProcess');
        $data = $this->DataErpCartClearanceProcess->getRouteCleanPackage($monthSalary['from'], $date, $cod_order, $workshift);
        if (empty($data)) return null;
        $detail = [];
        $groupByCart = [];
        $listCartIds = Hash::extract($data, '{n}.DataErpCartClearanceProcess.cart_id');
        $listPkgOrders = Hash::extract($data, '{n}.DataErpCartClearanceProcess.pkg_order');
        $this->ErpCart = getInstance('ErpCart');
        $cartIdToAlias = $this->ErpCart->find('list', [
            'fields' => ['id', 'alias'],
            'conditions' => [
                'id' => $listCartIds
            ]
        ]);
        $pkgOrderToAlias = $this->ErpPackageArchive->getAliasByOrder($listPkgOrders);
        foreach ($data as $value) {
            $value = $value['DataErpCartClearanceProcess'];
            $cart_id = $value['cart_id'];
            $cart_alias = $cartIdToAlias[$cart_id] ?? '';
            $detail[] = [
                'pkg_order' => $value['pkg_order'],
                'pkg_alias' => $pkgOrderToAlias[$value['pkg_order']] ?? '',
                'nhap_kho' => $value['nhap_kho'],
                'xuat_giao' => $value['xuat_giao'],
                'deadline' => $value['deadline'],
                'cart_id' => $value['cart_id'],
                'cart_alias' => $cart_alias,
                'is_true' => (boolean)$value['is_true'],
            ];
            $groupByCart[$cart_id] = $groupByCart[$cart_id] ??
                [
                    'cart_id' => $cart_id,
                    'cart_alias' => $cart_alias,
                    'total_pkgs' => 0,
                    'false_pkgs' => 0
                ];
            $groupByCart[$cart_id]['total_pkgs']++;
            if (!$value['is_true']) $groupByCart[$cart_id]['false_pkgs']++;
        }
        return [
            'cod_alias' => $cod['ErpCod']['alias'],
            'data_date_key' => $monthSalary['from'],
            'date' => $date,
            'shift' => $shift,
            'workshift' => $workshift,
            'group_by_cart' => array_values($groupByCart),
            'detail' => $detail
        ];
    }

    public function detailCleanRouteByMonth ($month, $year, $username) {
        $infoCod  = $this->ErpCod->getInfoCodByUsername($username);

        if (empty($infoCod['ErpCod']['cod_id'])) return false;

        $isCodProvince = $this->isCodProvince($infoCod['ErpCod']['cod_id'], $month, $year);
        if (empty($isCodProvince)) return false;

        $salaryMonth = $this->TimeSheets->getRangeTimeSalaryMonth($month, $year);
        $daysSalary  = $this->Calendar->getAllDate($salaryMonth['start_date'], $salaryMonth['end_date']);

        $dataSalary = $this->ErpSgwCodSalaryKpi->getSalaryCodByListDate(1000, $daysSalary, $infoCod['ErpCod']['cod_id']);
        if (empty($dataSalary)) return false;

        $dataReturn    = [];
        $listWorkShift = ['ws_1', 'ws_2', 'ws_3', 'ws_4', 'ws_5'];

        foreach ($dataSalary as $salaryDate) {
            $date = $salaryDate['ErpSgwCodSalaryKpi']['date'];
            foreach ($listWorkShift as $workShift) {
                if (empty($salaryDate['ErpSgwCodSalaryKpi'][$workShift])) continue;
                $dataInWorkShift = json_decode($salaryDate['ErpSgwCodSalaryKpi'][$workShift], true);
                $dataReturn[$date][$workShift] = [
                    'route_clean_percent' => $dataInWorkShift['kpi_reward_cod_province']['route_clean_percent'] ?? '',
                    'fine_clean_package'  => $dataInWorkShift['kpi_reward_cod_province']['fine_clean_package'] ?? ''
                ];
            }
        }

        return $dataReturn;
    }

    public function isCodProvince ($codId, $month, $year) {
        $codSalaryFormula = $this->ErpSgwCodSalaryFormula->getFormulaCodInMonthSalary($codId, $month, $year);
        if ($codSalaryFormula == 1000) return true;
        return false;
    }

    // Todo: API chi tiết đơn sạch tuyến
    public function getPackageClearanceProcessV2 ($cod_id, $date, $shift) {
        $cod = $this->ErpCod->findById($cod_id);
        if (empty($cod['ErpCod']['order'])) return null;
        $cod_order = $cod['ErpCod']['order'];
        if ($shift == 1) $workshift = [1, 2];
        if ($shift == 2) $workshift = [3, 4];
        if ($shift == 3) $workshift = [5];
        if (empty($workshift)) return null;
        $monthSalary = $this->TimeSheets->getSalaryMonthByDate($date);
        $formula = $this->ErpCod->getCodSalaryFormula($cod_id, $monthSalary['month'], $monthSalary['year']);
        $collection = null;
        if ($formula != 1000) $collection = 'huyen_ingest_cod_cart_clearance_process';
        $this->DataErpCartClearanceProcess = getInstance('DataErpCartClearanceProcess');
        $data = $this->DataErpCartClearanceProcess->getRouteCleanPackage($monthSalary['from'], $date, $cod_order, $workshift, $collection);
        if (empty($data)) return null;
        $detail = [];
        $groupByCart = [];
        $listCartIds = Hash::extract($data, '{n}.DataErpCartClearanceProcess.cart_id');
        $listPkgOrders = Hash::extract($data, '{n}.DataErpCartClearanceProcess.pkg_order');
        $this->ErpCart = getInstance('ErpCart');
        $cartIdToAlias = $this->ErpCart->find('list', [
            'fields' => ['id', 'alias'],
            'conditions' => [
                'id' => $listCartIds
            ]
        ]);
        $pkgOrderToAlias = $this->ErpPackageArchive->getAliasByOrder($listPkgOrders);
        foreach ($data as $value) {
            $value = $value['DataErpCartClearanceProcess'];
            $cart_id = $value['cart_id'];
            $cart_alias = $cartIdToAlias[$cart_id] ?? '';
            $pkgInfo = [
                'pkg_order' => $value['pkg_order'],
                'pkg_alias' => $pkgOrderToAlias[$value['pkg_order']] ?? '',
                'nhap_kho' => $value['nhap_kho'],
                'xuat_giao' => $value['xuat_giao'],
                'deadline' => $value['deadline'],
                'cart_id' => $value['cart_id'],
                'cart_alias' => $cart_alias,
                'is_true' => (boolean)$value['is_true'],
                'is_true_cod' => $value['is_true_cod']
            ];
            $groupByCart[$cart_id] = $groupByCart[$cart_id] ??
                [
                    'cart_id' => $cart_id,
                    'cart_alias' => $cart_alias,
                    'total_pkgs' => 0,
                    'false_pkgs' => 0,
                    'details'    => []
                ];
            $groupByCart[$cart_id]['total_pkgs']++;
            $groupByCart[$cart_id]['details'][] = $pkgInfo;
            if (!$value['is_true']) $groupByCart[$cart_id]['false_pkgs']++;
        }
        return [
            'cod_alias' => $cod['ErpCod']['alias'],
            'data_date_key' => $monthSalary['from'],
            'date' => $date,
            'shift' => $shift,
            'workshift' => $workshift,
            'group_by_cart' => array_values($groupByCart)
        ];
    }

    public function exportIndicatorCod($fromDate, $toDate, $regionId, $provinceId, $stationId) {
        $listDate        = $this->Calendar->getAllDate($fromDate, $toDate);
        $salaryMonth     = $this->TimeSheets->getSalaryMonthByDate($fromDate);
        $listCodProvince = $this->ErpSgwCodSalaryFormula->getListCodsByMonthSalaryV2($salaryMonth['month'], $salaryMonth['year'], 1000, $regionId, $provinceId, $stationId);

        if (empty($listCodProvince)) return false;

        $listCodProvince = Hash::combine($listCodProvince, '{n}.ErpCod.order', '{n}.ErpCod.alias', '{n}.ErpSgwCodSalaryFormula.cod_id');
        $dataExport      = [];

        foreach ($listCodProvince as $codId => $codOrderAlias) {
            $codOrder = key($codOrderAlias);
            $codAlias = current($codOrderAlias);

            $dataSalary = $this->ErpSgwCodSalaryKpi->getSalaryCodByListDate(1000, $listDate, $codId);

            if (empty($dataSalary)) continue;

            $dataDetail = $this->BigDataDbStationProcessCod->getAllDataCodProvinceByListDate($salaryMonth['from'], $listDate, $codOrder);

            if (empty($dataDetail)) continue;

            $groupDataByDate = [];
            foreach ($dataDetail as $dDetail) {
                $dDetail = $dDetail['BigDataDbStationProcessCod'];
                $date    = $dDetail['cur_date'];

                if (!isset($groupDataByDate[$date])) {
                    $groupDataByDate[$date] = [
                        'd_success' => $dDetail['d_success'],
                        'r_score'   => $dDetail['r_score'],
                        'p_score'   => $dDetail['p_score'],
                        'p_success' => $dDetail['p_success'],
                        'r_success' => $dDetail['r_success']
                    ];
                    continue;
                }
                $groupDataByDate[$date]['d_success']  += $dDetail['d_success'];
                $groupDataByDate[$date]['r_score']    += $dDetail['r_score'];
                $groupDataByDate[$date]['p_score']    += $dDetail['p_score'];
                $groupDataByDate[$date]['p_success']  += $dDetail['p_success'];
                $groupDataByDate[$date]['r_success']  += $dDetail['r_success'];
            }

            foreach ($dataSalary as $salary) {
                $fix = json_decode($salary['ErpSgwCodSalaryKpi']['fix'], true);
                $date = $salary['ErpSgwCodSalaryKpi']['date'];

                $dataExport[] = [
                    'alias'     => $codAlias,
                    'date'      => $date,
                    'd_success' => $groupDataByDate[$date]['d_success'] ?? 0,
                    'r_score'   => $groupDataByDate[$date]['r_score']   ?? 0,
                    'p_score'   => $groupDataByDate[$date]['p_score']   ?? 0,
                    'p_success' => $groupDataByDate[$date]['p_success'] ?? 0,
                    'r_success' => $groupDataByDate[$date]['r_success'] ?? 0,
                    'ws_3'      => $fix['bonus_work_on_night']          ?? 0,
                ];
            }
        }

        App::uses('ErpTransitSalaryComponent', 'Erp.Controller/Component');
        $this->ErpTransitSalary = new ErpTransitSalaryComponent(new ComponentCollection());

        $headerToValue = [
            'alias'     => 'Mã cod',
            'date'      => 'Ngày',
            'd_success' => 'Tổng đơn giao công',
            'r_score'   => 'Tổng điểm trả thành công',
            'p_score'   => 'Tổng điểm lấy thành công',
            'p_success' => 'Tổng đơn lấy thành công',
            'r_success' => 'Tổng đơn trả thành công',
            'ws_3'      => 'Phụ cấp tối'
        ];

        return $this->ErpTransitSalary->quickExportDataToExcel($headerToValue, $dataExport, 'CS_F1000', NOW . ".xlsx", AuthComponent::user('id'));
    }

    /*---------------Các hàm, logic hard code cũ chỉ code dùng một lần => không cần care ở main code------------------*/
    // todo: Kỳ lương tháng 5, 6 bỏ phạt phiên đối với các cod thuộc tỉnh 'Bình Dương', 'Đồng Nai', 'Long An'
    public function excludeFineT5T6V1 (&$session, &$listOfSession, $maxSessionMinusByWorkType, $month, $year, $alias, $provinceId) {
        if (
            (in_array($month, [5, 6, 7]) && $year == 2020 && in_array($provinceId, $this->LIST_PROVINCE_EXCLUDE_V1)) ||
            ($month == 7 && $year == 2020 && in_array($alias, $this->LIST_COD_BO_PHAT_PHIEN_T7_N2020))
        ) {
            foreach ($listOfSession as $dateCheck => $sessionOfDate) {
                if (empty($sessionOfDate)) continue;
                // Nếu có đi làm nhưng chỉ chốt 1 phiên thì vẫn được tính theo số phiên tối đa của 1 ngày
                $session['off_session'] -= $maxSessionMinusByWorkType - $sessionOfDate; // Số phiên nghỉ sẽ giảm bằng số phiên cộng thêm
                $listOfSession[$dateCheck] = $maxSessionMinusByWorkType; // Cập nhập lại list phiên
            }
        }
    }

    // todo: Bo phat thang 8 nam 2020
    public function excludeFineT8N2020 (&$session, &$listOfSession, $maxSessionMinusByWorkType, $alias, $month, $year) {
        if (in_array($alias, $this->LIST_COD_BP_PHIEN_T8_N2020) && $month == 8 && $year == 2020) {
            foreach ($listOfSession as $dateCheck => $sessionOfDate) {
                if (empty($sessionOfDate)) continue;
                // Nếu có đi làm nhưng chỉ chốt 1 phiên thì vẫn được tính theo số phiên tối đa của 1 ngày
                $session['off_session'] -= $maxSessionMinusByWorkType - $sessionOfDate; // Số phiên nghỉ sẽ giảm bằng số phiên cộng thêm
                $listOfSession[$dateCheck] = $maxSessionMinusByWorkType; // Cập nhập lại list phiên
            }
        }
    }

    // todo: bỏ phạt phiên tháng 6 do lỗi hệ thống đối với cod fulltime
    public function excludeFineT6ByErrorSystem(&$session, &$listOfSession, $maxSessionMinusByWorkType, $workTypeId, $alias, $month, $year) {
        if ($workTypeId == 1) {
            foreach ($listOfSession as $dateCheck => $sessionOfDate) {
                $isUpdate = false;
                if (in_array($dateCheck, $this->LIST_DATE_EXCLUDE_T6_2020_V1) && $sessionOfDate == 1) $isUpdate = true;

                if (
                    in_array($alias, $this->LIST_COD_BU_PHIEN_T6_2020) &&
                    $sessionOfDate == 1 &&
                    $month == 06 &&
                    $year == 2020
                ) $isUpdate = true;

                if (
                    strtotime($dateCheck) >= strtotime('2020-05-21') &&
                    strtotime($dateCheck) <= strtotime('2020-06-08') &&
                    in_array($alias, $this->LIST_COD_BU_PHIEN_21T8_08T6) &&
                    $sessionOfDate == 1
                ) $isUpdate = true;

                if ($isUpdate == true) {
                    $session['off_session']     -= $maxSessionMinusByWorkType - $sessionOfDate; // Số phiên nghỉ sẽ giảm bằng số phiên cộng thêm
                    $listOfSession[$dateCheck]   = $maxSessionMinusByWorkType; // Cập nhập lại list phiên
                }
            }
        }
    }

    // todo: Coupon T7-2020 cho 3 tỉnh Bình Dương, Long An, Đồng Nai
    public function couponT7N2020V1 (&$dataReturn, $listDateCal, $salaryMonth, $province, $positionJob, $workTypeId, $month, $year) {
        if (
            in_array($province, $this->LIST_PROVINCE_EXCLUDE_V1) &&
            ($positionJob != 98 && $workTypeId != 13) &&
            $month == 7 && $year == 2020
        ) $dataReturn['detail_coupon'] = $this->calCouponT7N2020($salaryMonth, $listDateCal);
        $dataReturn['total_coupon'] = array_sum($dataReturn['detail_coupon']);
    }
    /*----------------------------------------------------------------------------------------------------------------*/
}
