<?php

namespace ApurbaLabs\ApprovalEngine\Support\Traits;

trait HasTenant
{
    protected static function bootHasTenant()
    {
        static::creating(function ($model) {
            if (app()->bound('tenant_id') && empty($model->tenant_id)) {
                $model->tenant_id = app('tenant_id');
            }
        });
    }
}