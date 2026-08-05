<?php

namespace App\Traits;

use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait HasBulkActions
{
    /**
     * Handle a bulk action request.
     *
     * Override bulkActionMap() to define allowed actions and their handlers.
     * Each handler receives ($ids, $count) and must return a response.
     */
    public function bulkAction(Request $request)
    {
        $map = $this->bulkActionMap();

        $validated = $request->validate([
            'action' => 'required|string|in:'.implode(',', array_keys($map)),
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
        ]);

        $ids = $validated['ids'];
        $action = $validated['action'];
        $count = count($ids);

        return $map[$action]($ids, $count);
    }

    /**
     * Bulk-update rows by primary key.
     *
     * @param  string  $model  FQCN of the model
     * @param  string  $primaryKey  Column name of the primary key
     * @param  array  $attributes  Attributes to set
     * @param  array  $ids  IDs to update
     * @return int Number of updated rows
     */
    protected function bulkUpdate(string $model, string $primaryKey, array $attributes, array $ids): int
    {
        return $model::whereIn($primaryKey, $ids)->update($attributes);
    }

    /**
     * Bulk-delete rows by primary key.
     *
     * @param  string  $model  FQCN of the model
     * @param  string  $primaryKey  Column name of the primary key
     * @param  array  $ids  IDs to delete
     * @param  string|null  $guardColumn  Optional column to guard against (e.g. 'status')
     * @param  mixed  $guardValue  Optional value to exclude (e.g. 'completed')
     * @return int Number of deleted rows
     */
    protected function bulkDelete(string $model, string $primaryKey, array $ids, ?string $guardColumn = null, $guardValue = null): int
    {
        $query = $model::whereIn($primaryKey, $ids);

        if ($guardColumn && $guardValue !== null) {
            $query->where($guardColumn, '!=', $guardValue);
        }

        return $query->delete();
    }

    /**
     * Process items with a foreach loop, applying a guard check and optional logging.
     *
     * @param  string  $model  FQCN of the model
     * @param  string  $primaryKey  Column name of the primary key
     * @param  array  $ids  IDs to process
     * @param  callable  $guard  Closure($item): bool — returns true if item should be processed
     * @param  callable  $updater  Closure($item): array — returns attributes to update
     * @param  string|null  $module  ActivityLogger module
     * @param  string|null  $action  ActivityLogger description prefix
     * @param  callable|null  $notifyIds  Closure($item): array — returns user IDs to notify
     * @return int Number of processed items
     */
    protected function bulkProcessWithLog(
        string $model,
        string $primaryKey,
        array $ids,
        callable $guard,
        callable $updater,
        ?string $module = null,
        ?string $action = null,
        ?callable $notifyIds = null
    ): int {
        $items = $model::whereIn($primaryKey, $ids)->get();
        $count = 0;

        foreach ($items as $item) {
            if (! $guard($item)) {
                continue;
            }

            $item->update($updater($item));
            $count++;

            if ($module && $action) {
                $description = "{$action} #{$item->{$primaryKey}}";
                $properties = [$primaryKey => $item->{$primaryKey}];

                ActivityLogger::log(
                    $module,
                    $description,
                    $properties,
                    Auth::user(),
                    $item,
                    $notifyIds ? $notifyIds($item) : []
                );
            }
        }

        return $count;
    }

    /**
     * Redirect back with a success or error message.
     */
    protected function bulkRedirect(int $count, string $noun, bool $success = true): RedirectResponse
    {
        $message = "{$count} {$noun}(".($count !== 1 ? 's' : '').') '
            .($success ? 'processed.' : 'failed.');

        return redirect()->back()->with($success ? 'success' : 'error', $message);
    }
}
