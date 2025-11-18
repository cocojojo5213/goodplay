<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChecklistResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'checklist_version_id',
        'staff_id',
        'response_date',
        'completed_at',
    ];

    protected $casts = [
        'response_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function version(): BelongsTo
    {
        return $this->belongsTo(ChecklistVersion::class, 'checklist_version_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ChecklistResponseItem::class);
    }
}
