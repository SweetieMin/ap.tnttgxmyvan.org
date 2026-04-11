<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'holy_name' => 'Giuse',
                'name' => 'Đặng Đình Viên',
                'username' => 'mv21081010',
                'email' => 'tnttgxmyvan@gmail.com',
                'password' => '12345',
                'role' => 'admin',
            ],

            [
                'holy_name' => 'Maria',
                'name' => 'Nguyễn Thị Bích Liên',
                'birthday' => '1996-10-22',
                'username' => 'MV22109686',
                'email' => null,
                'password' => 'MV22109686',
                'role' => 'giáo viên',
            ],
            [
                'holy_name' => 'Monica',
                'name' => 'Nguyễn Hoàng Kim Dung',
                'birthday' => '2001-03-26',
                'username' => 'MV26030101',
                'email' => 'monicakimdung2603@gmail.com',
                'password' => 'MV26030101',
                'role' => 'giáo viên',
            ],
            [
                'holy_name' => 'Maria',
                'name' => 'Vũ Hồng Phúc',
                'birthday' => '2005-01-03',
                'username' => 'MV03010574',
                'email' => 'vhphucs315@gmail.com',
                'password' => 'MV03010574',
                'role' => 'giáo viên',
            ],
            [
                'holy_name' => 'Toma',
                'name' => 'Vũ Minh Đức',
                'birthday' => '1999-10-01',
                'username' => 'MV01109999',
                'email' => 'Paulminhduc99@gmail.com',
                'password' => 'MV01109999',
                'role' => 'giáo viên',
            ],
            [
                'holy_name' => 'Martino',
                'name' => 'Đặng Đình Quý',
                'birthday' => '2007-07-14',
                'username' => 'MV14070797',
                'email' => 'dangdinhquy140707@gmail.com',
                'password' => 'MV14070797',
                'role' => 'giáo viên',
            ],
            [
                'holy_name' => 'Teresa',
                'name' => 'Nguyễn Thị Thúy Vy',
                'birthday' => '2001-10-11',
                'username' => 'MV11100101',
                'email' => 'nguyenttvy1110@gmail.com',
                'password' => 'MV11100101',
                'role' => 'giáo viên',
            ],
            [
                'holy_name' => 'Phero',
                'name' => 'Nguyễn Minh Khôi',
                'birthday' => '2007-09-10',
                'username' => 'MV10090727',
                'email' => null,
                'password' => 'MV10090727',
                'role' => 'giáo viên',
            ],
            [
                'holy_name' => 'Michael',
                'name' => 'Trần Thiên Thanh Tú',
                'birthday' => '2007-02-04',
                'username' => 'MV04020712',
                'email' => null,
                'password' => 'MV04020712',
                'role' => 'giáo viên',
            ],
            [
                'holy_name' => 'Toma',
                'name' => 'Vũ Tấn Lộc',
                'birthday' => '2002-07-01',
                'username' => 'MV01070274',
                'email' => 'locvu406@gmail.com',
                'password' => 'MV01070274',
                'role' => 'giáo viên',
            ],
            [
                'holy_name' => 'Đaminh',
                'name' => 'Lê Nguyễn Quang Minh',
                'birthday' => '2002-10-28',
                'username' => 'MV10280202',
                'email' => 'coblustar@gmail.com',
                'password' => 'MV10280202',
                'role' => 'giáo viên',
            ],
            [
                'holy_name' => 'Maria Monica',
                'name' => 'Nguyễn Thị Kim Anh',
                'birthday' => '1999-08-26',
                'username' => 'MV26089924',
                'email' => 'nguyenthikimanh1012000@gmail.com',
                'password' => 'MV26089924',
                'role' => 'giáo viên',
            ],
            [
                'holy_name' => 'Vinh Sơn',
                'name' => 'Đoàn Trường Nam',
                'birthday' => '1998-09-21',
                'username' => 'MV21099898',
                'email' => 'namvn5555@gmail.com',
                'password' => 'MV21099898',
                'role' => 'giáo viên',
            ],


            [
                'holy_name' => 'Maria',
                'name' => 'Nguyễn Ngọc Phương Thảo',
                'username' => 'MV08081159',
                'password' => 'MV08081159',
                'role' => 'thiếu nhi',
                'birthday' => '2011-08-08',
            ],
            [
                'holy_name' => 'Maria',
                'name' => 'Nguyễn Phương Uyên',
                'username' => 'MV08111169',
                'password' => 'MV08111169',
                'role' => 'thiếu nhi',
                'birthday' => '2011-11-08',
            ],
            [
                'holy_name' => 'Phê-rô',
                'name' => 'Nguyễn Triệu Hoàng',
                'username' => 'MV02021126',
                'password' => 'MV02021126',
                'role' => 'thiếu nhi',
                'birthday' => '2011-02-02',
            ],
            [
                'holy_name' => 'Phê-rô',
                'name' => 'Vũ Hoàng Khánh',
                'username' => 'MV27101163',
                'password' => 'MV27101163',
                'role' => 'thiếu nhi',
                'birthday' => '2011-10-27',
            ],
            [
                'holy_name' => 'Gioan Baotixita',
                'name' => 'Nguyễn Hoàng Phúc',
                'username' => 'MV01101110',
                'password' => 'MV01101110',
                'role' => 'thiếu nhi',
                'birthday' => '2011-10-01',
            ],
            [
                'holy_name' => 'Giuse',
                'name' => 'Phan Văn Khải',
                'username' => 'MV07051112',
                'password' => 'MV07051112',
                'role' => 'thiếu nhi',
                'birthday' => '2011-05-07',
            ],
            [
                'holy_name' => 'Toma',
                'name' => 'Nguyễn Thế Hiển',
                'username' => 'MV14061159',
                'password' => 'MV14061159',
                'role' => 'thiếu nhi',
                'birthday' => '2011-06-14',
            ],
            [
                'holy_name' => 'Tôma',
                'name' => 'Vũ Anh Kiệt',
                'username' => 'MV24081163',
                'password' => 'MV24081163',
                'role' => 'thiếu nhi',
                'birthday' => '2011-08-24',
            ],
            [
                'holy_name' => 'Aselmo',
                'name' => 'Trịnh Quốc Việt',
                'username' => 'MV18040673',
                'password' => 'MV18040673',
                'role' => 'thiếu nhi',
                'birthday' => '2006-04-18',
            ],
            [
                'holy_name' => 'Đaminh',
                'name' => 'Đặng Đức Thắng',
                'username' => 'MV15040892',
                'password' => 'MV15040892',
                'role' => 'thiếu nhi',
                'birthday' => '2008-04-15',
            ],
            [
                'holy_name' => 'Maria',
                'name' => 'Nguyễn Mộng Thủy Tiên',
                'username' => 'MV01080978',
                'password' => 'MV01080978',
                'role' => 'thiếu nhi',
                'birthday' => '2009-08-01',
            ],
            [
                'holy_name' => 'Đaminh',
                'name' => 'Nguyễn Gia Huy',
                'username' => 'MV15031231',
                'password' => 'MV15031231',
                'role' => 'thiếu nhi',
                'birthday' => '2010-03-15',
            ],
            [
                'holy_name' => 'Giuse',
                'name' => 'Nguyễn Hoàng Minh Tuấn',
                'username' => 'MV11091196',
                'password' => 'MV11091196',
                'role' => 'thiếu nhi',
                'birthday' => '2011-09-11',
            ],
            [
                'holy_name' => 'Phanxico',
                'name' => 'Nguyễn Trung Nghĩa',
                'username' => 'MV15120828',
                'password' => 'MV15120828',
                'role' => 'thiếu nhi',
                'birthday' => '2008-12-15',
            ],
            [
                'holy_name' => 'Maria',
                'name' => 'Nguyễn Tú Quyên',
                'username' => 'MV20021150',
                'password' => 'MV20021150',
                'role' => 'thiếu nhi',
                'birthday' => '2011-02-20',
            ],
            [
                'holy_name' => 'Đaminh',
                'name' => 'Đặng Thiên Tuấn',
                'username' => 'MV06091085',
                'password' => 'MV06091085',
                'role' => 'thiếu nhi',
                'birthday' => '2010-09-06',
            ],
            [
                'holy_name' => 'Đaminh',
                'name' => 'Nguyễn Tấn Phát',
                'username' => 'MV19041078',
                'password' => 'MV19041078',
                'role' => 'thiếu nhi',
                'birthday' => '2010-04-19',
            ],
            [
                'holy_name' => 'Phê-rô',
                'name' => 'Vũ Quốc Việt',
                'username' => 'MV20011137',
                'password' => 'MV20011137',
                'role' => 'thiếu nhi',
                'birthday' => '2011-01-20',
            ],
        ];

        foreach ($users as $attributes) {

            $user = User::query()->updateOrCreate(
                ['username' => $attributes['username']],
                [
                    'holy_name' => $attributes['holy_name'],
                    'name' => $attributes['name'],
                    'email' => $attributes['email'] ?? null,
                    'birthday' => $attributes['birthday'] ?? null,
                    'username' => $attributes['username'],
                    'password' => $attributes['password'],
                ],
            );

            $user->syncRoles([$attributes['role']]);
        }
    }
}
