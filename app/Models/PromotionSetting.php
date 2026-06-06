<?php
// app/Models/PromotionSetting.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PromotionSetting extends Model
{
    use HasFactory;

    protected $table = 'promotion_settings';

    protected $fillable = [
        'schoolclass_id',
        'session_id',
        'term_id',
        'rule_type',
        'min_compulsory_pass',
        'compulsory_fail_action',
        'promotion_pass_average',
        'trial_pass_average',
        'see_principal_average',
        'combined_logic',
        'promoted_label',
        'trial_label',
        'see_principal_label',
        'repeat_label',
        'is_active',
    ];

    protected $casts = [
        'promotion_pass_average' => 'decimal:2',
        'trial_pass_average' => 'decimal:2',
        'see_principal_average' => 'decimal:2',
        'min_compulsory_pass' => 'integer',
        'is_active' => 'boolean',
    ];

    public function schoolclass()
    {
        return $this->belongsTo(Schoolclass::class, 'schoolclass_id');
    }

    public function session()
    {
        return $this->belongsTo(Schoolsession::class, 'session_id');
    }

    public function term()
    {
        return $this->belongsTo(Schoolterm::class, 'term_id');
    }
}
