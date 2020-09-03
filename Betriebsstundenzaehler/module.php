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

        //Messages
        $this->RegisterMessage(0, IPS_KERNELMESSAGE);
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

        //Only call this in READY state. On startup the ArchiveControl instance might not be available yet
        if (IPS_GetKernelRunlevel() == KR_READY) {
            $this->setupInstance();
        }
    }

    public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    {
        //Calculate when the archive module is loaded
        if ($Message == IPS_KERNELMESSAGE && $Data[0] == KR_READY) {
            $this->setupInstance();
        }
    }

    public function Calculate()
    {
        $errorState = $this->getErrorState();

        if ($errorState != 102) {
            $statuscodes = [];
            $statusForm = json_decode(IPS_GetConfigurationForm($this->InstanceID), true)['status'];
            foreach ($statusForm as $status) {
                $statuscodes[$status['code']] = $status['caption'];
            }
            echo $this->Translate($statuscodes[$errorState]);
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

    private function setupInstance()
    {
        $newStatus = 102;

        if (!$this->ReadPropertyBoolean('Active')) {
            $newStatus = 104;
        } else {
            $newStatus = $this->getErrorState();
        }
        $this->SetStatus($newStatus);
        if ($newStatus != 102) {
            $this->SetTimerInterval('UpdateCalculationTimer', 0);
            $this->SetValue('OperatingHours', 0);
            return;
        }

        if ($this->ReadPropertyBoolean('Active') && $this->GetTimerInterval('UpdateCalculationTimer') < ($this->ReadPropertyInteger('Interval') * 1000 * 60)) {
            $this->SetTimerInterval('UpdateCalculationTimer', $this->ReadPropertyInteger('Interval') * 1000 * 60);
        } elseif (!$this->ReadPropertyBoolean('Active')) {
            $this->SetTimerInterval('UpdateCalculationTimer', 0);
        }
    }

    private function getErrorState()
    {
        $source = $this->ReadPropertyInteger('Source');
        $archiveID = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0];
        //102 suggests everything is working not the active status
        $returnState = 102;
        if ($source == 0) {
            $returnState = 202;
        } elseif (!IPS_VariableExists($source)) {
            $returnState = 200;
        } elseif (!AC_GetLoggingStatus($archiveID, $source) || (IPS_GetVariable($source)['VariableType'] != 0)) {
            $returnState = 201;
        }

        return $returnState;
    }
}
