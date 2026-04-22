<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceTermSetting extends Model
{
    use HasFactory;

    protected $table = 'attendance_term_settings';

    protected $fillable = [
        'term_id', 'session_id', 'resumption_date', 'vacation_date',
        'track_morning', 'track_afternoon', 'created_by',
    ];

    protected $casts = [
        'resumption_date' => 'date',
        'vacation_date'   => 'date',
        'track_morning'   => 'boolean',
        'track_afternoon' => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────
    public function term()    { return $this->belongsTo(Schoolterm::class,   'term_id'); }
    public function session() { return $this->belongsTo(Schoolsession::class,'session_id'); }
    public function creator() { return $this->belongsTo(User::class,         'created_by'); }

    // ── Helpers ───────────────────────────────────────────────
    /** Total weekdays in the term, minus holidays. */
    public function totalSchoolDays(): int
    {
        $holidayDates = $this->getHolidayDates();

        $count   = 0;
        $current = $this->resumption_date->copy();
        while ($current->lte($this->vacation_date)) {
            if (!$current->isWeekend() && !$holidayDates->contains($current->toDateString())) {
                $count++;
            }
            $current->addDay();
        }

        return $count;
    }

    /** Collect all individual holiday dates for this term. */
    public function getHolidayDates(): \Illuminate\Support\Collection
    {
        $holidays = AttendanceHoliday::forTerm($this->term_id, $this->session_id)->get();

        $dates = collect();
        foreach ($holidays as $h) {
            foreach ($h->allDates() as $d) {
                $dates->push($d);
            }
        }
        return $dates->unique();
    }

    /** Number of periods tracked per day. */
    public function periodsPerDay(): int
    {
        return ($this->track_morning ? 1 : 0) + ($this->track_afternoon ? 1 : 0);
    }

    /** Check whether this setting covers a given date. */
    public function coversDate(string $date): bool
    {
        $d = \Carbon\Carbon::parse($date);
        return $d->between($this->resumption_date, $this->vacation_date);
    }
}
