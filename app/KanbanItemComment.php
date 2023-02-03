<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KanbanItemComment extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('d.m.Y H:i');
    }

    public function kanbanItem()
    {
        return $this->belongsTo(KanbanItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
