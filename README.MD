# Rittal CMiii PDU check plugin for nagios

This plugin allows you to monitor your rittal CMiii PDU's. The output is formatted so that Nagios can also use it to create performance graphs. You can monitor the following:

- Whole PDU unit
  - Status
  - Total kWh used
  - Total output Wattage
- Phase (L1, L2 and L3)
  - Status
  - Output Wattage
  - Input Voltage

# Installation

1 - upload check_rittal_cmiii.php to /usr/local/nagios/libexec and chmod +x

2 - upload the contents of the nagios-import directory to /usr/local/nagios/etc/import and import the config files in the core config manager

# Example host config

```
###############################################################################
#
# Hosts configuration file
#
# Created by: Nagios CCM 3.0.3
# Date:       2019-09-24 15:09:20
# Version:    Nagios Core 4.x
#
# --- DO NOT EDIT THIS FILE BY HAND ---
# Nagios CCM will overwrite all manual settings during the next update if you
# would like to edit files manually, place them in the 'static' directory or
# import your configs into the CCM by placing them in the 'import' directory.
#
###############################################################################

define host {
    host_name         Rack A PDU
    use               rittal-pdu
    address           192.168.1.2
    hostgroups        Pdus, datacenter
    contact_groups    Networking
    register          1
}

###############################################################################
#
# Hosts configuration file
#
# END OF FILE
#
###############################################################################
```

```
# Example services config

###############################################################################
#
# Services configuration file
#
# Created by: Nagios CCM 3.0.3
# Date:       2019-09-24 15:11:14
# Version:    Nagios Core 4.x
#
# --- DO NOT EDIT THIS FILE BY HAND ---
# Nagios CCM will overwrite all manual settings during the next update if you
# would like to edit files manually, place them in the 'static' directory or
# import your configs into the CCM by placing them in the 'import' directory.
#
###############################################################################

define service {
    host_name                Rack A PDU
    service_description      kWh
    servicegroups            Pdus, datacenter
    check_command            check_rittal_cmiii_unit_totalkwh!
    max_check_attempts       5
    check_interval           5
    retry_interval           1
    check_period             xi_timeperiod_24x7
    notification_interval    1440
    notification_period      24x7
    notification_options     w,c,
    notifications_enabled    0
    contact_groups           Networking
    register                 1
}

define service {
    host_name                Rack A PDU
    service_description      L1 output wattage
    servicegroups            Pdus, datacenter
    check_command            check_rittal_cmiii_phase_outputwattage!L1!1200!2500!
    max_check_attempts       5
    check_interval           5
    retry_interval           1
    check_period             xi_timeperiod_24x7
    notification_interval    1440
    notification_period      24x7
    notification_options     w,c,
    notifications_enabled    1
    contact_groups           Networking
    register                 1
}

define service {
    host_name                Rack A PDU
    service_description      L1 status
    servicegroups            Pdus, datacenter
    check_command            check_rittal_cmiii_phase_status!L1
    max_check_attempts       5
    check_interval           5
    retry_interval           1
    check_period             xi_timeperiod_24x7
    notification_interval    1440
    notification_period      24x7
    notification_options     w,c,
    notifications_enabled    1
    contact_groups           Networking
    register                 1
}

define service {
    host_name                Rack A PDU
    service_description      L1 Voltage
    servicegroups            Pdus, datacenter
    check_command            check_rittal_cmiii_phase_inputvoltage!L1!232!
    max_check_attempts       5
    check_interval           5
    retry_interval           1
    check_period             xi_timeperiod_24x7
    notification_interval    1440
    notification_period      24x7
    notification_options     w,c,
    notifications_enabled    1
    contact_groups           Networking
    register                 1
}

define service {
    host_name                Rack A PDU
    service_description      L2 output wattage
    servicegroups            Pdus, datacenter
    check_command            check_rittal_cmiii_phase_outputwattage!L2!1200!2500!
    max_check_attempts       5
    check_interval           5
    retry_interval           1
    check_period             xi_timeperiod_24x7
    notification_interval    1440
    notification_period      24x7
    notification_options     w,c,
    notifications_enabled    1
    contact_groups           Networking
    register                 1
}

define service {
    host_name                Rack A PDU
    service_description      L2 status
    servicegroups            Pdus, datacenter
    check_command            check_rittal_cmiii_phase_status!L2
    max_check_attempts       5
    check_interval           5
    retry_interval           1
    check_period             xi_timeperiod_24x7
    notification_interval    1440
    notification_period      24x7
    notification_options     w,c,
    notifications_enabled    1
    contact_groups           Networking
    register                 1
}

define service {
    host_name                Rack A PDU
    service_description      L2 Voltage
    servicegroups            Pdus, datacenter
    check_command            check_rittal_cmiii_phase_inputvoltage!L2!232!
    max_check_attempts       5
    check_interval           5
    retry_interval           1
    check_period             xi_timeperiod_24x7
    notification_interval    1440
    notification_period      24x7
    notification_options     w,c,
    notifications_enabled    1
    contact_groups           Networking
    register                 1
}

define service {
    host_name                Rack A PDU
    service_description      L3 output wattage
    servicegroups            Pdus, datacenter
    check_command            check_rittal_cmiii_phase_outputwattage!L3!1200!2500!
    max_check_attempts       5
    check_interval           5
    retry_interval           1
    check_period             xi_timeperiod_24x7
    notification_interval    1440
    notification_period      24x7
    notification_options     w,c,
    notifications_enabled    1
    contact_groups           Networking
    register                 1
}

define service {
    host_name                Rack A PDU
    service_description      L3 status
    servicegroups            Pdus, datacenter
    check_command            check_rittal_cmiii_phase_status!L3
    max_check_attempts       5
    check_interval           5
    retry_interval           1
    check_period             xi_timeperiod_24x7
    notification_interval    1440
    notification_period      24x7
    notification_options     w,c,
    notifications_enabled    1
    contact_groups           Networking
    register                 1
}

define service {
    host_name                Rack A PDU
    service_description      L3 Voltage
    servicegroups            Pdus, datacenter
    check_command            check_rittal_cmiii_phase_inputvoltage!L3!232!
    max_check_attempts       5
    check_interval           5
    retry_interval           1
    check_period             xi_timeperiod_24x7
    notification_interval    1440
    notification_period      24x7
    notification_options     w,c,
    notifications_enabled    1
    contact_groups           Networking
    register                 1
}

define service {
    host_name                Rack A PDU
    service_description      Output wattage
    servicegroups            Pdus, datacenter
    check_command            check_rittal_cmiii_unit_outputwattage!5000!7500!
    max_check_attempts       5
    check_interval           5
    retry_interval           1
    check_period             xi_timeperiod_24x7
    notification_interval    1440
    notification_period      24x7
    notification_options     w,c,
    notifications_enabled    1
    contact_groups           Networking
    register                 1
}

###############################################################################
#
# Services configuration file
#
# END OF FILE
#
###############################################################################
```
