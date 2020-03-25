<?php

declare(strict_types=1);
class Betriebsstundenzaehler extends IPSModule
{
    public function Create()
    {
        //Never delete this line!
        parent::Create();

        //Properties
        $this->RegisterPropertyInteger('Source', 0);
        $this->RegisterPropertyInteger('Level', 1);
        $this->RegisterPropertyInteger('Interval', 0);

        //VariableProfiles
        if (!IPS_VariableProfileExists('BSZ.OperatingHours')) {
            IPS_CreateVariableProfile('BSZ.OperatingHours', 2);
            IPS_SetVariableProfileText('BSZ.OperatingHours', '', $this->Translate(' hours'));
        }

        //Variables
        $this->RegisterVariableFloat('OperatingHours', $this->Translate('Hours of Operation'), 'BSZ.OperatingHours', 10);

        //Timer
        $this->RegisterTimer('UpdateCalculationTimer', 0, 'BSZ_Calculate($_IPS[\'TARGET\']);');
    }

    public function Destroy()
    {
        //Never delete this line!
        parent::Destroy();
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
        $source = $this->ReadPropertyInteger('Source');
        $archiveID = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0];
        if ($source == 0) {
            $this->SetErrorState(104);
        } elseif (!IPS_VariableExists($source)) {
            $this->SetErrorState(200);
        } elseif (!AC_GetLoggingStatus($archiveID, $source) || (IPS_GetVariable($source)['VariableType'] != 0)) {
            $this->SetErrorState(201);
        } else {
            $this->SetStatus(102);
            if ($this->GetTimerInterval('UpdateCalculationTimer') > ($this->ReadPropertyInteger('Interval') * 1000 * 60)) {
                $this->SetTimerInterval('UpdateCalculationTimer', $this->ReadPropertyInteger('Interval') * 1000 * 60);
            }
        }
        $this->Calculate();
        $this->SendDebug('TimerInterval', $this->GetTimerInterval('UpdateCalculationTimer'), 0);
        $this->GetTimerInterval('UpdateCalculationTimer');
    }

    public function Calculate()
    {
        if ($this->GetStatus() == 102) {
            $archiveID = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0];
            $aggregationLevel = $this->ReadPropertyInteger('Level');
            switch ($aggregationLevel) {
                case 1:
                    $startTime = strtotime('today 00:00:00', time());
                    break;
                case 2:
                    $startTime = strtotime('last monday 00:00:00', time());
                    break;
                case 3:
                    $startTime = strtotime('first day of this month 00:00:00', time());
                    break;
                case 4:
                    $startTime = strtotime('1st january 00:00:00', time());
                    break;
                case 5:
                    $startTime = 0;
                    $aggregationLevel = 4;
                    break;
                default:
                    $startTime = 0;
            }
            $values = AC_GetAggregatedValues($archiveID, $this->ReadPropertyInteger('Source'), $aggregationLevel, $startTime, time(), 0);
            $this->SendDebug('AggregatedValues', json_encode($values), 0);
            $average = 0;
            $duration = 0;
            foreach ($values as $value) {
                $average += $value['Avg'];
                $duration += $value['Duration'];
            }
            $seconds = $duration * $average;
            $this->SetValue('OperatingHours', ($seconds / (60 * 60)));
        }
    }

    private function SetErrorState($status)
    {
        $this->SetStatus($status);
        $this->SetTimerInterval('UpdateCalculationTimer', 0);
        $this->SetValue('OperatingHours', 0);
    }
}