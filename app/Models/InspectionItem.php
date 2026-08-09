<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspectionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'inspection_id',
        'inspection_template_id',
        'name',
        'category',
        'condition',
        'notes',
    ];

    public function inspection()
    {
        return $this->belongsTo(Inspection::class);
    }

    public function inspectionTemplate()
    {
        return $this->belongsTo(InspectionTemplate::class);
    }
}
