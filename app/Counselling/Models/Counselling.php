<?php

namespace App\Counselling\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Course\Models\CourseMember;

class Counselling extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'course_member_id',
        'counselling_setup_id',
        'persona_id',
        'status_chat_id',
        'start_date',
        'note'
    ];

    protected $casts = [
        'note' => 'json',
    ];

    public function courseMember()
    {
        return $this->belongsTo(CourseMember::class);
    }

    public function counsellingSetup() {
        return $this->belongsTo(CounsellingSetup::class);
    }

    public function counsellingMessages() {
        return $this->hasMany(CounsellingMessage::class);
    }
}
