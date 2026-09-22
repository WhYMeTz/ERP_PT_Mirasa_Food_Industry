<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait AuditableTrait
{
    /**
     * Boot the Auditable trait for Eloquent models.
     */
    protected static function bootAuditableTrait(): void
    {
        static::creating(function ($model) {
            $userId = Auth::id() ?? 'SYSTEM';

            if (empty($model->created_by)) {
                $model->created_by = (string) $userId;
            }
            if (empty($model->updated_by)) {
                $model->updated_by = (string) $userId;
            }
            if (!isset($model->deleted_st)) {
                $model->deleted_st = false;
            }
            if (!isset($model->active_st)) {
                $model->active_st = true;
            }
        });

        static::updating(function ($model) {
            $userId = Auth::id() ?? 'SYSTEM';
            $model->updated_by = (string) $userId;
        });

        static::deleting(function ($model) {
            // If model supports soft delete custom audit fields
            $userId = Auth::id() ?? 'SYSTEM';
            $model->deleted_by = (string) $userId;
            $model->deleted_st = true;
            $model->active_st = false;
            $model->saveQuietly();
        });
    }

    /**
     * Scope query untuk hanya mengambil data aktif dan belum dihapus.
     */
    public function scopeActive($query)
    {
        return $query->where('active_st', true)->where('deleted_st', false);
    }
}
