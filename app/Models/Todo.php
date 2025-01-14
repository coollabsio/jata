<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'status', 'description', 'user_id', 'worked_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function getAllTodos()
    {
        return self::where('user_id', auth()->user()->id)->get();
    }

    public static function getAllTodosExceptToday()
    {
        return self::where('user_id', auth()->user()->id)->where('worked_at', '<', now()->startOfDay())->orWhere('worked_at', null)->orderBy('created_at', 'desc')->get();
    }

    public static function getTodayTodos()
    {
        return self::where('user_id', auth()->user()->id)->where('worked_at', '>=', now()->startOfDay())->where('worked_at', '<=', now()->endOfDay())->get();
    }

    public static function getBacklogTodos()
    {
        return self::where('user_id', auth()->user()->id)->where('status', '!=', 'completed')->get();
    }

    public static function updateTodo($id, $data)
    {
        $todo = self::where('user_id', auth()->user()->id)->where('id', $id)->first();
        if (! $todo) {
            return false;
        }
        $todo->update($data);

        return true;
    }

    public static function transferYesterdayTodos()
    {
        $todos = self::where('user_id', auth()->user()->id)->where('worked_at', '>=', now()->subDay(1)->startOfDay())->orWhere('worked_at', '<=', now()->subDay(1)->endOfDay())->get();
        foreach ($todos as $todo) {
            $todo->worked_at = now();
            $todo->save();
        }
    }
}
