<?php

namespace Lioneagle\LeSortable\Tests;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Lioneagle\LeSortable\Tests\Fixtures\SortableModel;

class SortableTest extends TestCase
{
    /** @test */
    public function it_sets_the_new_order_by_default()
    {
        $model = SortableModel::first();

        $models = $model->newSortQuery()->get();

        $models->each(function (SortableModel $model) {
            $this->assertEquals($model->id, $model->name);
        });
    }

    /** @test */
    public function it_can_get_the_current_highest_order_in_a_group()
    {
        $model = SortableModel::first();

        $highest = $model->getCurrentHighestOrder();

        $this->assertEquals(5, $highest);
    }

    /** @test */
    public function it_can_get_the_next_order_in_a_group()
    {
        $model = SortableModel::first();

        $next = $model->getNextOrder();

        $this->assertEquals(6, $next);
    }

    /** @test */
    public function it_returns_a_new_query_builder()
    {
        /** @var SortableModel $model */
        $model = SortableModel::query()->first();

        $this->assertInstanceOf(Builder::class, (new SortableModel())->newSortQuery());
        $this->assertInstanceOf(Builder::class, $model->newSortQuery());
    }

    /** @test */
    public function it_can_insert_model_before_another_in_same_group()
    {
        $first = SortableModel::find(11);
        $second = SortableModel::find(12);
        $third = SortableModel::find(13);
        $fourth = SortableModel::find(14);

        $third->moveBefore($first);

        $this->assertEquals(2, $first->getFreshOrder());
        $this->assertEquals(3, $second->getFreshOrder());
        $this->assertEquals(1, $third->getFreshOrder());
        $this->assertEquals(4, $fourth->getFreshOrder());
    }

    /** @test */
    public function it_can_insert_model_after_another_in_same_group()
    {
        $first = SortableModel::find(11);
        $second = SortableModel::find(12);
        $third = SortableModel::find(13);
        $fourth = SortableModel::find(14);

        $first->moveAfter($third);

        $this->assertEquals(3, $first->getFreshOrder());
        $this->assertEquals(1, $second->getFreshOrder());
        $this->assertEquals(2, $third->getFreshOrder());
        $this->assertEquals(4, $fourth->getFreshOrder());
    }

    /** @test */
    public function it_can_insert_model_after_another_in_another_group()
    {
        $first = SortableModel::find(1);
        $second = SortableModel::find(6);
        $third = SortableModel::find(7);
        $first->moveAfter($second);

        $this->assertEquals(2, $first->getFreshOrder());
        $this->assertEquals(3, $third->getFreshOrder());
    }

    /** @test */
    public function it_can_insert_model_before_another_in_another_group()
    {
        $first = SortableModel::find(1);
        $second = SortableModel::find(2);
        $third = SortableModel::find(7);

        $third->moveBefore($second);

        $this->assertEquals(1, $first->getFreshOrder());
        $this->assertEquals(3, $second->getFreshOrder());
        $this->assertEquals(2, $third->getFreshOrder());
    }

    /** @test */
    public function it_changes_the_date_when_inserted_after()
    {
        $first = SortableModel::find(1);
        $this->assertTrue(Carbon::today()->isSameDay($first->date));

        $second = SortableModel::find(6);
        $this->assertTrue(Carbon::tomorrow()->isSameDay($second->date));

        $first->moveAfter($second);
        $this->assertTrue($first->refresh()->date->isSameDay(Carbon::tomorrow()));
    }

    /** @test */
    public function it_changes_the_date_when_inserted_before()
    {
        $first = SortableModel::find(1);
        $this->assertTrue(Carbon::today()->isSameDay($first->date));

        $second = SortableModel::find(6);
        $this->assertTrue(Carbon::tomorrow()->isSameDay($second->date));

        $second->moveAfter($first);
        $this->assertTrue($first->refresh()->date->isSameDay(Carbon::today()));
    }

    /** @test */
    public function it_sets_the_order_to_one_in_a_new_group()
    {
        $model = SortableModel::create([
            'name' => 1,
            'date' => Carbon::now()->addDays(10),
        ]);

        $this->assertEquals(1, $model->priority);
    }

    /** @test */
    public function it_sets_the_order_to_the_highest_in_an_existing_group()
    {
        $model = SortableModel::create([
            'name' => 1,
            'date' => Carbon::now(),
        ]);

        $this->assertEquals(6, $model->priority);
    }

    /** @test */
    public function it_updates_the_order_when_date_is_changed()
    {
        $first = SortableModel::create([
            'name' => 1,
            'date' => Carbon::now(),
        ]);

        $second = SortableModel::create([
            'name' => 1,
            'date' => Carbon::tomorrow(),
        ]);

        $this->assertEquals(6, $second->priority);
        $this->assertEquals(6, $first->priority);

        $first->update([
            'date' => Carbon::tomorrow(),
        ]);

        $this->assertNotEquals(6, $first->priority);
        $this->assertEquals(7, $first->priority);
    }
}
