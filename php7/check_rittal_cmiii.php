#!/usr/bin/php
<?php

$checkRittal = new checkRittal();

$checkRittal->check(getopt("hD:C:H:W:M:V:", ["help"]));

class checkRittal
{
    const STATE_OK       = 0;
    const STATE_WARNING  = 1;
    const STATE_CRITICAL = 2;
    const STATE_UNKNOWN  = 3;

    private $communityString = 'public';

    private $oid = [
        'unit' => [
            'status'             => '.1.3.6.1.4.1.2606.7.4.2.2.1.11.1.9',
            'totaloutputwattage' => '.1.3.6.1.4.1.2606.7.4.2.2.1.11.1.12',
            'totalkwh'           => '.1.3.6.1.4.1.2606.7.4.2.2.1.11.1.20',
        ],
        'l1' => [
            'status'             => '.1.3.6.1.4.1.2606.7.4.2.2.1.11.1.32',
            'inputvoltage'       => '.1.3.6.1.4.1.2606.7.4.2.2.1.11.1.26',
            'outputwattage'      => '.1.3.6.1.4.1.2606.7.4.2.2.1.11.1.45',
        ],
        'l2' => [
            'status'             => '.1.3.6.1.4.1.2606.7.4.2.2.1.11.1.65',
            'inputvoltage'       => '.1.3.6.1.4.1.2606.7.4.2.2.1.11.1.59',
            'outputwattage'      => '.1.3.6.1.4.1.2606.7.4.2.2.1.11.1.78',
        ],
        'l3' => [
            'status'             => '.1.3.6.1.4.1.2606.7.4.2.2.1.11.1.98',
            'inputvoltage'       => '.1.3.6.1.4.1.2606.7.4.2.2.1.11.1.92',
            'outputwattage'      => '.1.3.6.1.4.1.2606.7.4.2.2.1.11.1.111',
        ],
    ];

    function __construct()
    {
        snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
    }

    /**
     * Entry function that switches to the correct subfunction depending on the device and check
     *
     * @param  array $options array containing cli options
     */

    public function check(array $options) :void
    {
        if(isset($options['h']) || isset($options['help']))
        {
            $this->help();
        }

        $device = strtolower($options['D']);
        $check  = strtolower($options['C']);

        try {
            $snmpValue = snmp2_get($options['H'], $this->communityString, $this->oid[$device][$check]);
        } catch (Exception $e) {
            echo "Check Failed";
            exit(self::STATE_UNKNOWN);
        }

        switch($device)
        {
            case "unit":
            {
                switch ($check)
                {
                    case "status":
                        $this->checkUnitStatus($snmpValue);
                    break;
                    case "totaloutputwattage":
                        $this->checkUnitOutputWattage($snmpValue, $options['W'], $options['M']);
                    break;
                    case "totalkwh":
                        $this->checkUnitKwh($snmpValue);
                    break;
                    default:
                        exit(self::STATE_UNKNOWN);
                }
            }
            break;
            case "l1":
            case "l2":
            case "l3":
            {
                $device = strtoupper($device);

                switch ($check)
                {
                    case "status":
                        $this->checkPhaseStatus($device, $snmpValue);
                    break;
                    case "inputvoltage":
                        $this->checkPhaseInputVoltage($device, $snmpValue, $options['V']);
                    break;
                    case "outputwattage":
                        $this->checkPhaseOutputWattage($device, $snmpValue, $options['W'], $options['M']);
                    break;
                    default:
                        exit(self::STATE_UNKNOWN);
                }
            }
            break;
            default:
                exit(self::STATE_UNKNOWN);
        }
    }

    /**
     * Checks the status of the whole pdu
     * @param  int    $status      status code
     *                             1   notAvail
     *                             4   ok
     *                             6   highWarn
     *                             7   lowAlarm
     *                             8   highAlarm
     *                             9   lowWarn
     */

    private function checkUnitStatus(int $status) :void
    {
        switch($status)
        {
            case 4:
            echo "OK";
            exit(self::STATE_OK);

            case 7:
            case 9:
            echo "WARNING";
            exit(self::STATE_WARNING);

            case 1:
            case 6:
            case 8:
            echo "CRITICAL";
            exit(self::STATE_CRITICAL);

            case 1:
            default:
            echo "UNKNOWN/UNREACHABLE";
            exit(self::STATE_UNKNOWN);
        }
    }

    /**
     * Checks the total wattage output of the pdu
     * @param int $wattage        The current wattage output
     * @param int $warningWattage Wattage that should raise an error
     * @param int $maxWattage     Critical wattage
     */

    private function checkUnitOutputWattage(int $wattage, int $warningWattage, int $maxWattage) :void
    {
        switch (true)
        {
                case $wattage < $warningWattage:
                echo "{$wattage}W|'Watt'={$wattage};$warningWattage;$maxWattage;0;10000";
                exit(self::STATE_OK);

                case $wattage < $maxWattage:
                echo "{$wattage}W|'Watt'={$wattage};$warningWattage;$maxWattage;0;10000";
                exit(self::STATE_WARNING);

                case $wattage >= $maxWattage:
                echo "{$wattage}W|'Watt'={$wattage};$warningWattage;$maxWattage;0;10000";
                exit(self::STATE_CRITICAL);

                default:
                echo "0W|'Watt'={$wattage};$warningWattage;$maxWattage;0;10000";
                exit(self::STATE_UNKNOWN);
        }
    }

    /**
     * Checks the total kWh provided by the pdu
     * @param int $kwh provided kWh
     */

    private function checkUnitKwh(int $kwh) : void
    {
            $kwh = $kwh/10;
            echo "{$kwh}kWh|'Kwh'={$kwh};;;0;10000";
            exit(self::STATE_OK);
    }

    /**
     * Checks the status of a given phase
     * @param string $phase  Name of the phase
     * @param int    $status status code
     *                       1   notAvail
     *                       4   ok
     *                       6   highWarn
     *                       7   lowAlarm
     *                       8   highAlarm
     *                       9   lowWarn
     */

    private function checkPhaseStatus(string $phase, int $status) :void
    {
        switch($status)
        {
            case 4:
            echo "{$phase} OK";
            exit(self::STATE_OK);

            case 7:
            case 9:
            echo "{$phase} WARNING";
            exit(self::STATE_WARNING);

            case 1:
            case 6:
            case 8:
            echo "{$phase} CRITICAL";
            exit(self::STATE_CRITICAL);

            case 1:
            default:
            echo "{$phase} UNKNOWN/UNREACHABLE";
            exit(self::STATE_UNKNOWN);
        }
    }

    /**
     * Checks the input voltage of a given phase, checks the voltage against the provided mean voltage
     * @param string $phase       Name of the phase
     * @param int    $voltage     Input voltage
     * @param int    $meanVoltage Mean expected voltage
     */

    private function checkPhaseInputVoltage(string $phase, int $voltage, int $meanVoltage) :void
    {
        $voltage = $voltage / 10;
        $voltageDifference = abs($voltage - $meanVoltage);

        switch (true)
        {
                case $voltageDifference < 5:
                echo "{$phase} {$voltage}|'Voltage'={$voltage};;;0;250";
                exit(self::STATE_OK);

                case $voltageDifference < 10:
                echo "{$phase} {$voltage}|'Voltage'={$voltage};;;0;250";
                exit(self::STATE_WARNING);

                case $voltageDifference >= 10:
                echo "{$phase} {$voltage}|'Voltage'={$voltage};;;0;250";
                exit(self::STATE_CRITICAL);

                default:
                echo "0V|'Voltage'={$voltage};;;0;250";
                exit(self::STATE_UNKNOWN);
        }
    }

    /**
     * Checks the output wattage of a given phase.
     * @param string $phase          Name of the phase
     * @param int    $wattage        Current output wattage
     * @param int    $warningWattage Wattage that should raise an error
     * @param int    $maxWattage     Critical wattage
     */

    private function checkPhaseOutputWattage(string $phase, int $wattage, int $warningWattage, int $maxWattage) :void
    {
        switch (true)
        {
                case $wattage < $warningWattage:
                echo "{$phase} {$wattage}W|'Watt'={$wattage};$warningWattage;$maxWattage;0;3500";
                exit(self::STATE_OK);

                case $wattage < $maxWattage:
                echo "{$phase} {$wattage}W|'Watt'={$wattage};$warningWattage;$maxWattage;0;3500";
                exit(self::STATE_WARNING);

                case $wattage >= $maxWattage:
                echo "{$phase} {$wattage}W|'Watt'={$wattage};$warningWattage;$maxWattage;0;3500";
                exit(self::STATE_CRITICAL);

                default:
                echo "0W|'Watt'={$wattage};$warningWattage;$maxWattage;0;3500";
                exit(self::STATE_UNKNOWN);
        }
    }

    /**
     * Displays the help information
     */

    private function help() :void
    {
        echo "
        Check plugin for rittal pdus

        // Base parameters
        -H hostname
        -D device to check
        -C check to run

        // When checking wattages
        -W Warning wattage
        -M Max wattage

        // When checking voltages
        -V Mean expected voltage

        \010\010\010\010\010\010\010\010";
        exit;
    }
}
