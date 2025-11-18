<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistResponseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'checklist_response_id',
        'checklist_item_id',
        'score',
        'comment',
    ];

    public function response(): BelongsTo
    {
        return $this->belongsTo(ChecklistResponse::class, 'checklist_response_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(ChecklistItem::class, 'checklist_item_id');
    }
}
