<?php

declare(strict_types=1);

namespace App\Models;

use App\Constants\HeartbeatKeys;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Heartbeat extends Model
{
    use HasFactory;

    protected $fillable = [
        HeartbeatKeys::DB_APPLICATION_KEY,
        HeartbeatKeys::DB_HEARTBEAT_KEY,
        HeartbeatKeys::DB_UNHEALTHY_AFTER_MINUTES,
        HeartbeatKeys::DB_LAST_CHECK_IN,
    ];

    protected $casts = [
        HeartbeatKeys::DB_LAST_CHECK_IN => 'datetime',
    ];

    public function isUnhealthy(): bool
    {
        $threshold = $this->{HeartbeatKeys::DB_LAST_CHECK_IN}->addMinutes($this->{HeartbeatKeys::DB_UNHEALTHY_AFTER_MINUTES});
        return Carbon::now()->gt($threshold);
    }

    public function scopeUnhealthy($query)
    {
        $now = Carbon::now();

        return $query->whereRaw(
            $this->getDatabaseSpecificDateAddExpression(HeartbeatKeys::DB_LAST_CHECK_IN, HeartbeatKeys::DB_UNHEALTHY_AFTER_MINUTES),
            [$now->toDateTimeString()]
        );
    }

    protected function getDatabaseSpecificDateAddExpression(string $dateColumn, string $minutesColumn): string
    {
        $driver = DB::connection()->getDriverName();

        return match ($driver) {
            'sqlite' => "datetime({$dateColumn}, '+' || {$minutesColumn} || ' minutes') < ?",
            'pgsql' => "({$dateColumn} + ({$minutesColumn} || ' minutes')::interval) < ?",
            'sqlsrv' => "DATEADD(minute, {$minutesColumn}, {$dateColumn}) < ?",
            default => "DATE_ADD({$dateColumn}, INTERVAL {$minutesColumn} MINUTE) < ?"
        };
    }
}
