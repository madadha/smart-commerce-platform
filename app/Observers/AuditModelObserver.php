<?php

namespace App\Observers;

use App\Services\Audit\AuditLogger;
use Illuminate\Database\Eloquent\Model;

class AuditModelObserver
{
    public function __construct(protected AuditLogger $logger)
    {
    }

    public function updating(Model $model): void
    {
        $this->logger->captureUpdate($model);
    }

    public function created(Model $model): void
    {
        $this->logger->recordCreated($model);
    }

    public function updated(Model $model): void
    {
        $this->logger->recordUpdated($model);
    }

    public function deleted(Model $model): void
    {
        $this->logger->recordDeleted($model);
    }
}
