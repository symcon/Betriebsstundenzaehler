<?php

declare(strict_types=1);

include_once __DIR__ . '/stubs/Validator.php';

class BetriebsstundenzaehlerValidationTest extends TestCaseSymconValidation
{
    public function testValidateBetriebsstundenzaehler(): void
    {
        $this->validateLibrary(__DIR__ . '/..');
    }

    public function testValidateOperatingHoursCounterModule(): void
    {
        $this->validateModule(__DIR__ . '/../OperatingHoursCounter');
    }
}