<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsInsight extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'metric',
        'value',
        'trend',
        'note',
        'created_by',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
