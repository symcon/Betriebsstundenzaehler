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
        $this->RegisterPropertyInteger('Level', 1);
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
        if ($this->CheckSourceVariable() == 102) {
            if ($this->ReadPropertyBoolean('Active') && $this->GetTimerInterval('UpdateCalculationTimer') < ($this->ReadPropertyInteger('Interval') * 1000 * 60)) {
                $this->SetTimerInterval('UpdateCalculationTimer', $this->ReadPropertyInteger('Interval') * 1000 * 60);
            } elseif (!$this->ReadPropertyBoolean('Active')) {
                $this->SetTimerInterval('UpdateCalculationTimer', 0);
            }
            $this->Calculate();
        }
    }

    public function Calculate()
    {
        if ($this->GetSourceStatus() != 0) {
            $this->SendDebug('Error', $this->evaluateStatus($this->GetSourceStatus()), 0);
            echo $this->Translate($this->evaluateStatus($this->GetSourceStatus()));
            return;
        }

        $archiveID = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0];
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
        $values = AC_GetAggregatedValues($archiveID, $this->ReadPropertyInteger('Source'), $aggregationLevel, $startTime, time(), 0);
        $this->SendDebug('AggregatedValues', json_encode($values), 0);
        $seconds = 0;
        foreach ($values as $value) {
            $seconds += $value['Avg'] * $value['Duration'];
        }
        $this->SetValue('OperatingHours', ($seconds / (60 * 60)));
    }

    private function CheckSourceVariable()
    {
        $source = $this->ReadPropertyInteger('Source');
        $archiveID = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0];
        $returnStatus = 0;
        if (!$this->ReadPropertyBoolean('Active')) {
            $this->SetErrorState(104);
            return 104;
        } elseif ($source == 0) {
            $this->SetErrorState(202);
            return 202;
        } elseif (!IPS_VariableExists($source)) {
            $this->SetErrorState(200);
            return 200;
        } elseif (!AC_GetLoggingStatus($archiveID, $source) || (IPS_GetVariable($source)['VariableType'] != 0)) {
            $this->SetErrorState(201);
            return  201;
        }
        if ($this->ReadPropertyBoolean('Active')) {
            $this->SetStatus(102);
            return 102;
        }
    }

    private function GetSourceStatus()
    {
        $source = $this->ReadPropertyInteger('Source');
        $archiveID = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0];
        $returnStatus = 0;
        if ($source == 0) {
            $returnStatus = 202;
        } elseif (!IPS_VariableExists($source)) {
            $returnStatus = 200;
        } elseif (!AC_GetLoggingStatus($archiveID, $source) || (IPS_GetVariable($source)['VariableType'] != 0)) {
            $returnStatus = 201;
        }
        return $returnStatus;
    }

    private function evaluateStatus($code)
    {
        $statuscodes = [];
        $statusForm = json_decode(IPS_GetConfigurationForm($this->InstanceID), true)['status'];
        foreach ($statusForm as $status) {
            $statuscodes[$status['code']] = $status['caption'];
        }
        return $statuscodes[$code];
    }

    private function SetErrorState($status)
    {
        $this->SetStatus($status);
        $this->SetTimerInterval('UpdateCalculationTimer', 0);
        $this->SetValue('OperatingHours', 0);
    }
}