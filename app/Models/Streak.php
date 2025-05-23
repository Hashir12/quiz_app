<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Streak extends Model
{
    protected $fillable = ['user_id','streak_count','last_login'];

    public static function updateStreak($user)
    {
        $streak = self::where('user_id', $user->id)->first();

        if (!$streak) {
            $streakData = [
                'user_id' => $user->id,
                'streak_count' => 0,
                'last_login' => now(),
            ];

            self::create($streakData);
        }

        if ($streak->last_login == Carbon::yesterday()) {
            $streak->streak_count = $streak->streak_count++;
        } else if ($streak->last_login == Carbon::yesterday()->addDay(1)) {
            $streak->streak_count = 0;
        }
        $streak->last_login = now();
        $streak->save();
    }
}
