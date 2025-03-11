<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class Heartbeat extends Model
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

    /**
     * Scope a query to only include unhealthy heartbeats.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeUnhealthy($query)
    {
        $now = Carbon::now();

        return $query->where(function ($query) use ($now) {
            $query->whereRaw('datetime(last_check_in, \'+\' || unhealthy_after_minutes || \' minutes\') < ?', [$now->toDateTimeString()]);
        });
    }
}
