<?php

declare(strict_types=1);

include_once __DIR__ . '/TestBase.php';

class BetriebsstundenzaehlerTest extends TestBase
{
    public function test_CalculationDay()
    {
        //Variables
        $variableID = IPS_CreateVariable(0);
        $archivID = $this->archiveControlID;
        $instanceID = $this->betriebsstundenzaehlerID;

        //Set archived settings
        AC_SetLoggingStatus($archivID, $variableID, true);

        //Custom Time for Testing
        BSZ_setTime($instanceID, strtotime('May 25 1971 12:00'));

        //Add archive data
        $aggregationPeriodDay = [
            [
                'Avg'       => 0.5,
                'Duration'  => 24 * 60 * 60,
                'Max'       => 0,
                'MaxTime'   => 0,
                'Min'       => 0,
                'MinTime'   => 0,
                'TimeStamp' => strtotime('May 23 1971 00:00:00'),
            ],
            [
                'Avg'       => 0.5,
                'Duration'  => 24 * 60 * 60,
                'Max'       => 0,
                'MaxTime'   => 0,
                'Min'       => 0,
                'MinTime'   => 0,
                'TimeStamp' => strtotime('May 24 1971 00:00:00'),
            ],
            [
                'Avg'       => 0.5,
                'Duration'  => 12 * 60 * 60,
                'Max'       => 0,
                'MaxTime'   => 0,
                'Min'       => 0,
                'MinTime'   => 0,
                'TimeStamp' => strtotime('May 25 1971 00:00:00'),
            ]
        ];
        AC_StubsAddAggregatedValues($archivID, $variableID, 1, $aggregationPeriodDay);

        //Properties
        IPS_SetProperty($instanceID, 'Price', 1.0);
        IPS_SetProperty($instanceID, 'Source', $variableID);
        IPS_SetProperty($instanceID, 'Active', true);
        IPS_SetProperty($instanceID, 'CalculateCost', true);

        IPS_ApplyChanges($instanceID);

        //Not use because it calls in ApplyChanges
        //BSZ_Calculate($instanceID);

        $this->assertEquals(0.12, GetValue(IPS_GetObjectIDByIdent('CostLastPeriod', $instanceID)));
        $this->assertEquals(0.06, GetValue(IPS_GetObjectIDByIdent('CostThisPeriod', $instanceID)));
        $this->assertEquals(0.12, GetValue(IPS_GetObjectIDByIdent('PredictionThisPeriod', $instanceID)));
    }
}