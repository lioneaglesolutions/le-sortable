<?php

namespace Lioneagle\LeSortable\Tests;

use App\Models\Task;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Lioneagle\LeSortable\Tests\Fixtures\SortableModel;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createDatabaseTables($this->app);
        $this->seedDatabase();
    }

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        Model::unguard();

        $config = $app->get('config');

        $config->set('database.default', 'testing');
        $config->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
    }

    protected function createDatabaseTables(Application $application)
    {
        $application['db']->connection()->getSchemaBuilder()->create('sortable_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('priority');
            $table->dateTime('date');
            $table->timestamps();
        });
    }

    protected function seedDatabase()
    {
        $period = CarbonPeriod::create(Carbon::now(), Carbon::now()->addDays(5));

        collect($period)->each(function (Carbon $date) {
            collect(range(1, 5))->each(function ($index) use ($date) {
                SortableModel::create([
                    'name' => $index,
                    'date' => $date,
                ]);
            });
        });
    }
}
