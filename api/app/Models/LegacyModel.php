<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

abstract class LegacyModel extends Model
{
    use HasFactory;

    protected $connection = 'legacy';

    protected $guarded = [];

    public $timestamps = false;
}
