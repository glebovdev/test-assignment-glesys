<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Heartbeat extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_key',
        'heartbeat_key',
        'unhealthy_after_minutes',
        'last_check_in',
    ];

    protected $casts = [
        'last_check_in' => 'datetime',
    ];

    public function isUnhealthy(): bool
    {
        $threshold = $this->last_check_in->addMinutes($this->unhealthy_after_minutes);
        return Carbon::now()->gt($threshold);
    }

    public function scopeUnhealthy($query)
    {
        $now = Carbon::now();

        return $query->whereRaw(
            $this->getDatabaseSpecificDateAddExpression('last_check_in', 'unhealthy_after_minutes'),
            [$now->toDateTimeString()]
        );
    }

    protected function getDatabaseSpecificDateAddExpression($dateColumn, $minutesColumn): string
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
