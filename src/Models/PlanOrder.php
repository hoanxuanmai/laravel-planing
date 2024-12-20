<?php

namespace HXM\LaravelPlanning\Models;

use HXM\LaravelPlanning\Contracts\PlanInstanceInterface;
use HXM\LaravelPlanning\Contracts\PlanOrderInstanceInterface;
use HXM\LaravelPlanning\Contracts\PlanResourceInstance;
use HXM\LaravelPlanning\Events\PlanOrderCreatedEvent;
use HXM\LaravelPlanning\Events\PlanOrderForceDeletedEvent;
use HXM\LaravelPlanning\Events\PlanOrderSoftDeletedEvent;
use HXM\LaravelPlanning\Facades\LaravelPlanning;
use HXM\LaravelPlanning\Traits\Concerns\HasCycleAttribute;
use HXM\LaravelPlanning\Traits\Concerns\HasIntervalAttribute;
use HXM\LaravelPlanning\Traits\Concerns\HasIntervalCountAttribute;
use HXM\LaravelPlanning\Traits\Concerns\HasItems;
use HXM\LaravelPlanning\Traits\Concerns\HasTotalCycleAttribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * @property int $plan_id
 * @property string $name
 * @property string $description
 * @property Carbon $started_at
 * @property int $total_cycle
 * @property string $interval
 * @property int $interval_count
 * @property Collection $items
 * @property Plan $plan
 * @property Collection $cycles
 * @property Collection $logs
 * @property Model $target
 */
class PlanOrder extends Model implements PlanOrderInstanceInterface
{

    use HasIntervalAttribute, HasIntervalCountAttribute, HasItems, HasTotalCycleAttribute, HasCycleAttribute;
    use SoftDeletes;
    protected $fillable = ['plan_id', 'name', 'description', 'started_at', 'total_cycle', 'interval', 'interval_count'];

    protected $casts = [
        'total_cycle' => 'int',
        'started_at' => 'datetime',
        'interval' => 'string',
        'interval_count' => 'int'
    ];

    function getStartAtCycle(): int
    {
        return 0;
    }

    function getCondition(): ?PlanCondition
    {
        return null;
    }

    function isCycle(): bool
    {
        return $this->getCycle() != 1;
    }

    function getCycle(): int
    {
        return $this->attributes['total_cycle'] ?? 0;
    }

    /**
     * relation items
     * @return HasMany
     */
    public function items()
    {
        return $this->hasMany(PlanOrderItem::class, 'plan_order_id');
    }

    /**
     * relation plan
     * @return BelongsTo
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    function getPlan(): PlanInstanceInterface
    {
        return $this->plan;
    }

    /**
     * relation target of order
     * @return MorphTo
     */
    public function resource()
    {
        return $this->morphTo('resource');
    }

    function getResource(): PlanResourceInstance
    {
        return $this->resource;
    }

    /**
     * relation cycles
     * @return HasMany
     */
    public function cycles()
    {
        return $this->hasMany(PlanCycle::class);
    }

    function getCycles(): Collection
    {
        return $this->cycles;
    }

    /**
     * relation logs
     * @return HasMany
     */
    public function logs()
    {
        return $this->hasMany(PlanOrderLog::class);
    }

    function getLogs(): Collection
    {
        return $this->logs;
    }

    /**
     * Summary of addLog
     * @param mixed $content
     * @param \Illuminate\Database\Eloquent\Model|null $referable
     * @return Model
     */
    public function addLog($content, Model $referable = null)
    {
        $log = $this->logs()->make(['content' => $content]);
        $log->referable()->associate($referable);
        $log->save();
        return $log;
    }

    public function schedules()
    {
        return $this->hasMany(PlanCycleSchedule::class, 'plan_order_id', 'id');
    }

    public function getSchedules(): Collection
    {
        return $this->schedules;
    }
    
    public function changeTotalCycle(int $totalCycle, Model $referable)
    {
        $from = $this->getOriginalWithoutRewindingModel('total_cycle');
        if ($this->update(['total_cycle' => $totalCycle])) {
            $this->addLog("Change total cycle from {$from} to {$totalCycle}", $referable);
        }
    }

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        return LaravelPlanning::getTable('order');
    }

    public function save(array $options = [])
    {
        DB::beginTransaction();
        try {
            $return = parent::save($options);
            DB::commit();
            return $return;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }


    static function booted()
    {
        static::creating(function (PlanOrder $model) {

            [$type, $id] = $model->getMorphs('resource', null, null);
            // Delete current order
            static::where([
                $type => $model->{$type},
                $id => $model->{$id},

            ])->get()->each->delete();
        });
        static::created(function (self $model) {
            $items = collect();
            $itemPercentPrices = collect();
            if ($model->relationLoaded('items') && $model->items instanceof Collection) {
                $model->items->each(function (PlanOrderItem $item) use ($model, $itemPercentPrices, $items) {
                    $items->add($model->items()->save($item));
                    if ($item->relationLoaded('percent_price') && $item->percent_price instanceof PlanItemPercentPrice) {
                        $itemPercentPrices->add($item->percent_price()->make($item->percent_price->toArray()));
                    }
                });
            }
            $model->setRelation('items', $items);

            if ($itemPercentPrices->isNotEmpty()) {
                foreach ($itemPercentPrices as $price) {
                    $itemPrice = $model->items->where('plan_item_id', $price->parent_item_id)->first();
                    if ($itemPrice) {
                        $price->parent_item_id = $itemPrice->id;
                        $price->save();
                    }
                }
            }

            if ($model->relationLoaded('cycles') && $model->cycles instanceof Collection) {

                $model->cycles->each(function (PlanCycle $item) use ($model) {
                    $item->planOrder()->associate($model);
                    $model->cycles()->save($item);
                });
            }
            event(new PlanOrderCreatedEvent($model));
        });

        static::deleted(function (self $model) {

            if ($model->isForceDeleting()) {
                $model->items()->delete();
                $model->cycles()->delete();
                event(new PlanOrderForceDeletedEvent($model));
            } else {
                $time = $model->fromDateTime($model->freshTimestamp());
                $model->items()->update([
                    $model->items()->make()->getDeletedAtColumn() => $time
                ]);
                $model->cycles()->update([
                    $model->cycles()->make()->getDeletedAtColumn() => $time
                ]);
                event(new PlanOrderSoftDeletedEvent($model));
            }
        });
    }
}
