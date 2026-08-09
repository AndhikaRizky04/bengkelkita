<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspectionTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'inspection_category_id',
        'name',
        'sort_order',
    ];

    public function inspectionCategory()
    {
        return $this->belongsTo(InspectionCategory::class);
    }
}
