<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\ClassroomSubject;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    /**
     * @return array<int, array{subject: string, date: string, start_time: string, end_time: string, status: string, type: string}>
     */
    public static function scheduleItems(): array
    {
        return [
            ['code' => 'MV-LTTT', 'subject' => 'Lý tưởng, tôn chỉ, 10 điều tâm niệm', 'date' => '2026-04-11', 'start_time' => '19:30:00', 'end_time' => '20:15:00', 'status' => 'pending', 'type' => 'study', 'have_record' => true],

            ['code' => 'MV-KHDP', 'subject' => 'Khẩu hiệu, đồng phục, ý nghĩa màu khăn và logo TNTT', 'date' => '2026-04-11', 'start_time' => '20:15:00', 'end_time' => '21:00:00', 'status' => 'pending', 'type' => 'study', 'have_record' => true],

            ['code' => 'MV-NUTD', 'subject' => 'Nút dây', 'date' => '2026-04-16', 'start_time' => '19:30:00', 'end_time' => '21:00:00', 'status' => 'pending', 'type' => 'study', 'have_record' => true],

            ['code' => 'MV-MORS', 'subject' => 'Morse', 'date' => '2026-04-18', 'start_time' => '19:30:00', 'end_time' => '21:00:00', 'status' => 'pending', 'type' => 'study', 'have_record' => true],

            ['code' => 'MV-LSCD', 'subject' => 'Lịch sử cứu độ', 'date' => '2026-04-23', 'start_time' => '19:30:00', 'end_time' => '20:15:00', 'status' => 'pending', 'type' => 'study', 'have_record' => true],

            ['code' => 'MV-NGDX', 'subject' => 'Nguồn gốc, danh xưng, mục đích TNTT', 'date' => '2026-04-23', 'start_time' => '20:15:00', 'end_time' => '21:00:00', 'status' => 'pending', 'type' => 'study', 'have_record' => true],

            ['code' => 'MV-NGTP', 'subject' => 'Nghiêm tập', 'date' => '2026-04-25', 'start_time' => '19:30:00', 'end_time' => '21:00:00', 'status' => 'pending', 'type' => 'study', 'have_record' => true],

            ['code' => 'MV-PPTN', 'subject' => 'Phương pháp TNTT', 'date' => '2026-04-30', 'start_time' => '19:30:00', 'end_time' => '20:15:00', 'status' => 'pending', 'type' => 'study', 'have_record' => true],

            ['code' => 'MV-TTDD', 'subject' => 'Tinh thần đoàn đội, vai trò ĐT, ĐP', 'date' => '2026-04-30', 'start_time' => '20:15:00', 'end_time' => '21:00:00', 'status' => 'pending', 'type' => 'study', 'have_record' => true],

            ['code' => 'MV-NUTD', 'subject' => 'Nút dây', 'date' => '2026-05-02', 'start_time' => '19:30:00', 'end_time' => '21:00:00', 'status' => 'pending', 'type' => 'study', 'have_record' => true],

            ['code' => 'MV-DAUD', 'subject' => 'Dấu đường', 'date' => '2026-05-07', 'start_time' => '19:30:00', 'end_time' => '20:15:00', 'status' => 'pending', 'type' => 'study', 'have_record' => true],

            ['code' => 'MV-BHSH', 'subject' => 'Bài hát sinh hoạt + nghi thức', 'date' => '2026-05-07', 'start_time' => '20:15:00', 'end_time' => '21:00:00', 'status' => 'pending', 'type' => 'study', 'have_record' => true],

            ['code' => 'MV-MORS', 'subject' => 'Morse', 'date' => '2026-05-09', 'start_time' => '19:30:00', 'end_time' => '21:00:00', 'status' => 'pending', 'type' => 'study', 'have_record' => true],

            ['code' => 'MV-MATT', 'subject' => 'Mật thư', 'date' => '2026-05-14', 'start_time' => '19:30:00', 'end_time' => '21:00:00', 'status' => 'pending', 'type' => 'study', 'have_record' => true],

            ['code' => 'MV-NGTP', 'subject' => 'Nghiêm tập', 'date' => '2026-05-16', 'start_time' => '19:30:00', 'end_time' => '21:00:00', 'status' => 'pending', 'type' => 'study', 'have_record' => true],

            ['code' => 'MV-MATT', 'subject' => 'Mật thư', 'date' => '2026-05-21', 'start_time' => '19:30:00', 'end_time' => '21:00:00', 'status' => 'pending', 'type' => 'study', 'have_record' => true],

            ['code' => 'MV-DSTT', 'subject' => 'Đời sống trại', 'date' => '2026-05-23', 'start_time' => '19:30:00', 'end_time' => '21:00:00', 'status' => 'pending', 'type' => 'study', 'have_record' => true],

            ['code' => 'MV-DUNG', 'subject' => 'Dựng lều', 'date' => '2026-05-24', 'start_time' => '08:00:00', 'end_time' => '10:00:00', 'status' => 'pending', 'type' => 'study', 'have_record' => true],

            ['code' => 'MV-MORS', 'subject' => 'Morse', 'date' => '2026-05-28', 'start_time' => '19:30:00', 'end_time' => '21:00:00', 'status' => 'pending', 'type' => 'study', 'have_record' => true],

            ['code' => 'MV-NUTD', 'subject' => 'Nút dây', 'date' => '2026-05-30', 'start_time' => '19:30:00', 'end_time' => '21:00:00', 'status' => 'pending', 'type' => 'study', 'have_record' => true],

            ['code' => 'MV-DUNG', 'subject' => 'Dựng lều', 'date' => '2026-05-31', 'start_time' => '08:00:00', 'end_time' => '10:00:00', 'status' => 'pending', 'type' => 'study', 'have_record' => true],

            ['code' => 'MV-NGTP', 'subject' => 'Nghiêm tập', 'date' => '2026-06-04', 'start_time' => '19:30:00', 'end_time' => '21:00:00', 'status' => 'pending', 'type' => 'study', 'have_record' => true],

            ['code' => 'MV-THLT', 'subject' => 'Thi lý thuyết', 'date' => '2026-06-06', 'start_time' => '19:30:00', 'end_time' => '21:00:00', 'status' => 'pending', 'type' => 'exam', 'have_record' => false],
            ['code' => 'MV-THTH', 'subject' => 'Thi thực hành', 'date' => '2026-06-07', 'start_time' => '08:00:00', 'end_time' => '10:00:00', 'status' => 'pending', 'type' => 'exam', 'have_record' => false],
            ['code' => 'MV-DDCB', 'subject' => 'Dặn dò + chuẩn bị', 'date' => '2026-06-11', 'start_time' => '19:30:00', 'end_time' => '21:00:00', 'status' => 'pending', 'type' => 'reminder', 'have_record' => false],
            ['code' => 'MV-DITR', 'subject' => 'Đi trại', 'date' => '2026-06-13', 'start_time' => null, 'end_time' => null, 'status' => 'pending', 'type' => 'camp', 'have_record' => false],
        ];
    }

    public function run(): void
    {

        $classroom = Classroom::query()->firstOrCreate(
            ['code' => 'MV-CB26'],
            [
                'name' => 'Lớp Căn Bản 2026',
                'description' => 'Lớp cho Đội Trưởng 2026',
                'start_date' => '2026-04-11',
                'end_date' => '2026-06-07',
                'status' => 'open',
            ],
        );

        $youthIds = User::role('thiếu nhi')->pluck('id')->all();

        if (! empty($youthIds)) {
            $classroom->youths()->syncWithoutDetaching($youthIds);
        }

        foreach (self::scheduleItems() as $attributes) {
            $subject = Subject::query()->firstOrCreate(
                ['name' => $attributes['subject']],
                [
                    'status' => 'active',
                ],
            );

            $assignment = ClassroomSubject::query()->firstOrCreate(
                [
                    'classroom_id' => $classroom->id,
                    'subject_id' => $subject->id,
                ],
                [
                    'status' => 'active',
                ],
            );

            $date = Carbon::parse($attributes['date']);

            Schedule::query()->updateOrCreate(
                [
                    'classroom_subject_id' => $assignment->id,
                    'date' => $attributes['date'],
                ],
                [
                    'start_time' => $attributes['start_time'],
                    'end_time' => $attributes['end_time'],
                    'type' => $attributes['type'],
                    'status' => $attributes['status'],
                    'date_end_spirit' => $date->copy()->addDay(),
                    'date_end_practice_theory' => $date->copy()->addDays(7),
                ],
            );
        }
    }
}
