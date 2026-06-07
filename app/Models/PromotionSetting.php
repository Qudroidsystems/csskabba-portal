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
        'promotion_rules',  // ADD THIS - for storing JSON rules
        'is_active',
    ];

    protected $casts = [
        'promotion_pass_average' => 'decimal:2',
        'trial_pass_average' => 'decimal:2',
        'see_principal_average' => 'decimal:2',
        'min_compulsory_pass' => 'integer',
        'is_active' => 'boolean',
        'promotion_rules' => 'array',  // ADD THIS - to automatically cast JSON to array
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

    // Accessor to ensure we always get an array
    public function getPromotionRulesAttribute($value)
    {
        if (is_null($value)) {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : [];
    }

    // Mutator to ensure JSON is stored properly
    public function setPromotionRulesAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['promotion_rules'] = json_encode($value);
        } elseif (is_string($value)) {
            $this->attributes['promotion_rules'] = $value;
        } else {
            $this->attributes['promotion_rules'] = json_encode([]);
        }
    }
}
