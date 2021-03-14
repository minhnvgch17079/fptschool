<?php
namespace App\Http\Components;

class CalendarComponent {

    // todo: Is sunday ?
    public function checkDateIsSunday($date){
        $dateFormat =  strtotime($date);
        $dateFormat = date("l", $dateFormat);
        $dateFormat = strtolower($dateFormat);
        if( ($dateFormat == 'sunday')  ){
            return true;
        }
        return false;
    }
}
