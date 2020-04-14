<?php

declare(strict_types=1);

define('LVL_DAY', 1);
define('LVL_WEEK', 2);
define('LVL_MONTH', 3);
define('LVL_YEAR', 4);
define('LVL_COMPLETE', 5);

class Betriebsstundenzaehler extends IPSModule
{
    public function Create()
    {
        //Never delete this line!
        parent::Create();

        //Properties
        $this->RegisterPropertyInteger('Source', 0);
        $this->RegisterPropertyInteger('Level', LVL_DAY);
        $this->RegisterPropertyInteger('Interval', 30);
        $this->RegisterPropertyBoolean('Active', false);

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
        $newStatus = 0;

        if (!$this->ReadPropertyBoolean('Active')) {
            $newStatus = 104;
        } else {
            $newStatus = $this->getErrorState();
        }
        if ($this->ReadPropertyBoolean('Active') && $newStatus == 0) {
            $this->SetStatus(102);
            $newStatus = 102;
        } else {
            $this->SetStatus($newStatus);
            $this->SetTimerInterval('UpdateCalculationTimer', 0);
            $this->SetValue('OperatingHours', 0);
            return;
        }

        if ($this->ReadPropertyBoolean('Active') && $this->GetTimerInterval('UpdateCalculationTimer') < ($this->ReadPropertyInteger('Interval') * 1000 * 60)) {
            $this->SetTimerInterval('UpdateCalculationTimer', $this->ReadPropertyInteger('Interval') * 1000 * 60);
        } elseif (!$this->ReadPropertyBoolean('Active')) {
            $this->SetTimerInterval('UpdateCalculationTimer', 0);
        }
        $this->Calculate();
    }

    public function Calculate()
    {
        $errorState = $this->getErrorState();

        if ($errorState != 0) {
            $statuscodes = [];
            $statusForm = json_decode(IPS_GetConfigurationForm($this->InstanceID), true)['status'];
            foreach ($statusForm as $status) {
                $statuscodes[$status['code']] = $status['caption'];
            }
            echo $this->Translate($this->evaluateStatus($statuscodes[$errorState]));
            return;
        }

        $aggregationLevel = $this->ReadPropertyInteger('Level');
        switch ($aggregationLevel) {
            case LVL_DAY:
                $startTime = strtotime('today 00:00:00', time());
                break;
            case LVL_WEEK:
                $startTime = strtotime('last monday 00:00:00', time());
                break;
            case LVL_MONTH:
                $startTime = strtotime('first day of this month 00:00:00', time());
                break;
            case LVL_YEAR:
                $startTime = strtotime('1st january 00:00:00', time());
                break;
            case LVL_COMPLETE:
                $startTime = 0;
                $aggregationLevel = 4;
                break;
            default:
                $startTime = 0;
            }
        $archiveID = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0];
        $values = AC_GetAggregatedValues($archiveID, $this->ReadPropertyInteger('Source'), $aggregationLevel, $startTime, time(), 0);
        $this->SendDebug('AggregatedValues', json_encode($values), 0);
        $seconds = 0;
        foreach ($values as $value) {
            $seconds += $value['Avg'] * $value['Duration'];
        }
        $this->SetValue('OperatingHours', ($seconds / (60 * 60)));
    }

    private function getErrorState()
    {
        $source = $this->ReadPropertyInteger('Source');
        $archiveID = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0];
        $returnState = 0;
        if ($source == 0) {
            $returnState = 202;
        } elseif (!IPS_VariableExists($source)) {
            $returnState = 200;
        } elseif (!AC_GetLoggingStatus($archiveID, $source) || (IPS_GetVariable($source)['VariableType'] != 0)) {
            $returnState = 201;
        }
    }
}