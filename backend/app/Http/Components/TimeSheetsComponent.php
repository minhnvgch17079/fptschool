<?php
namespace App\Http\Components;

class TimeSheetsComponent {
    public function getSalaryMonthByDate ($date, $typeStaff = 'cod') {
        $flagDate = (strtotime($date) >= strtotime('2018-12-20') ) ? 20: 25;

        if ($typeStaff == 'cod') $flagDate = 20;


        $salaryMonth = date('Y-m-15', strtotime($date));

        if (date('d', strtotime($date)) > $flagDate) {
            $date = date('Y-m-20', strtotime($date));
            $salaryMonth = date('Y-m-15', strtotime($date . ' + 1 months'));
        }

        $year   = date('Y', strtotime($salaryMonth));
        $month  = date('m', strtotime($salaryMonth));
        $to     = "{$year}-{$month}-20";
        $from   = date('Y-m-', strtotime('-1 month', strtotime($to))) . '21';

        return [
            'month' => $month,
            'year'  => $year,
            'from'  => $from,
            'to'    =>  $to,
        ];
    }

    public function getRangeTimeSalaryMonth($month, $year)
    {
        if ((int)$month == 01 && (int)$year == 2019) {
            return [
                'start_date' => '2018-12-26',
                'end_date' => '2019-01-20',
            ];
        }

        $end_date = $year . "-" . $month . "-20";
        $end_date = date('Y-m-d', strtotime($end_date));
        $start_date = date("Y-m-21", strtotime($end_date . "-1 month"));
        return [
            'start_date' => $start_date,
            'end_date' => $end_date,
        ];
    }

    public function getTimeSheetData($userId, $from, $to, $status = ['approved'] , $stationId = null, $bIsDriverCal = false) {
        if (empty($userId)) return null;
        $this->ErpUser = getInstance('ErpUser');
        $this->ErpEmpProfile = getInstance('ErpEmpProfile');
        $this->ErpEmpContract = getInstance('ErpEmpContract');

        $userInfo = $this->ErpUser->getInfoUser($userId);

        $this->isMainUser = $this->ErpUser->checkMainUser($userId);

        $startDateDay   = $userInfo['start_date_day'] ?? null;
        $startDateDay =  date('Y-m-d', strtotime($startDateDay));
        // Từ kỳ lương tháng 5 lấy ngày bắt đầu hợp đồng lao động đầu tiên để xác định ngày hết thử việc
        $officialWorkDate = $startDateDay;
        if (strtotime($from) >= strtotime('2020-04-21')) {
            $officialWorkDateNew = $this->ErpUser->getOfficialWorkDate($userId);
            if (!empty($officialWorkDateNew)) $officialWorkDate = $officialWorkDateNew;
        }
//        $startDateDay = '2020-03-25';
//        $officialWorkDate = '2020-10-30';
        $this->user_group_id = $userInfo['group_id'];
        $dateEndProbationary = date('Y-m-d', strtotime($startDateDay. "-1 days"));
        $joinDate   = $userInfo['join_date'] ?? null;
        $joinDate =  date('Y-m-d', strtotime($joinDate));
        $resignDate = null;
        $activeDate = $userInfo['active_date'] ?? null;
        $disableDate = $userInfo['disable_date'] ?? null;
//        if (!empty($resignDate)) {
//            $resignDate = date('Y-m-d', strtotime($resignDate. "-1 days"));
//        }
        $fromTime = max($joinDate, $activeDate, $from);
        $toTime = empty($resignDate) ? $to : min($resignDate, $to);
        $toTime = empty($disableDate) ? $toTime : min($toTime, $disableDate);

        if (empty($startDateDay) || (strtotime($startDateDay) <= strtotime($from)) || ($userInfo['group_id'] == Group::DRIVER && strtotime($from) >= strtotime('2020-11-21'))) { // Toan bo la cong chinh thuc
            $employee_time = $this->getDataTimeSheetInRangeTime($userId, $officialWorkDate, $fromTime, $toTime, $status, 'employee', $stationId, $bIsDriverCal);
            $probationary_time = $this->getDataTimeSheetInRangeTime($userId,  $officialWorkDate, null, null, $status, 'probation', $stationId, $bIsDriverCal);
        } else if ((strtotime($startDateDay) > strtotime($from)) && (strtotime($startDateDay) <= strtotime($to))) {
            $employee_time = $this->getDataTimeSheetInRangeTime($userId, $officialWorkDate, $startDateDay, $toTime, $status, 'employee', $stationId, $bIsDriverCal);
            $probationary_time = $this->getDataTimeSheetInRangeTime($userId, $officialWorkDate, $fromTime, $dateEndProbationary, $status, 'probation', $stationId, $bIsDriverCal);
        } else {
            $employee_time = $this->getDataTimeSheetInRangeTime($userId, $officialWorkDate, null, null, $status, 'employee', $stationId, $bIsDriverCal);
            $probationary_time = $this->getDataTimeSheetInRangeTime($userId, $officialWorkDate, $fromTime, $toTime, $status, 'probation', $stationId, $bIsDriverCal);
        }
        $timeSheet = [];
        $timeSheet['employee_time'] = $employee_time;
        $timeSheet['probationary_time'] = $probationary_time;
        $timeSheet['is_main'] = $this->isMainUser;
        $timeSheet['official_work_date'] = $officialWorkDate;
        $timeSheet['start_date'] = $startDateDay;
        $timeSheet['join_date'] = $joinDate;
        $timeSheet['resign_date'] = $resignDate;
        $timeSheet['active_date'] = max($joinDate, $activeDate);
        $timeSheet['disable_date'] = empty($resignDate) ? ($disableDate ?? '') : '';
        foreach ($employee_time as $k => $v) {
            $timeSheet[$k] = $employee_time[$k] + ($probationary_time[$k] ?? 0);
        }

        $this->ErpUserWorkType = getInstance('ErpUserWorkType');
        $userWorkType = $this->ErpUserWorkType->getUserWorkTypeByUserId($userId);
        $workShift = $userWorkType ? $userWorkType['WorkType']['shift'] : 'day';
        $workType = $userWorkType ? $userWorkType['WorkType']['work_type'] : 'part_time';
        $rangeTime = $this->getSalaryMonthByDate($from);
        $listHolidayAndSunday = $this->Calendar->getDataHolidayAndSunDayInRangeTime($rangeTime['from'], $rangeTime['to']);

        $timeSheet['require_workshift_in_month'] = $listHolidayAndSunday['count_date_excluding_sundays'];
        unset($timeSheet['employee_time']['list_sunday_holiday_workshift']);
        unset($timeSheet['probationary_time']['list_sunday_holiday_workshift']);
        if ($workType == 'part_time' && $workShift == 'sunday') $timeSheet['require_workshift_in_month'] = count($listHolidayAndSunday['list_sundays']);
        return $timeSheet;
    }


    // todo: Lấy ngày bắt đầu và kết thúc tính lương trong tháng theo loại user
    public function getSalaryRangeDateInMonth($groupType, $month, $year)
    {
        $month = isset($month) ? (int) $month : (int) date('n');
        $year = isset($year) ? (int) $year : (int) date('Y');

        if ($month < 1 || $month > 12) {
            return null;
        }

        $month = (int) $month;
        $prevMonth = $month > 1 ? $month - 1 : 12;
        $prevYear = $month > 1 ? $year : $year - 1;

        if ($month < 10) {
            $month = '0' . $month;
        }

        if ($prevMonth < 10) {
            $prevMonth = '0' . $prevMonth;
        }

        if ($groupType === 'cod' || (strtotime("$year-$month-01") >= strtotime('2019-02-01'))) {
            $from = "$prevYear-$prevMonth-21";
            $to = "$year-$month-20";
        } else if ($year == 2019 && $month == 1) {
            $from = "$prevYear-$prevMonth-26";
            $to = "$year-$month-20";
        } else {
            $from = "$prevYear-$prevMonth-26";
            $to = "$year-$month-25";
        }

        return [
            'from' => $from,
            'to' => $to
        ];
    }
}
