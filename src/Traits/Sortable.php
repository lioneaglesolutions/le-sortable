<?php

namespace Lioneagle\LeSortable\Traits;

use Illuminate\Database\Eloquent\Builder;
use Lioneagle\LeSortable\Contracts\Sortable as SortableInterface;

trait Sortable
{
    protected string $sortableColumn = 'order';

    protected string $dateColumn = 'start_date';

    public static function bootSortable(): void
    {
        self::creating(function (SortableInterface $model) {
            $model->setOrderLast();
        });
        self::updating(function (SortableInterface $model) {
            if ($model->isDirty($model->getDateColumn())) {
                $model->setOrderLast();
            }
        });
    }

    public function setOrderLast()
    {
        $this->setOrder($this->getNextOrder());
    }

    public function newSortQuery(): Builder
    {
        return self::query();
    }

    public function getCurrentHighestOrder()
    {
        return $this->newSortQuery()->max($this->getSortableColumn());
    }

    public function getNextOrder(): int
    {
        return $this->getCurrentHighestOrder() + 1;
    }

    public function moveBefore(SortableInterface $model)
    {
        if (! $this->onSameDayAs($model)) {
            $this->moveModelBeforeInAnotherGroup($model);
        } else {
            $currentOrder = $this->getOrder();

            $newOrder = $model->getOrder();

            $this->newSortQuery()
                ->where($this->getSortableColumn(), '<', $currentOrder)
                ->where($this->getSortableColumn(), '>=', $newOrder)
                ->increment($this->getSortableColumn());

            $this->setOrder($newOrder)->save();
        }
    }

    public function moveAfter(SortableInterface $model)
    {
        if (! $this->onSameDayAs($model)) {
            $this->moveModelAfterInAnotherGroup($model);
        } else {
            $newOrder = $model->getOrder();

            $currentOrder = $this->getOrder();

            $this->newSortQuery()
                ->where($this->getSortableColumn(), '>', $currentOrder)
                ->where($this->getSortableColumn(), '<=', $newOrder)
                ->decrement($this->getSortableColumn());

            $this->setOrder($newOrder)->save();
        }
    }

    public function onSameDayAs(SortableInterface $model): bool
    {
        return $this->{$this->getDateColumn()}->isSameDay($model->{$this->getDateColumn()});
    }

    public function getOrder($fresh = false): int
    {
        if ($fresh) {
            $this->refresh();
        }

        return $this->{$this->getSortableColumn()};
    }

    public function getFreshOrder(): int
    {
        return $this->getOrder(true);
    }

    protected function moveModelBeforeInAnotherGroup(SortableInterface $model)
    {
        $newOrder = $model->getOrder();

        $currentOrder = $this->getOrder();

        $this->update([$this->getDateColumn() => $model->{$this->getDateColumn()}]);

        $this->newSortQuery()
            ->where($this->getSortableColumn(), '>=', $newOrder)
            ->increment($this->getSortableColumn());

        $this->setOrder($newOrder)->save();
    }

    protected function moveModelAfterInAnotherGroup(SortableInterface $model)
    {
        $newOrder = $model->getOrder() + 1;

        $currentOrder = $this->getOrder();

        dump('init order: ' . $this->order);

        $this->update([$this->getDateColumn() => $model->{$this->getDateColumn()}]);

        $this->newSortQuery()
            ->where($this->getSortableColumn(), '>=', $newOrder)
            // ->where($this->getKeyName(), '!=', $this->getKey())
            ->increment($this->getSortableColumn());

        $this->newSortQuery()
            ->where($this->getSortableColumn(), '>', $currentOrder)
            ->where($this->getSortableColumn(), '<=', $newOrder)
            ->decrement($this->getSortableColumn());

        dump('order in memory: ' . $this->order);

        dump(
            $this->newSortQuery()->where($this->getSortableColumn(), '>=', $newOrder)->where('id', $this->id)->pluck(
                'order'
            )->first()
        );
        $this->setOrder($newOrder)->save();
        dump('order after saving: ' . $this->order);
        dump(
            $this->newSortQuery()->where($this->getSortableColumn(), '>=', $newOrder)->where('id', $this->id)->pluck(
                'order'
            )->first()
        );
    }

    protected function getSortableColumn(): string
    {
        return $this->sortableColumn;
    }

    protected function getDateColumn(): string
    {
        return $this->dateColumn;
    }

    protected function setOrder(int $order): static
    {
        $this->fill([$this->getSortableColumn() => $order]);

        return $this;
    }
}
