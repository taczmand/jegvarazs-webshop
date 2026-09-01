<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use LogsActivity;
    
    protected $fillable = [
        'title',
        'description',
        'folder',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'created_by',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
