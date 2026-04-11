<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        collect(ScheduleSeeder::scheduleItems())
            ->unique('subject')
            ->values()
            ->each(function (array $item): void {
                Subject::query()->updateOrCreate(
                    ['name' => $item['subject']],
                    [
                        'status' => 'active',
                        'code' => $item['code'], 
                    ], 
                );
            });
    }
}
