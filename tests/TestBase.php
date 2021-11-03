<?php

declare(strict_types=1);

define('VAR_BOOL', 0);
define('VAR_INT', 1);
define('VAR_FLOAT', 2);
define('VAR_STRING', 3);

include_once __DIR__ . '/stubs/GlobalStubs.php';
include_once __DIR__ . '/stubs/KernelStubs.php';
include_once __DIR__ . '/stubs/ModuleStubs.php';
include_once __DIR__ . '/stubs/MessageStubs.php';
include_once __DIR__ . '/stubs/ConstantStubs.php';

use PHPUnit\Framework\TestCase;

class TestBase extends TestCase
{
    protected $ArchiveControlID;
    protected $BetriebsstundenzählerID;

    protected function setUp(): void
    {
        //Reset
        IPS\Kernel::reset();

        //Register our core stubs for testing
        IPS\ModuleLoader::loadLibrary(__DIR__ . '/stubs/CoreStubs/library.json');

        //Register our library we need for testing
        IPS\ModuleLoader::loadLibrary(__DIR__ . '/../library.json');

        //Register required profiles
        if (!IPS\ProfileManager::variableProfileExists('~Euro')) {
            IPS\ProfileManager::createVariableProfile('~Euro', 2);
        }
        $this->archiveControlID = IPS_CreateInstance('{43192F0B-135B-4CE7-A0A7-1475603F3060}');
        $this->betriebsstundenzaehlerID = IPS_CreateInstance('{37569A16-8496-EF9B-A322-FC972D605D90}');

        parent::setUp();
    }
}