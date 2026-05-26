<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Schoolclass extends Model
{
    use HasFactory;

    protected $table = "schoolclass";

    protected $fillable = ['schoolclass', 'arm', 'description'];

    public function arms()
    {
        return $this->belongsTo(Schoolarm::class, 'arm', 'id');
    }

    public function classcategories()
    {
        return $this->belongsToMany(Classcategory::class, 'schoolclass_classcategory', 'schoolclass_id', 'classcategory_id');
    }

    public function arm()
    {
        return $this->belongsTo(Schoolarm::class, 'arm');
    }

    // Add this method for consistency
    public function armRelation()
    {
        return $this->belongsTo(Schoolarm::class, 'arm', 'id');
    }

    public function classcategory()
    {
        return $this->belongsTo(Classcategory::class, 'classcategoryid', 'id');
    }

    public function subjectClasses()
    {
        return $this->hasMany(Subjectclass::class, 'schoolclassid', 'id');
    }

    public function studentCurrentTerms()
    {
        return $this->hasMany(StudentCurrentTerm::class, 'schoolclassId', 'id');
    }

    public function currentStudents()
    {
        return $this->hasManyThrough(
            Student::class,
            StudentCurrentTerm::class,
            'schoolclassId',
            'id',
            'id',
            'studentId'
        )->where('student_current_term.is_current', true);
    }
}
