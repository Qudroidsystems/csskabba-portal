<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Broadsheets extends Model
{
    use HasFactory;

    protected $fillable = [
        'broadsheet_record_id',
        'term_id',
        'subjectclass_id',
        'staff_id',
        // 'ca1',
        // 'ca2',
        // 'ca3',
        'exam',
        'total',
        'bf',
        'cum',
        'grade',
        'all_subjects_total_score',  // Note: underscore between 'all' and 'subjects'
        'subject_position_class',
        'subject_position_class_total',  // New field
        'arm_position',  // New field
        'arm_position_cum',  // New field
        'cmin',
        'cmax',
        'avg',
        'remark',
        'submiitedby',
        'vettedby',
        'vettedstatus'
    ];

    protected $casts = [
        'ca1' => 'float',
        'ca2' => 'float',
        'ca3' => 'float',
        'exam' => 'float',
        'total' => 'float',
        'bf' => 'decimal:2',
        'cum' => 'decimal:2',
        'cmin' => 'float',
        'cmax' => 'float',
        'avg' => 'float',
        'subject_position_class_total' => 'integer',
        'arm_position' => 'integer',
        'arm_position_cum' => 'integer',
    ];

    public function broadsheetRecord()
    {
        return $this->belongsTo(BroadsheetRecord::class, 'broadsheet_record_id', 'id');
    }

    public function term()
    {
        return $this->belongsTo(Schoolterm::class, 'term_id');
    }

    public function session()
    {
        return $this->belongsTo(Schoolsession::class, 'session_id');
    }

    public function subjectclass()
    {
        return $this->belongsTo(Subjectclass::class, 'subjectclass_id');
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    /**
     * Relationship to Assessment Scores (Dynamic Assessments)
     */
    public function assessmentScores()
    {
        return $this->hasMany(BroadsheetAssessmentScore::class, 'broadsheet_id');
    }

    /**
     * Relationship to Sub-Assessment Scores (for sub-assessments under assessments)
     */
    public function subAssessmentScores()
    {
        return $this->hasMany(BroadsheetSubAssessmentScore::class, 'broadsheet_id');
    }
}
