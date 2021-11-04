<?php

declare(strict_types=1);

include_once __DIR__ . '/TestBase.php';

class BetriebsstundenzaehlerTest extends TestBase
{
    public function testCalculationDay()
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
    public function testCalculationWeek()
    {
        //Variables
        $variableID = IPS_CreateVariable(0);
        $archivID = $this->archiveControlID;
        $instanceID = $this->betriebsstundenzaehlerID;

        //Set archived settings
        AC_SetLoggingStatus($archivID, $variableID, true);

        //Custom Time for Testing
        BSZ_setTime($instanceID, strtotime('May 07 2020 12:00'));

        //Add archive data
        $aggregationPeriodWeek = [
            [
                'Avg'       => 0.5,
                'Duration'  => 7 * 24 * 60 * 60,
                'Max'       => 0,
                'MaxTime'   => 0,
                'Min'       => 0,
                'MinTime'   => 0,
                'TimeStamp' => strtotime('April 27 2020 00:00:00'),
            ],
            [
                'Avg'       => 1,
                'Duration'  => 3 * 24 * 60 * 60,
                'Max'       => 0,
                'MaxTime'   => 0,
                'Min'       => 0,
                'MinTime'   => 0,
                'TimeStamp' => strtotime('May 4 2020 00:00:00'),
            ],
            [
                'Avg'       => 1,
                'Duration'  => 12 * 60 * 60,
                'Max'       => 0,
                'MaxTime'   => 0,
                'Min'       => 0,
                'MinTime'   => 0,
                'TimeStamp' => strtotime('May 7 2020 00:00:00'),
            ]
        ];
        AC_StubsAddAggregatedValues($archivID, $variableID, 2, $aggregationPeriodWeek);

        //Properties
        IPS_SetProperty($instanceID, 'Price', 1.0);
        IPS_SetProperty($instanceID, 'Source', $variableID);
        IPS_SetProperty($instanceID, 'Active', true);
        IPS_SetProperty($instanceID, 'CalculateCost', true);
        IPS_SetProperty($instanceID, 'Level', LVL_WEEK);

        IPS_ApplyChanges($instanceID);

        //Not use because it calls in ApplyChanges
        //BSZ_Calculate($instanceID);

        //Assert
        $this->assertEquals(0.84, GetValue(IPS_GetObjectIDByIdent('CostThisPeriod', $instanceID)));
        $this->assertEquals(0.84, GetValue(IPS_GetObjectIDByIdent('CostLastPeriod', $instanceID)));
        $this->assertEquals(1.68, GetValue(IPS_GetObjectIDByIdent('PredictionThisPeriod', $instanceID)));
    }
    public function testCalculationMonth()
    {
        //Variables
        $variableID = IPS_CreateVariable(0);
        $archivID = $this->archiveControlID;
        $instanceID = $this->betriebsstundenzaehlerID;

        //Set archived settings
        AC_SetLoggingStatus($archivID, $variableID, true);

        //Custom Time for Testing
        BSZ_setTime($instanceID, strtotime('February 15 2021 00:00'));

        //Add archive data
        $aggregationPeriodMonth = [
            [
                'Avg'       => 0.5,
                'Duration'  => 7 * 24 * 60 * 60,
                'Max'       => 0,
                'MaxTime'   => 0,
                'Min'       => 0,
                'MinTime'   => 0,
                'TimeStamp' => strtotime('January 10 2021 00:00:00'),
            ],
            [
                'Avg'       => 1,
                'Duration'  => 7 * 24 * 60 * 60,
                'Max'       => 0,
                'MaxTime'   => 0,
                'Min'       => 0,
                'MinTime'   => 0,
                'TimeStamp' => strtotime('February 01 2021 00:00:00'),
            ]
        ];
        AC_StubsAddAggregatedValues($archivID, $variableID, 3, $aggregationPeriodMonth);

        //Properties
        IPS_SetProperty($instanceID, 'Price', 1.0);
        IPS_SetProperty($instanceID, 'Source', $variableID);
        IPS_SetProperty($instanceID, 'Active', true);
        IPS_SetProperty($instanceID, 'CalculateCost', true);
        IPS_SetProperty($instanceID, 'Level', LVL_MONTH);

        IPS_ApplyChanges($instanceID);

        //Not use because it calls in ApplyChanges
        //BSZ_Calculate($instanceID);

        //Assert
        $this->assertEquals(1.68, GetValue(IPS_GetObjectIDByIdent('CostThisPeriod', $instanceID)));
        $this->assertEquals(0.84, GetValue(IPS_GetObjectIDByIdent('CostLastPeriod', $instanceID)));
        $this->assertEquals(3.36, GetValue(IPS_GetObjectIDByIdent('PredictionThisPeriod', $instanceID)));
    }

    public function testCalculationYear()
    {
        //Variables
        $variableID = IPS_CreateVariable(0);
        $archivID = $this->archiveControlID;
        $instanceID = $this->betriebsstundenzaehlerID;

        //Set archived settings
        AC_SetLoggingStatus($archivID, $variableID, true);

        //Custom Time for Testing
        BSZ_setTime($instanceID, strtotime('March 29 2021 12:00'));

        //Add archive data
        $aggregationPeriodMonth = [
            [
                'Avg'       => 0.5,
                'Duration'  => 14 * 24 * 60 * 60,
                'Max'       => 0,
                'MaxTime'   => 0,
                'Min'       => 0,
                'MinTime'   => 0,
                'TimeStamp' => strtotime('January 10 2021 00:00:00'),
            ],
            [
                'Avg'       => 0.5,
                'Duration'  => 14 * 24 * 60 * 60,
                'Max'       => 0,
                'MaxTime'   => 0,
                'Min'       => 0,
                'MinTime'   => 0,
                'TimeStamp' => strtotime('February 2021 00:00:00'),
            ],
            [
                'Avg'       => 1,
                'Duration'  => 28 * 24 * 60 * 60,
                'Max'       => 0,
                'MaxTime'   => 0,
                'Min'       => 0,
                'MinTime'   => 0,
                'TimeStamp' => strtotime('February 01 2020 00:00:00'),
            ]
        ];
        AC_StubsAddAggregatedValues($archivID, $variableID, 4, $aggregationPeriodMonth);

        //Properties
        IPS_SetProperty($instanceID, 'Price', 1.0);
        IPS_SetProperty($instanceID, 'Source', $variableID);
        IPS_SetProperty($instanceID, 'Active', true);
        IPS_SetProperty($instanceID, 'CalculateCost', true);
        IPS_SetProperty($instanceID, 'Level', LVL_YEAR);

        IPS_ApplyChanges($instanceID);

        //Not use because it calls in ApplyChanges
        //BSZ_Calculate($instanceID);

        //Assert
        $this->assertEquals(3.36, GetValue(IPS_GetObjectIDByIdent('CostThisPeriod', $instanceID)));
        $this->assertEquals(6.72, GetValue(IPS_GetObjectIDByIdent('CostLastPeriod', $instanceID)));
        $this->assertEquals(14.016, GetValue(IPS_GetObjectIDByIdent('PredictionThisPeriod', $instanceID)));
    }
    public function testCalculationComplete()
    {
        //Variables
        $variableID = IPS_CreateVariable(0);
        $archivID = $this->archiveControlID;
        $instanceID = $this->betriebsstundenzaehlerID;

        //Set archived settings
        AC_SetLoggingStatus($archivID, $variableID, true);

        //Custom Time for Testing
        BSZ_setTime($instanceID, strtotime('March 29 2021 12:00'));

        //Add archive data
        $aggregationPeriodMonth = [
            [
                'Avg'       => 0.5,
                'Duration'  => 14 * 24 * 60 * 60,
                'Max'       => 0,
                'MaxTime'   => 0,
                'Min'       => 0,
                'MinTime'   => 0,
                'TimeStamp' => strtotime('January 10 2021 00:00:00'),
            ],
            [
                'Avg'       => 0.5,
                'Duration'  => 14 * 24 * 60 * 60,
                'Max'       => 0,
                'MaxTime'   => 0,
                'Min'       => 0,
                'MinTime'   => 0,
                'TimeStamp' => strtotime('February 2021 00:00:00'),
            ],
            [
                'Avg'       => 1,
                'Duration'  => 28 * 24 * 60 * 60,
                'Max'       => 0,
                'MaxTime'   => 0,
                'Min'       => 0,
                'MinTime'   => 0,
                'TimeStamp' => strtotime('February 24 1998 00:00:00'),
            ]
        ];
        AC_StubsAddAggregatedValues($archivID, $variableID, 4, $aggregationPeriodMonth);

        //Properties
        IPS_SetProperty($instanceID, 'Price', 1.0);
        IPS_SetProperty($instanceID, 'Source', $variableID);
        IPS_SetProperty($instanceID, 'Active', true);
        IPS_SetProperty($instanceID, 'CalculateCost', true);
        IPS_SetProperty($instanceID, 'Level', LVL_COMPLETE);

        IPS_ApplyChanges($instanceID);

        //Not use because it calls in ApplyChanges
        //BSZ_Calculate($instanceID);

        //Assert
        $this->assertEquals(10.08, GetValue(IPS_GetObjectIDByIdent('CostThisPeriod', $instanceID)));
    }
}