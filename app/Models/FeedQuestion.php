<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedQuestion extends Model
{
    protected $fillable = ['user_id', 'question'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function answers()
    {
        return $this->hasMany(FeedAnswer::Class,'question_id');
    }
}
