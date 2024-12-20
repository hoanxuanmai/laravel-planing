<?php

namespace HXM\LaravelPlanning\Models;

use DateTimeInterface;
use Exception;
use HXM\LaravelPlanning\Contracts\PlanCycleInstanceInterface;
use HXM\LaravelPlanning\Contracts\PlanInstanceInterface;
use HXM\LaravelPlanning\Contracts\PlanOrderInstanceInterface;
use HXM\LaravelPlanning\Contracts\PlanResourceInstance;
use HXM\LaravelPlanning\Events\PlanCycleCreatedEvent;
use HXM\LaravelPlanning\Events\PlanCycleUpdatedStatusEvent;
use HXM\LaravelPlanning\Facades\LaravelPlanning;
use HXM\LaravelPlanning\Traits\Concerns;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * @property int $plan_id
 * @property int $plan_order_id
 * @property int $number_of_cycle
 * @property float $price
 * @property string $currency
 * @property Carbon $started_at
 * @property Carbon $ended_at
 * @property int $status
 * @property PlanOrder $planOrder
 * @property Collection $items
 * @property Model $referable
 */
class PlanCycle extends Model implements PlanCycleInstanceInterface
{
    use SoftDeletes;
    use Concerns\HasItems;
    protected $fillable = ['plan_id', 'plan_order_id', 'number_of_cycle', 'price', 'currency', 'started_at', 'ended_at', 'status', 'resource_type', 'resource_id'];

    protected $casts = [
        'price' => 'float',
        'number_of_cycle' => 'int',
        'status' => 'int',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    function getPlan(): PlanInstanceInterface
    {
        return $this->plan;
    }

    function getNumberOfCycle(): int
    {
        return $this->attributes['number_of_cycle'] ?? 0;
    }

    public function planOrder()
    {
        return $this->belongsTo(PlanOrder::class);
    }

    function getPlanOrder(): PlanOrderInstanceInterface
    {
        return $this->planOrder;
    }

    public function referable()
    {
        return $this->morphTo('referable');
    }

    function getReferable(): ?Model
    {
        return $this->referable;
    }

    public function resource()
    {
        return $this->morphTo('resource');
    }

    public function getResource(): ?PlanResourceInstance
    {
        return $this->resource;
    }


    public function items()
    {
        return $this->hasMany(PlanCycleItem::class, 'plan_cycle_id', 'id');
    }


    function getItems(): Collection
    {
        return $this->items;
    }

    public function orderItems()
    {
        return $this->belongsToMany(PlanOrderItem::class, 'plan_cycle_items', 'plan_cycle_id', 'plan_order_item_id');
    }

    function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    protected function scopeWhereByNumberOfCycle(Builder $query, int $value = 1, $operator = '=')
    {
        return $query->where('number_of_cycle', $operator, $value);
    }

    protected function scopeOrderByNumberOfCycle(Builder $query, string $directory = 'desc')
    {
        return $query->orderBy('number_of_cycle', $directory);
    }

    protected function scopeOnlyCurrentCycle(Builder $builder, DateTimeInterface $now = null)
    {
        $now = $now == null ? now() : Carbon::parse($now);
        return $builder->whereDate($this->qualifyColumn('started_at'), '<=', $now)
            ->whereDate($this->qualifyColumn('ended_at'), '>=', $now);
    }

    public function save(array $option = [])
    {
        DB::beginTransaction();
        try {
            $return = parent::save($option);
            DB::commit();
            return $return;
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        return LaravelPlanning::getTable('cycle');
    }


    static function booted()
    {
        static::creating(function (self $model) {
            if (static::query()->where([
                'plan_id' => $model->plan_id,
                'number_of_cycle' => $model->number_of_cycle,
                'plan_order_id' => $model->plan_order_id,
            ])->exists()) {
                throw new Exception("The Cycle (number_of_cycle: {$model->number_of_cycle}) has already been taken.");
            }
        });
        static::created(function (self $model) {
            if ($model->planOrder && $model->relationLoaded('items') && $model->items instanceof Collection) {
                $attachs = collect();
                $model->items->each(function (Model $item) use ($model, $attachs) {
                    if ($item instanceof PlanItem) {
                        $orderItem = $model->planOrder->items->firstWhere('plan_item_id', $item->getKey());
                        // $orderItem && $attachs->add($orderItem->getKey());
                    } elseif ($item instanceof PlanOrderItem) {
                        $orderItem = $model->planOrder->items->firstWhere($item->getKeyName(), $item->getKey());
                        // $orderItem && $attachs->add($orderItem->getKey());
                    }

                    $orderItem && $attachs->add($model->items()->make([
                        'plan_order_item_id' => $orderItem->getKey(),
                        'name' => $orderItem->name,
                        'description' => $orderItem->description,
                        'currency' => $orderItem->currency,
                        'price' => $orderItem->price,
                        'sort' => $orderItem->sort,
                    ]));
                });
                if ($attachs->isNotEmpty()) {
                    $model->items()->insert($attachs->toArray());
                }

                $model->planOrder->addLog('created new Cycle', $model);
            }
            try {
                event(new PlanCycleCreatedEvent($model));
            } catch (Exception $exception) {
            }
        });

        static::updated(function (self $model) {
            if ($model->isDirty('status') && $model->wasChanged('status')) {
                event(new PlanCycleUpdatedStatusEvent($model, $model->getOriginalWithoutRewindingModel('status')));
            }
        });
    }
}
