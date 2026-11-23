<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Workspace extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'owner_id',
        'currency',
        'timezone',
        'reminder_hour',
        'logo_path',
        'brand_color',
        'reply_to_email',
        'stripe_account_id',
        'plan',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'workspace_user'
        )->withPivot('role')
         ->withTimestamps();
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function invitations()
    {
    return $this->hasMany(Invitation::class);
    }
}