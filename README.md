# Laravel Planning

Laravel Planning is a library that supports creating and managing plans in Laravel projects.


## Features

- Flexible plan and item management
- Event-driven actions and listeners
- Built-in scheduling and cron job support
- Customizable panel interface


## Requirements

- PHP >= 7.4
- Laravel >= 7.x

## Installation

Install the package via Composer:

```bash
composer require hxm/laravel-planning
```

## Configuration

After installation, you can publish the configuration file using the following command:

```bash
php artisan vendor:publish --tag="laravel-planning:config"
```

You can also publish the migration files using:

```bash
php artisan vendor:publish --tag="laravel-planning:migration"
```

### Default Configurations

Below are the default configurations:

```php
<?php

use HXM\LaravelPlanning\Events;
use HXM\LaravelPlanning\Listeners;
use HXM\LaravelPlanning\Models;

return [
    "resources" => [
        // \App\User::class
    ], // string[] resource list
    "useMigration" => true,
    "tables" => [
        "prefix" => '',
        "plan" => 'plans',
        "item" => 'plan_items',
        "itemPercentPrice" => 'plan_item_percent_prices',
        "condition" => 'plan_conditions',
        "order" => 'plan_orders',
        "orderItem" => 'plan_order_items',
        "orderItemPercentPrice" => 'plan_order_item_percent_prices',
        "orderLog" => 'plan_order_logs',
        "cycle" => 'plan_cycles',
        "cycleItem" => 'plan_cycle_items',
        "cycleSchedule" => 'plan_schedules',
    ],
    "models" => [
        "plan" => Models\Plan::class,
        "item" => Models\PlanItem::class,
        "itemPercentPrice" => Models\PlanItemPercentPrice::class,
        "condition" => Models\PlanCondition::class,
        "order" => Models\PlanOrder::class,
        "orderItem" => Models\PlanOrderItem::class,
        "orderLog" => Models\PlanOrderLog::class,
        "cycle" => Models\PlanCycle::class,
        "cycleItem" => Models\PlanCycleItem::class,
    ],
    "listeners" => [
        Events\PlanOrderCreatedEvent::class => function (Events\PlanOrderCreatedEvent $event) {
            $event->planOrder->addLog('Created');
        },
        Events\PlanCycleCreatedEvent::class => [],
        Events\PlanCycleUpdatedStatusEvent::class => [
            Listeners\CreatePlanScheduleWhenCycleChangedStatus::class,
            function (Events\PlanCycleUpdatedStatusEvent $event) {
                $event->planCycle->planOrder->addLog('cycle#' . $event->planCycle->getKey() . ' change Status from ' . $event->preStatus . ' to ' . $event->planCycle->status, $event->planCycle->getReferable());
            }
        ],
    ],
    "schedule" => true,
    "cron" => '10 0 * * *',
    "pannel" => [
        "enable" => true,
        "prefix" => "plans",
        "as" => "plans.",
        "middleware" => ["web", "auth"]
    ]
];
```

### Resource Management

The plans operate based on resource models. To create a plan for `App\User`, add it to the `resources` array in the configuration file:

```php
"resources" => [
    \App\User::class
],
```

Alternatively, you can dynamically add resources in the `AppServiceProvider`:

```php
use HXM\LaravelPlanning\LaravelPlanning;

public function boot()
{
    LaravelPlanning::addResources([\App\User::class]);
}
```

### Implementing PlanResourceInstance

Before creating plan cycles for a resource (e.g., `User`), you must implement the `PlanResourceInstance` interface in your model and use the `HasPlanResourceInstance` trait:

```php
use HXM\LaravelPlanning\Traits\HasPlanResourceInstance;
use HXM\LaravelPlanning\Contracts\PlanResourceInstance;

class User extends Authenticatable implements PlanResourceInstance
{
    use HasPlanResourceInstance;
}
```

### Panel Management

Laravel Planning provides a built-in panel interface to manage plans. The panel settings are defined in the configuration under the `pannel` key:

```php
"pannel" => [
    "enable" => true,
    "prefix" => "plans",
    "as" => "plans.",
    "middleware" => ["web", "auth"]
]
```

By default, the panel is enabled. You can disable it by setting `"enable"` to `false`. If the panel is disabled, you will need to create your own implementation for managing plans.

To access the panel, visit:

```plaintext
https://{yourhost}/plans
```

#### Using the Panel

1. Navigate to the `Create` section to create a new plan via the form.
2. Once a plan is created, add items to it. These items represent the features or content activated by the plan. Costs and prices can be set for each item.
3. If the cycle is set to `0`, the items will repeat across cycles.

## Usage

Here is an example of how to use the library:

### Creating a Plan Order for a Resource

To create a plan order for a `User` resource:

```php
use App\User;
use HXM\LaravelPlanning\Actions\Creations\CreatePlanOrderCalculator;
use HXM\LaravelPlanning\Models\Plan;

$user = User::find(1);
$plan = Plan::find(1);
$action = new CreatePlanOrderCalculator($user, $plan);

$startAt = now();
$numberOfCycle = 1;
$planOrder = $action->handle($startAt, $numberOfCycle);

// Save the plan order instance to the database
$planOrder->save();

// Get Items
$planOrder->getItems();

// Get Cycle list
$planOrder->getCycles();

// Optionally, initialize a payment for the plan order
// This can be done before or after saving the plan order.
```

### Managing Plan Cycles

After saving the plan order, you can retrieve its cycles:

```php
$planCycle = $planOrder->getCycles()->last();
```

You can associate a payment with the retrieved cycle:

```php
$planCycle->referable()->associate($payment);
$planCycle->save();
```

By default, the plan cycle will have a status of `inactive` (`status = 0`). You can define your custom logic for handling this state.

To update the cycle's status, you can use:

```php
use Illuminate\Database\Eloquent\Model;
use HXM\LaravelPlanning\Models\PlanCycle;
use HXM\LaravelPlanning\Actions\Updates\UpdatePlanCycleStatus;

UpdatePlanCycleStatus::handle(PlanCycle $planCycle,int $newStatus, Model $resource);
// Or
UpdatePlanCycleStatus::handleByReferable(Model $payment, int $newStatus);
```
When a cycle's status is updated to `1`, a schedule is automatically created to generate the next cycle. The schedule's configuration is defined in the config file. You can listen for events such as `PlanCycleCreatedEvent` or `PlanCycleUpdatedStatusEvent` to add custom logic when new cycles are created or their statuses are updated.

### Querying Resources with Cycles

You can query resources along with their current cycles:

```php
$usersWithCurrentCycle = User::withCurrentCycle()->get();
```

Or retrieve a list of users with active cycles (`status = 1`):

```php
$usersWithActiveCycle = User::hasActiveCycle()->get();
```

## Contact

Please contact me if you need assistance [Email](mailto:hoanxuanmai@gmail.com).

You can also donate to me at [Paypal](https://paypal.me/MaiXuanHoan)

## Contribution

We welcome contributions! Please follow these steps:

1. Fork the repository.
2. Create a new branch: `git checkout -b new-feature`.
3. Commit your changes: `git commit -m 'Add new feature'`.
4. Push to your branch: `git push origin new-feature`.
5. Submit a pull request.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE.md).
