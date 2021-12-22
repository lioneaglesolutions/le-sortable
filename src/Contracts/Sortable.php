<?php

namespace Lioneagle\LeSortable\Contracts;

/**
 * @mixin \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\
 */
interface Sortable
{
    public function setOrderLast();
}
