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
        IPS_SetProperty($instanceID, 'Price', 2.0);
        IPS_SetProperty($instanceID, 'Source', $variableID);
        IPS_SetProperty($instanceID, 'Active', true);
        IPS_SetProperty($instanceID, 'CalculateCost', true);

        IPS_ApplyChanges($instanceID);

        //Not use because it calls in ApplyChanges
        //BSZ_Calculate($instanceID);

        //Assert
        $this->assertEquals(6, GetValue(IPS_GetObjectIDByIdent('OperatingHours', $instanceID)));
        $this->assertEquals(0.24, GetValue(IPS_GetObjectIDByIdent('CostLastPeriod', $instanceID)));
        $this->assertEquals(0.12, GetValue(IPS_GetObjectIDByIdent('CostThisPeriod', $instanceID)));
        $this->assertEquals(0.24, GetValue(IPS_GetObjectIDByIdent('PredictionThisPeriod', $instanceID)));

        /*
        Manuelle Berechnung:
        Erwartete Daten:
            Operating Hours:			6h 		(0,5 * 12);
            Cost Last Period:			0.24 €	(0,5 * 24 *0,02€);
            Cost This Period:			0.12 €	(6 * 0.02€);
            Prediction This Period: 	0.24 €	(0,12 /((44020800 - 43977600) / (24*60*60) *100)*100);
         */
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
                'Duration'  => 3 * 24 * 60 * 60 + 12 * 60 * 60,
                'Max'       => 0,
                'MaxTime'   => 0,
                'Min'       => 0,
                'MinTime'   => 0,
                'TimeStamp' => strtotime('May 4 2020 00:00:00'),
            ]
        ];
        AC_StubsAddAggregatedValues($archivID, $variableID, 2, $aggregationPeriodWeek);

        //Properties
        IPS_SetProperty($instanceID, 'Price', 2.0);
        IPS_SetProperty($instanceID, 'Source', $variableID);
        IPS_SetProperty($instanceID, 'Active', true);
        IPS_SetProperty($instanceID, 'CalculateCost', true);
        IPS_SetProperty($instanceID, 'Level', LVL_WEEK);

        IPS_ApplyChanges($instanceID);

        //Not use because it calls in ApplyChanges
        //BSZ_Calculate($instanceID);

        //Assert
        $this->assertEquals(84, GetValue(IPS_GetObjectIDByIdent('OperatingHours', $instanceID)));
        $this->assertEquals(1.68, GetValue(IPS_GetObjectIDByIdent('CostThisPeriod', $instanceID)));
        $this->assertEquals(1.68, GetValue(IPS_GetObjectIDByIdent('CostLastPeriod', $instanceID)));
        $this->assertEquals(3.36, GetValue(IPS_GetObjectIDByIdent('PredictionThisPeriod', $instanceID)));

        /*
        Manuelle Berechnung:
        Erwartete Daten:
            Operating Hours:			84h		(1* (3 * 24 + 12));
            Cost Last Period:			1.68 €	(0,5 *(7*24))*0,02€;
            Cost This Period:			1.68 €	(84 * 0,02€);
            Prediction This Period: 	3,36 €	(1,68/((1588852800 - 1588550400) / (7 * 24 * 60 * 60) *100)*100);
         */
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
                'Duration'  => 31 * 24 * 60 * 60,
                'Max'       => 0,
                'MaxTime'   => 0,
                'Min'       => 0,
                'MinTime'   => 0,
                'TimeStamp' => strtotime('January 1 2021 00:00:00'),
            ],
            [
                'Avg'       => 0.5,
                'Duration'  => 14 * 24 * 60 * 60,
                'Max'       => 0,
                'MaxTime'   => 0,
                'Min'       => 0,
                'MinTime'   => 0,
                'TimeStamp' => strtotime('February 1 2021 00:00:00'),
            ]
        ];
        AC_StubsAddAggregatedValues($archivID, $variableID, 3, $aggregationPeriodMonth);

        //Properties
        IPS_SetProperty($instanceID, 'Price', 2.0);
        IPS_SetProperty($instanceID, 'Source', $variableID);
        IPS_SetProperty($instanceID, 'Active', true);
        IPS_SetProperty($instanceID, 'CalculateCost', true);
        IPS_SetProperty($instanceID, 'Level', LVL_MONTH);

        IPS_ApplyChanges($instanceID);

        //Not use because it calls in ApplyChanges
        //BSZ_Calculate($instanceID);

        //Assert
        $this->assertEquals(168, GetValue(IPS_GetObjectIDByIdent('OperatingHours', $instanceID)));
        $this->assertEquals(3.36, GetValue(IPS_GetObjectIDByIdent('CostThisPeriod', $instanceID)));
        $this->assertEquals(7.44, GetValue(IPS_GetObjectIDByIdent('CostLastPeriod', $instanceID)));
        $this->assertEquals(6.72, GetValue(IPS_GetObjectIDByIdent('PredictionThisPeriod', $instanceID)));

        /*
        Manuelle Berechnung:
        Erwartete Daten:
            Operating Hours:			372h    (14 * 24 * 0,5);
            Cost Last Period:			7.44 €  (0,02 * (31 * 24));
            Cost This Period:			3.36 €  (372 * 0.02);
            Prediction This Period: 	6.72 €  (3,36 / ((1613347200 - 1612137600) / (28 * 24 * 60 * 60) * 100) * 100);
         */
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
        BSZ_setTime($instanceID, strtotime('March 29 2021 13:00'));

        //Add archive data
        $aggregationPeriodYear = [
            [
                'Avg'       => 0.7,
                'Duration'  => 366 * 24 * 60 * 60,
                'Max'       => 0,
                'MaxTime'   => 0,
                'Min'       => 0,
                'MinTime'   => 0,
                'TimeStamp' => strtotime('January 01 2020 00:00:00'),
            ],
            [
                'Avg'       => 0.25,
                'Duration'  => 31 * 24 * 3600 + 28 * 24 * 3600 + 28 * 24 * 3600 + 13 * 3600,
                'Max'       => 0,
                'MaxTime'   => 0,
                'Min'       => 0,
                'MinTime'   => 0,
                'TimeStamp' => strtotime('January 1 2021 00:00:00'),
            ]
        ];
        AC_StubsAddAggregatedValues($archivID, $variableID, 4, $aggregationPeriodYear);

        //Properties
        IPS_SetProperty($instanceID, 'Price', 2.0);
        IPS_SetProperty($instanceID, 'Source', $variableID);
        IPS_SetProperty($instanceID, 'Active', true);
        IPS_SetProperty($instanceID, 'CalculateCost', true);
        IPS_SetProperty($instanceID, 'Level', LVL_YEAR);

        IPS_ApplyChanges($instanceID);

        //Not use because it calls in ApplyChanges
        //BSZ_Calculate($instanceID);

        //Assert
        $this->assertEquals(525.25, GetValue(IPS_GetObjectIDByIdent('OperatingHours', $instanceID)));
        $this->assertEquals(10.505, GetValue(IPS_GetObjectIDByIdent('CostThisPeriod', $instanceID)));
        $this->assertEquals(122.976, GetValue(IPS_GetObjectIDByIdent('CostLastPeriod', $instanceID)));
        $this->assertEquals(43.8, GetValue(IPS_GetObjectIDByIdent('PredictionThisPeriod', $instanceID)));

        /*
        Manuelle Berechnung:
        Erwartete Daten:
            Operating Hours:			525,25h     ((31 * 24 + 28 * 24 + 28 * 24 + 12 +1) * 0,25)
            Cost Last Period:			122,976 €   ((366 * 24) * 0,07);
            Cost This Period:			10.505 €    (525,25 * 0,02);
            Prediction This Period: 	43,8 €      (10,505 / ((1617022800 - 1609459200) / (31536000) * 100) * 100);
         */
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
        $aggregationPeriodComplete = [
            [
                'Avg'       => 0.3,
                'Duration'  => 365 * 24 * 60 * 60,
                'Max'       => 0,
                'MaxTime'   => 0,
                'Min'       => 0,
                'MinTime'   => 0,
                'TimeStamp' => strtotime('January 1 1998 00:00:00'),
            ],
            [
                'Avg'       => 0.5,
                'Duration'  => 365 * 24 * 60 * 60,
                'Max'       => 0,
                'MaxTime'   => 0,
                'Min'       => 0,
                'MinTime'   => 0,
                'TimeStamp' => strtotime('January 1 2020 00:00:00'),
            ],
            [
                'Avg'       => 0.5,
                'Duration'  => 87 * 24 * 60 * 60,
                'Max'       => 0,
                'MaxTime'   => 0,
                'Min'       => 0,
                'MinTime'   => 0,
                'TimeStamp' => strtotime('January 1 2021 00:00:00'),
            ]
        ];
        AC_StubsAddAggregatedValues($archivID, $variableID, 4, $aggregationPeriodComplete);

        //Properties
        IPS_SetProperty($instanceID, 'Price', 2.0);
        IPS_SetProperty($instanceID, 'Source', $variableID);
        IPS_SetProperty($instanceID, 'Active', true);
        IPS_SetProperty($instanceID, 'CalculateCost', true);
        IPS_SetProperty($instanceID, 'Level', LVL_COMPLETE);

        IPS_ApplyChanges($instanceID);

        //Not use because it calls in ApplyChanges
        //BSZ_Calculate($instanceID);

        //Assert
        $this->assertEquals(8052, GetValue(IPS_GetObjectIDByIdent('OperatingHours', $instanceID)));
        $this->assertEquals(161.04, GetValue(IPS_GetObjectIDByIdent('CostThisPeriod', $instanceID)));

        /*
        Manuelle Berechnung:
        Erwartete Daten:
            Operating Hours:			8052h       (365 * 24 * 0,3 + 365 * 24 * 0,5 + 87 * 24 * 0,5);
            Cost This Period:			161,04 €    (8052 * 0,02);
         */
    }

    public function testDynamicCosts()
    {
        //Variables
        $variableID = IPS_CreateVariable(0);
        $priceID = IPS_CreateVariable(2);
        $archivID = $this->archiveControlID;
        $instanceID = $this->betriebsstundenzaehlerID;
        IPS_EnableDebug($instanceID, 600);

        //Set archived settings
        AC_SetLoggingStatus($archivID, $variableID, true);
        AC_SetLoggingStatus($archivID, $priceID, true);

        //Custom Time for Testing
        BSZ_setTime($instanceID, strtotime('March 29 2021 12:00'));

        //Add archive data for source
        $aggregationPeriodDay = [
            [
                'Avg'       => 0.3,
                'Duration'  => 24 * 60 * 60,
                'Max'       => 0,
                'MaxTime'   => 0,
                'Min'       => 0,
                'MinTime'   => 0,
                'TimeStamp' => strtotime('March 28 2021 00:00:00'),
            ],
            [
                'Avg'       => 0.3,
                'Duration'  => 12 * 60 * 60,
                'Max'       => 0,
                'MaxTime'   => 0,
                'Min'       => 0,
                'MinTime'   => 0,
                'TimeStamp' => strtotime('March 29 2021 00:00:00'),
            ]
        ];
        AC_StubsAddAggregatedValues($archivID, $variableID, 1, $aggregationPeriodDay);

        //Add archive data for price
        $aggregationPeriodDay = [
            [
                'Avg'       => 0.34,
                'Duration'  => 24 * 60 * 60,
                'Max'       => 0,
                'MaxTime'   => 0,
                'Min'       => 0,
                'MinTime'   => 0,
                'TimeStamp' => strtotime('March 28 2021 00:00:00'),
            ],
            [
                'Avg'       => 0.73,
                'Duration'  => 12 * 60 * 60,
                'Max'       => 0,
                'MaxTime'   => 0,
                'Min'       => 0,
                'MinTime'   => 0,
                'TimeStamp' => strtotime('March 29 2021 00:00:00'),
            ]
        ];
        AC_StubsAddAggregatedValues($archivID, $priceID, 1, $aggregationPeriodDay);

        //Properties
        IPS_SetProperty($instanceID, 'PriceDynamic', $priceID);
        IPS_SetProperty($instanceID, 'Source', $variableID);
        IPS_SetProperty($instanceID, 'Active', true);
        IPS_SetProperty($instanceID, 'CalculateCost', true);
        IPS_SetProperty($instanceID, 'PriceType', 'Dynamic');
        IPS_SetProperty($instanceID, 'Level', LVL_DAY);

        IPS_ApplyChanges($instanceID);

        //Not use because it calls in ApplyChanges
        //BSZ_Calculate($instanceID);

        //Assert
        $this->assertEquals(3.6, GetValue(IPS_GetObjectIDByIdent('OperatingHours', $instanceID)));
        $this->assertEquals(94.608, GetValue(IPS_GetObjectIDByIdent('CostThisPeriod', $instanceID)));
        $this->assertEquals(88.128, GetValue(IPS_GetObjectIDByIdent('CostLastPeriod', $instanceID)));
        $this->assertEquals(189.216, GetValue(IPS_GetObjectIDByIdent('PredictionThisPeriod', $instanceID)));

        /**
         * Manuelle Berechnung:
         * Erwartete Daten:
         *      Operating Hours:        3.6      (12 * 0,3);
         *      Cost This Periode:      94.608   ((12 * 60 * 60 * 0,3 * 0,73)
         *      Cost Last Periode:      88.128   (24 * 0,3 * 0,34)
         *      Prediction This Period: 189.216  (94,608 / (43200/ 86400 * 100) *100 )
         */
    }
}