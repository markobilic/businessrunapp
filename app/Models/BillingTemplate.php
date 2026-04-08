<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Organizer;
use App\Models\TemplateType;

class BillingTemplate extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'template_type',
        'template_type_id',
        'template_name',
        'template_content',
        'organizer_id',
    ];

    public function templateType()
    {
        return $this->belongsTo(TemplateType::class, 'template_type_id');
    }

    public function organizer()
    {
        return $this->belongsTo(Organizer::class, 'organizer_id');
    }
}
