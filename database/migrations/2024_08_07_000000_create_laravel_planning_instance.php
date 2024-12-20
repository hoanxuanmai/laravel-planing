<?php

use HXM\LaravelPlanning\Facades\LaravelPlanning;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLaravelPlanningInstance extends Migration
{
    function __construct() {}
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(LaravelPlanning::getTable('plan'), function (Blueprint $table) {
            $table->id();
            $table->string('resource')->index();
            $table->string('code')->unique()->index();
            $table->string('name');
            $table->string('description')->nullable();
            $table->unsignedInteger('cycle')->default(0);
            $table->enum('interval', ['day', 'week', 'month', 'year'])->nullable();
            $table->unsignedInteger('interval_count')->default(1)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create(LaravelPlanning::getTable('item'), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plan_id');
            $table->string('name');
            $table->string('description')->nullable();
            $table->float('price');
            $table->char('currency', 3)->default('USD');
            $table->unsignedInteger('cycle')->default(0)->comment('0 to continue multiple times');
            $table->unsignedInteger('start_at_cycle')->default(1);
            $table->unsignedInteger('interval_count')->default(1);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('plan_id')->references('id')->on(LaravelPlanning::getTable('plan'))->cascadeOnDelete();
        });

        Schema::create(LaravelPlanning::getTable('itemPercentPrice'), function (Blueprint $table) {
            $table->id('plan_item_id');
            $table->unsignedBigInteger('parent_item_id')->nullable();
            $table->float('value', 5, 2)->default(0);
            $table->foreign('plan_item_id')->references('id')->on(LaravelPlanning::getTable('item'))->cascadeOnDelete();
            $table->foreign('parent_item_id')->references('id')->on(LaravelPlanning::getTable('item'))->cascadeOnDelete();
        });

        Schema::create(LaravelPlanning::getTable('condition'), function (Blueprint $table) {
            $table->id();
            $table->morphs('target');
            $table->string('resource');
            $table->string('attribute');
            $table->string('operation');
            $table->string('value')->nullable()->default(null);
            $table->timestamps();
        });

        Schema::create(LaravelPlanning::getTable('order'), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plan_id');
            $table->uuidMorphs('resource');
            $table->string('name');
            $table->string('description')->nullable()->default(null);
            $table->dateTime('started_at');
            $table->unsignedInteger('total_cycle')->default(1);
            $table->enum('interval', ['day', 'week', 'month', 'year'])->nullable();
            $table->unsignedInteger('interval_count')->default(1)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create(LaravelPlanning::getTable('orderLog'), function (Blueprint $table) {
            $table->unsignedBigInteger('plan_order_id');
            $table->nullableMorphs('referable');
            $table->text('content')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->foreign('plan_order_id')->references('id')->on(LaravelPlanning::getTable('order'))->cascadeOnDelete();
        });

        Schema::create(LaravelPlanning::getTable('orderItem'), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plan_order_id');
            $table->unsignedBigInteger('plan_item_id');
            $table->string('name');
            $table->string('description')->nullable();
            $table->float('price');
            $table->char('currency', 3)->default('USD');
            $table->unsignedInteger('cycle')->default(0)->comment('0 to continue multiple times');
            $table->unsignedInteger('start_at_cycle')->default(1);
            $table->unsignedInteger('interval_count')->default(1);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('plan_order_id')->references('id')->on(LaravelPlanning::getTable('order'))->cascadeOnDelete();
        });

        Schema::create(LaravelPlanning::getTable('orderItemPercentPrice'), function (Blueprint $table) {
            $table->id('plan_order_item_id');
            $table->unsignedBigInteger('parent_item_id')->nullable();
            $table->float('value', 5, 2)->default(0);
            $table->foreign('plan_order_item_id')->references('id')->on(LaravelPlanning::getTable('orderItem'))->cascadeOnDelete();
            $table->foreign('parent_item_id')->references('id')->on(LaravelPlanning::getTable('orderItem'))->cascadeOnDelete();
        });

        Schema::create(LaravelPlanning::getTable('cycle'), function (Blueprint $table) {
            $table->id();
            $table->uuidMorphs('resource');
            $table->nullableUuidMorphs('referable');
            $table->unsignedBigInteger('plan_id');
            $table->unsignedBigInteger('plan_order_id');
            $table->unsignedInteger('number_of_cycle');
            $table->float('price');
            $table->string('currency')->default('USD');
            $table->dateTime('started_at');
            $table->dateTime('ended_at');
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('plan_order_id')->references('id')->on(LaravelPlanning::getTable('order'))->cascadeOnDelete();
            $table->index(['started_at', 'ended_at', 'status']);
        });

        Schema::create(LaravelPlanning::getTable('cycleItem'), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plan_cycle_id');
            $table->unsignedBigInteger('plan_order_item_id');
            $table->string('name');
            $table->string('description')->nullable();
            $table->char('currency', 3)->default('USD');
            $table->float('price');
            $table->unsignedInteger('sort')->default(0);
            $table->foreign('plan_cycle_id')->references('id')->on(LaravelPlanning::getTable('cycle'))->cascadeOnDelete();
        });

        Schema::create(LaravelPlanning::getTable('cycleSchedule'), function (Blueprint $table) {
            $table->id();
            $table->timestamp('run_at');
            $table->bigInteger('plan_order_id')->unsigned();
            $table->uuidMorphs('resource');
            $table->string('interval');
            $table->integer('number_of_cycle')->unsigned();
            $table->string('action')->default('create_next_cycle');
            $table->tinyInteger('status')->unsigned()->default(0);
            $table->string('message')->nullable();
            $table->timestamps();
            $table->foreign('plan_order_id')->references('id')->on(LaravelPlanning::getTable('order'))->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(LaravelPlanning::getTable('cycleSchedule'));
        Schema::dropIfExists(LaravelPlanning::getTable('cycleItem'));
        Schema::dropIfExists(LaravelPlanning::getTable('cycle'));

        Schema::dropIfExists(LaravelPlanning::getTable('orderItemPercentPrice'));
        Schema::dropIfExists(LaravelPlanning::getTable('orderItem'));
        Schema::dropIfExists(LaravelPlanning::getTable('orderLog'));
        Schema::dropIfExists(LaravelPlanning::getTable('order'));

        Schema::dropIfExists(LaravelPlanning::getTable('condition'));
        Schema::dropIfExists(LaravelPlanning::getTable('itemPercentPrice'));
        Schema::dropIfExists(LaravelPlanning::getTable('item'));
        Schema::dropIfExists(LaravelPlanning::getTable('plan'));
    }
}
