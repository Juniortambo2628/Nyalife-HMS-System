<?php

namespace App\Traits;

use Spatie\Activitylog\Models\Activity;

trait DescribesActivity
{
    public function tapActivity(Activity $activity, string $eventName): void
    {
        $model = class_basename(static::class);
        $label = $this->getActivityLabel();

        $changes = $activity->properties->get('attributes', []);
        $key = $this->getKey();

        match ($eventName) {
            'created' => $activity->description = "{$label} #{$key} created",
            'updated' => $activity->description = "{$label} #{$key} updated",
            'deleted' => $activity->description = "{$label} #{$key} deleted",
            default   => $activity->description = "{$label} #{$key} {$eventName}",
        };
    }

    protected function getActivityLabel(): string
    {
        return class_basename(static::class);
    }
}
