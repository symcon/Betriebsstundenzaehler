<?php

declare(strict_types=1);

include_once __DIR__ . '/timetest.php';

define('LVL_DAY', 1);
define('LVL_WEEK', 2);
define('LVL_MONTH', 3);
define('LVL_YEAR', 4);
define('LVL_COMPLETE', 5);

class Betriebsstundenzaehler extends IPSModule
{
    use TestTime;
    public function Create()
    {
        //Never delete this line!
        parent::Create();

        //Properties
        $this->RegisterPropertyInteger('Source', 0);
        $this->RegisterPropertyInteger('Level', LVL_DAY);
        $this->RegisterPropertyInteger('Interval', 30);
        $this->RegisterPropertyBoolean('Active', false);
        $this->RegisterPropertyFloat('Price', 0.00);
        $this->RegisterPropertyBoolean('CalculateCost', false);

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
        // Do not throw this message during testing. Verifying the status code is enough
        if (!defined('PHPUNIT_TESTSUITE')) {
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
        }

        $aggregationLevel = $this->ReadPropertyInteger('Level');
        switch ($aggregationLevel) {
            case LVL_DAY:
                $startTimeThisPeriod = strtotime('today 00:00:00', $this->getTime());
                $startTimeLastPeriod = strtotime('-1 day', $startTimeThisPeriod);
                $endTimeThisPeriod = strtotime('+1 day', $startTimeThisPeriod);
                break;

            case LVL_WEEK:
                $startTimeThisPeriod = strtotime('monday this week 00:00:00', $this->getTime());
                $startTimeLastPeriod = strtotime('-1 week', $startTimeThisPeriod);
                $endTimeThisPeriod = strtotime('+1 week', $startTimeThisPeriod);
                break;

            case LVL_MONTH:
                $startTimeThisPeriod = strtotime('first day of this month 00:00:00', $this->getTime());
                $startTimeLastPeriod = strtotime('-1 month', $startTimeThisPeriod);
                $endTimeThisPeriod = strtotime('+1 month', $startTimeThisPeriod);
                break;

            case LVL_YEAR:
                $startTimeThisPeriod = strtotime('first day of january 00:00:00', $this->getTime());
                $startTimeLastPeriod = strtotime('-1 year', $startTimeThisPeriod);
                $endTimeThisPeriod = strtotime('+1 year', $startTimeThisPeriod);
                break;

            case LVL_COMPLETE:
                $startTimeThisPeriod = 0;
                $aggregationLevel = 4;
                $startTimeLastPeriod = 0;
                break;

            default:
                $startTimeThisPeriod = 0;
                $startTimeLastPeriod = 0;
        }

        $archiveID = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0];
        $getHours = function ($timeStart, $timeEnd) use ($archiveID, $aggregationLevel)
        {
            $values = AC_GetAggregatedValues($archiveID, $this->ReadPropertyInteger('Source'), $aggregationLevel, $timeStart, $timeEnd, 0);
            $this->SendDebug('AggregatedValues', json_encode($values), 0);
            $seconds = 0;
            foreach ($values as $value) {
                $seconds += $value['Avg'] * $value['Duration'];
            }
            return $seconds / (60 * 60);
        };

        $this->SetValue('OperatingHours', $getHours($startTimeThisPeriod, $this->getTime()));

        if ($this->ReadPropertyBoolean('CalculateCost')) {
            $this->SetValue('CostThisPeriod', $getHours($startTimeThisPeriod, $this->getTime()) * $this->ReadPropertyFloat('Price') / 100);

            if ($this->ReadPropertyInteger('Level') != LVL_COMPLETE) {
                $currendDuration = $this->getTime() - $startTimeThisPeriod;
                $endOfDuration = $endTimeThisPeriod - $startTimeThisPeriod;
                $percentOfCurrendPeriod = $currendDuration / $endOfDuration * 100;
                $this->SetValue('PredictionThisPeriod', $this->GetValue('CostThisPeriod') / $percentOfCurrendPeriod * 100);
                $this->SetValue('CostLastPeriod', $getHours($startTimeLastPeriod, ($startTimeThisPeriod - 1)) * $this->ReadPropertyFloat('Price') / 100);
            }
        }
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

        if ($this->GetTimerInterval('UpdateCalculationTimer') < ($this->ReadPropertyInteger('Interval') * 1000 * 60)) {
            $this->SetTimerInterval('UpdateCalculationTimer', $this->ReadPropertyInteger('Interval') * 1000 * 60);
        }

        $this->MaintainVariable('CostThisPeriod', $this->Translate('Cost of this period'), VARIABLETYPE_FLOAT, '~Euro', 0, $this->ReadPropertyBoolean('CalculateCost'));

        $visible = $this->ReadPropertyBoolean('CalculateCost') && ($this->ReadPropertyInteger('Level') != LVL_COMPLETE);
        $this->MaintainVariable('PredictionThisPeriod', $this->Translate('Prediction end of this period'), VARIABLETYPE_FLOAT, '~Euro', 0, $visible);
        $this->MaintainVariable('CostLastPeriod', $this->Translate('Cost of the last period'), VARIABLETYPE_FLOAT, '~Euro', 0, $visible);

        //Reference

         //Deleting all refererences in order to readd them
         foreach ($this->GetReferenceList() as $referenceID) {
            $this->UnregisterReference($referenceID);
        }

        //Register References
        $sourceID = $this->ReadPropertyInteger('Source');
        $this->RegisterReference($sourceID);

        $this->Calculate();
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
