<?php

namespace Database\Seeders;

use Butler\Service\Models\Consumer;
use Illuminate\Database\Seeder;

class DefaultDatabaseSeeder extends Seeder
{
    public function run()
    {
        if (app()->isLocal()) {
            $this->seedDeveloperConsumer();
        }
    }

    private function seedDeveloperConsumer(): void
    {
        Consumer::firstOrCreate(['name' => 'developer'])
            ->tokens()
            ->firstOrCreate([
                'token' => hash('sha256', 'secret'),
                'abilities' => ['*'],
            ]);
    }
}
