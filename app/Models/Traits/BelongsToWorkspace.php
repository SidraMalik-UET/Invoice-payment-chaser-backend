<?php

namespace App\Models\Traits;

use App\Support\CurrentWorkspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait BelongsToWorkspace
{
    protected static function bootBelongsToWorkspace(): void
    {
        // Automatically filter records by active workspace
        static::addGlobalScope('workspace', function (Builder $query) {
            $workspaceId = app(CurrentWorkspace::class)->id();

            if ($workspaceId) {
                $query->where(
                    $query->getModel()->getTable() . '.workspace_id',
                    $workspaceId
                );
            } else {
                // No active workspace = no records
                $query->whereRaw('1 = 0');
            }
        });

        // Automatically assign workspace when creating a record
        static::creating(function (Model $model) {
            $workspaceId = app(CurrentWorkspace::class)->id();

            if (!$workspaceId) {
                throw new \RuntimeException(
                    'Cannot create tenant record without an active workspace.'
                );
            }

            $model->workspace_id ??= $workspaceId;
        });
    }
}