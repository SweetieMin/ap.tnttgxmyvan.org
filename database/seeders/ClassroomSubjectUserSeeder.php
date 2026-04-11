<?php

namespace Database\Seeders;

use App\Models\ClassroomSubject;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClassroomSubjectUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mappings = [
            'MV-LTTT' => ['MV03010574'],
            'MV-KHDP' => ['MV03010574'],
            'MV-NUTD' => ['MV10090727'],
            'MV-MORS' => ['MV14070797'],
            'MV-LSCD' => ['MV01109999'],
            'MV-NGDX' => ['MV22109686'],
            'MV-NGTP' => ['MV04020712'],
            'MV-PPTN' => ['MV26030101'],
            'MV-TTDD' => ['MV26030101'],
            'MV-DAUD' => ['MV26089924'],
            'MV-BHSH' => ['MV21099898'],
            'MV-MATT' => ['MV11100101'],
            'MV-DSTT' => ['MV10121898'],
            'MV-DUNG' => ['MV01070274', 'MV10280202'],
        ];

        foreach ($mappings as $subjectCode => $usernames) {
            $subject = Subject::where('code', $subjectCode)->first();

            if (! $subject) {
                continue;
            }

            $usernames = (array) $usernames;

            $users = User::whereIn('username', $usernames)->pluck('id');

            if ($users->isEmpty()) {
                continue;
            }

            $classroomSubjects = ClassroomSubject::where('subject_id', $subject->id)->get();

            foreach ($classroomSubjects as $classroomSubject) {
                $classroomSubject->teachers()->syncWithoutDetaching($users->toArray());
            }
        }
    }
}
