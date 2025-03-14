<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KanbanSubscription extends Model
{
    protected $fillable = [
        'subscribable_type',
        'subscribable_id',
        'kanban_id',
        'editable',
        'owner_id', ];

    /**
     * Get the subscriber model.
     */
    public function subscribable()
    {
        return $this->morphTo();
    }

    public function kanban()
    {
        return $this->belongsTo('App\Kanban', 'kanban_id', 'id');
    }

    public function isAccessible()
    {
        return $this->kanban->isAccessible();
    }
}
