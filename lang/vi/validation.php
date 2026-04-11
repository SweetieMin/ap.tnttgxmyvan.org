<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Dòng Ngôn Ngữ Xác Thực Dữ Liệu
    |--------------------------------------------------------------------------
    |
    | Những dòng ngôn ngữ sau chứa các thông báo lỗi mặc định được lớp
    | validator sử dụng. Một số rule có nhiều phiên bản khác nhau như nhóm
    | rule về kích thước. Bạn có thể chỉnh sửa các thông báo này tùy ý.
    |
    */

    'accepted' => ':attribute phải được chấp nhận.',
    'accepted_if' => ':attribute phải được chấp nhận khi :other là :value.',
    'active_url' => ':attribute phải là một URL hợp lệ.',
    'after' => ':attribute phải là ngày sau :date.',
    'after_or_equal' => ':attribute phải là ngày sau hoặc bằng :date.',
    'alpha' => ':attribute chỉ được chứa chữ cái.',
    'alpha_dash' => ':attribute chỉ được chứa chữ cái, số, dấu gạch ngang và dấu gạch dưới.',
    'alpha_num' => ':attribute chỉ được chứa chữ cái và số.',
    'any_of' => ':attribute không hợp lệ.',
    'array' => ':attribute phải là một mảng.',
    'ascii' => ':attribute chỉ được chứa ký tự chữ, số và ký hiệu ASCII một byte.',
    'before' => ':attribute phải là ngày trước :date.',
    'before_or_equal' => ':attribute phải là ngày trước hoặc bằng :date.',
    'between' => [
        'array' => ':attribute phải có từ :min đến :max phần tử.',
        'file' => ':attribute phải có dung lượng từ :min đến :max kilobyte.',
        'numeric' => ':attribute phải nằm trong khoảng từ :min đến :max.',
        'string' => ':attribute phải có từ :min đến :max ký tự.',
    ],
    'boolean' => ':attribute phải là true hoặc false.',
    'can' => ':attribute chứa giá trị không được phép.',
    'confirmed' => 'Xác nhận :attribute không khớp.',
    'contains' => ':attribute đang thiếu một giá trị bắt buộc.',
    'current_password' => 'Mật khẩu hiện tại không chính xác.',
    'date' => ':attribute phải là ngày hợp lệ.',
    'date_equals' => ':attribute phải là ngày bằng :date.',
    'date_format' => ':attribute phải đúng định dạng :format.',
    'decimal' => ':attribute phải có :decimal chữ số thập phân.',
    'declined' => ':attribute phải bị từ chối.',
    'declined_if' => ':attribute phải bị từ chối khi :other là :value.',
    'different' => ':attribute và :other phải khác nhau.',
    'digits' => ':attribute phải có :digits chữ số.',
    'digits_between' => ':attribute phải có từ :min đến :max chữ số.',
    'dimensions' => ':attribute có kích thước ảnh không hợp lệ.',
    'distinct' => ':attribute có giá trị bị trùng lặp.',
    'doesnt_contain' => ':attribute không được chứa bất kỳ giá trị nào sau đây: :values.',
    'doesnt_end_with' => ':attribute không được kết thúc bằng một trong các giá trị sau: :values.',
    'doesnt_start_with' => ':attribute không được bắt đầu bằng một trong các giá trị sau: :values.',
    'email' => ':attribute phải là địa chỉ email hợp lệ.',
    'encoding' => ':attribute phải được mã hóa theo :encoding.',
    'ends_with' => ':attribute phải kết thúc bằng một trong các giá trị sau: :values.',
    'enum' => ':attribute đã chọn không hợp lệ.',
    'exists' => ':attribute đã chọn không hợp lệ.',
    'extensions' => ':attribute phải có một trong các phần mở rộng sau: :values.',
    'file' => ':attribute phải là một tệp.',
    'filled' => ':attribute phải có giá trị.',
    'gt' => [
        'array' => ':attribute phải có nhiều hơn :value phần tử.',
        'file' => ':attribute phải lớn hơn :value kilobyte.',
        'numeric' => ':attribute phải lớn hơn :value.',
        'string' => ':attribute phải dài hơn :value ký tự.',
    ],
    'gte' => [
        'array' => ':attribute phải có ít nhất :value phần tử.',
        'file' => ':attribute phải lớn hơn hoặc bằng :value kilobyte.',
        'numeric' => ':attribute phải lớn hơn hoặc bằng :value.',
        'string' => ':attribute phải dài tối thiểu :value ký tự.',
    ],
    'hex_color' => ':attribute phải là mã màu thập lục phân hợp lệ.',
    'image' => ':attribute phải là hình ảnh.',
    'in' => ':attribute đã chọn không hợp lệ.',
    'in_array' => ':attribute phải tồn tại trong :other.',
    'in_array_keys' => ':attribute phải chứa ít nhất một trong các khóa sau: :values.',
    'integer' => ':attribute phải là số nguyên.',
    'ip' => ':attribute phải là địa chỉ IP hợp lệ.',
    'ipv4' => ':attribute phải là địa chỉ IPv4 hợp lệ.',
    'ipv6' => ':attribute phải là địa chỉ IPv6 hợp lệ.',
    'json' => ':attribute phải là chuỗi JSON hợp lệ.',
    'list' => ':attribute phải là một danh sách.',
    'lowercase' => ':attribute phải viết bằng chữ thường.',
    'lt' => [
        'array' => ':attribute phải có ít hơn :value phần tử.',
        'file' => ':attribute phải nhỏ hơn :value kilobyte.',
        'numeric' => ':attribute phải nhỏ hơn :value.',
        'string' => ':attribute phải ngắn hơn :value ký tự.',
    ],
    'lte' => [
        'array' => ':attribute không được có nhiều hơn :value phần tử.',
        'file' => ':attribute phải nhỏ hơn hoặc bằng :value kilobyte.',
        'numeric' => ':attribute phải nhỏ hơn hoặc bằng :value.',
        'string' => ':attribute phải ngắn hơn hoặc bằng :value ký tự.',
    ],
    'mac_address' => ':attribute phải là địa chỉ MAC hợp lệ.',
    'max' => [
        'array' => ':attribute không được có quá :max phần tử.',
        'file' => ':attribute không được lớn hơn :max kilobyte.',
        'numeric' => ':attribute không được lớn hơn :max.',
        'string' => ':attribute không được dài quá :max ký tự.',
    ],
    'max_digits' => ':attribute không được có quá :max chữ số.',
    'mimes' => ':attribute phải là tệp có định dạng: :values.',
    'mimetypes' => ':attribute phải là tệp có kiểu: :values.',
    'min' => [
        'array' => ':attribute phải có ít nhất :min phần tử.',
        'file' => ':attribute phải có dung lượng tối thiểu :min kilobyte.',
        'numeric' => ':attribute phải tối thiểu là :min.',
        'string' => ':attribute phải có ít nhất :min ký tự.',
    ],
    'min_digits' => ':attribute phải có ít nhất :min chữ số.',
    'missing' => ':attribute không được xuất hiện.',
    'missing_if' => ':attribute phải không được xuất hiện khi :other là :value.',
    'missing_unless' => ':attribute phải không được xuất hiện trừ khi :other là :value.',
    'missing_with' => ':attribute phải không được xuất hiện khi :values có mặt.',
    'missing_with_all' => ':attribute phải không được xuất hiện khi tất cả :values có mặt.',
    'multiple_of' => ':attribute phải là bội số của :value.',
    'not_in' => ':attribute đã chọn không hợp lệ.',
    'not_regex' => 'Định dạng :attribute không hợp lệ.',
    'numeric' => ':attribute phải là một số.',
    'password' => [
        'letters' => ':attribute phải chứa ít nhất một chữ cái.',
        'mixed' => ':attribute phải chứa ít nhất một chữ hoa và một chữ thường.',
        'numbers' => ':attribute phải chứa ít nhất một chữ số.',
        'symbols' => ':attribute phải chứa ít nhất một ký hiệu.',
        'uncompromised' => ':attribute đã xuất hiện trong một vụ rò rỉ dữ liệu. Vui lòng chọn :attribute khác.',
    ],
    'present' => ':attribute phải xuất hiện trong dữ liệu.',
    'present_if' => ':attribute phải xuất hiện khi :other là :value.',
    'present_unless' => ':attribute phải xuất hiện trừ khi :other là :value.',
    'present_with' => ':attribute phải xuất hiện khi :values có mặt.',
    'present_with_all' => ':attribute phải xuất hiện khi tất cả :values có mặt.',
    'prohibited' => ':attribute không được phép xuất hiện.',
    'prohibited_if' => ':attribute không được phép xuất hiện khi :other là :value.',
    'prohibited_if_accepted' => ':attribute không được phép xuất hiện khi :other được chấp nhận.',
    'prohibited_if_declined' => ':attribute không được phép xuất hiện khi :other bị từ chối.',
    'prohibited_unless' => ':attribute không được phép xuất hiện trừ khi :other nằm trong :values.',
    'prohibits' => ':attribute không cho phép :other xuất hiện.',
    'regex' => 'Định dạng :attribute không hợp lệ.',
    'required' => ':attribute là bắt buộc.',
    'required_array_keys' => ':attribute phải chứa các khóa sau: :values.',
    'required_if' => ':attribute là bắt buộc khi :other là :value.',
    'required_if_accepted' => ':attribute là bắt buộc khi :other được chấp nhận.',
    'required_if_declined' => ':attribute là bắt buộc khi :other bị từ chối.',
    'required_unless' => ':attribute là bắt buộc trừ khi :other nằm trong :values.',
    'required_with' => ':attribute là bắt buộc khi :values có mặt.',
    'required_with_all' => ':attribute là bắt buộc khi tất cả :values có mặt.',
    'required_without' => ':attribute là bắt buộc khi :values không có mặt.',
    'required_without_all' => ':attribute là bắt buộc khi không có giá trị nào trong :values xuất hiện.',
    'same' => ':attribute phải khớp với :other.',
    'size' => [
        'array' => ':attribute phải chứa :size phần tử.',
        'file' => ':attribute phải có dung lượng :size kilobyte.',
        'numeric' => ':attribute phải bằng :size.',
        'string' => ':attribute phải dài :size ký tự.',
    ],
    'starts_with' => ':attribute phải bắt đầu bằng một trong các giá trị sau: :values.',
    'string' => ':attribute phải là chuỗi.',
    'timezone' => ':attribute phải là múi giờ hợp lệ.',
    'unique' => ':attribute đã tồn tại.',
    'uploaded' => 'Tải lên :attribute thất bại.',
    'uppercase' => ':attribute phải viết bằng chữ hoa.',
    'url' => ':attribute phải là URL hợp lệ.',
    'ulid' => ':attribute phải là ULID hợp lệ.',
    'uuid' => ':attribute phải là UUID hợp lệ.',

    /*
    |--------------------------------------------------------------------------
    | Dòng Ngôn Ngữ Xác Thực Tùy Chỉnh
    |--------------------------------------------------------------------------
    |
    | Tại đây bạn có thể khai báo các thông báo xác thực tùy chỉnh cho từng
    | thuộc tính theo quy ước "attribute.rule". Cách này giúp bạn dễ dàng đặt
    | thông báo riêng cho từng rule cụ thể.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tên Thuộc Tính Tùy Chỉnh
    |--------------------------------------------------------------------------
    |
    | Những dòng ngôn ngữ sau được dùng để thay thế placeholder của thuộc tính
    | bằng tên thân thiện hơn với người dùng, ví dụ "địa chỉ email" thay cho
    | "email". Điều này giúp thông báo lỗi dễ hiểu và tự nhiên hơn.
    |
    */

    'attributes' => [
        'holy_name' => 'tên thánh',
        'name' => 'họ và tên',
        'birthday' => 'ngày sinh',
        'username' => 'tên đăng nhập',
        'email' => 'địa chỉ email',
        'password' => 'mật khẩu',
        'password_confirmation' => 'xác nhận mật khẩu',
    ],

];
