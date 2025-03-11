<?php

namespace App\Constants;

interface HeartbeatKeys
{
    // GraphQL Input/Response keys (camelCase)
    public const APPLICATION_KEY = 'applicationKey';
    public const HEARTBEAT_KEY = 'heartbeatKey';
    public const UNHEALTHY_AFTER_MINUTES = 'unhealthyAfterMinutes';
    public const LAST_CHECK_IN = 'lastCheckIn';
    
    // Database column names (snake_case)
    public const DB_APPLICATION_KEY = 'application_key';
    public const DB_HEARTBEAT_KEY = 'heartbeat_key';
    public const DB_UNHEALTHY_AFTER_MINUTES = 'unhealthy_after_minutes';
    public const DB_LAST_CHECK_IN = 'last_check_in';
    
    // Common keys
    public const INPUT = 'input';
    public const HEARTBEAT = 'heartbeat'; 
    public const APPLICATION_KEYS = 'applicationKeys';
} 