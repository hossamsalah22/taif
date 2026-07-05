<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'حقل :attribute يجب أن يتم قبوله.',
    'accepted_if' => 'يجب قبول الحقل :attribute عندما يكون :other هو :value.',
    'active_url' => 'حقل :attribute يجب أن يكون عنوان URL صحيحًا.',
    'after' => 'يجب أن يكون الحقل :attribute تاريخًا بعد :date.',
    'after_or_equal' => 'يجب أن يكون الحقل :attribute تاريخًا بعد أو يساوي :date.',
    'alpha' => 'يجب أن يحتوي الحقل :attribute على أحرف فقط.',
    'alpha_dash' => 'يجب أن يحتوي الحقل :attribute على أحرف وأرقام وشرطات وخطوط سفلية فقط.',
    'alpha_num' => 'يجب أن يحتوي الحقل :attribute على أحرف وأرقام فقط.',
    'any_of' => 'حقل :attribute غير صالح.',
    'array' => 'يجب أن يكون الحقل :attribute مصفوفة.',
    'ascii' => 'يجب أن يحتوي الحقل :attribute على أحرف أبجدية رقمية أحادية البايت ورموز فقط.',
    'before' => 'يجب أن يكون الحقل :attribute تاريخًا قبل :date.',
    'before_or_equal' => 'يجب أن يكون الحقل :attribute تاريخًا قبل أو يساوي :date.',
    'between' => [
        'array' => 'يجب أن يحتوي الحقل :attribute على بين :min و :max عنصر.',
        'file' => 'يجب أن يكون الحقل :attribute بين :min و :max كيلوبايت.',
        'numeric' => 'يجب أن يكون الحقل :attribute بين :min و :max.',
        'string' => 'يجب أن يكون الحقل :attribute بين :min و :max حرفًا.',
    ],
    'boolean' => 'يجب أن تكون قيمة الحقل :attribute إما true أو false.',
    'can' => 'الحقل :attribute يحتوي على قيمة غير مصرح بها.',
    'confirmed' => 'تأكيد الحقل :attribute غير مطابق.',
    'contains' => 'يفتقر الحقل :attribute إلى قيمة مطلوبة.',
    'current_password' => 'كلمة المرور غير صحيحة.',
    'date' => 'الحقل :attribute ليس تاريخًا صالحًا.',
    'date_equals' => 'يجب أن يكون الحقل :attribute مطابقاً للتاريخ :date.',
    'date_format' => 'لا يتوافق الحقل :attribute مع الشكل :format.',
    'decimal' => 'يجب أن يحتوي الحقل :attribute على :decimal منازل عشرية.',
    'declined' => 'يجب رفض الحقل :attribute.',
    'declined_if' => 'يجب رفض الحقل :attribute عندما يكون :other هو :value.',
    'different' => 'يجب أن يكون الحقلان :attribute و :other مُختلفين.',
    'digits' => 'يجب أن يحتوي الحقل :attribute على :digits أرقام.',
    'digits_between' => 'يجب أن يحتوي الحقل :attribute بين :min و :max أرقام.',
    'dimensions' => 'أبعاد الصورة في الحقل :attribute غير صالحة.',
    'distinct' => 'للحقل :attribute قيمة مُكررة.',
    'doesnt_contain' => 'يجب ألا يحتوي الحقل :attribute على أي من التالي: :values.',
    'doesnt_end_with' => 'يجب ألا ينتهي الحقل :attribute بأي من التالي: :values.',
    'doesnt_start_with' => 'يجب ألا يبدأ الحقل :attribute بأي من التالي: :values.',
    'email' => 'يجب أن يكون الحقل :attribute عنوان بريد إلكتروني صالح.',
    'encoding' => 'يجب أن يتم تشفير الحقل :attribute بـ :encoding.',
    'ends_with' => 'يجب أن ينتهي الحقل :attribute بأحد القيم التالية: :values.',
    'enum' => 'الحقل :attribute المحدد غير صالح.',
    'exists' => 'الحقل :attribute المحدد غير صالح.',
    'extensions' => 'يجب أن يحتوي الحقل :attribute على إحدى الامتدادات التالية: :values.',
    'file' => 'يجب أن يكون الحقل :attribute ملفًا.',
    'filled' => 'يجب أن يحتوي الحقل :attribute على قيمة.',
    'gt' => [
        'array' => 'يجب أن يحتوي الحقل :attribute على أكثر من :value عناصر.',
        'file' => 'يجب أن يكون الحقل :attribute أكبر من :value كيلوبايت.',
        'numeric' => 'يجب أن يكون الحقل :attribute أكبر من :value.',
        'string' => 'يجب أن يكون طول الحقل :attribute أكثر من :value أحرف.',
    ],
    'gte' => [
        'array' => 'يجب أن يحتوي الحقل :attribute على الأقل على :value عناصر.',
        'file' => 'يجب أن يكون الحقل :attribute أكبر من أو يساوي :value كيلوبايت.',
        'numeric' => 'يجب أن يكون الحقل :attribute أكبر من أو يساوي :value.',
        'string' => 'يجب أن يكون طول الحقل :attribute أكبر من أو يساوي :value أحرف.',
    ],
    'hex_color' => 'يجب أن يكون الحقل :attribute لون سداسي عشري (hex) صالح.',
    'image' => 'يجب أن يكون الحقل :attribute صورة.',
    'in' => 'الحقل :attribute المحدد غير صالح.',
    'in_array' => 'الحقل :attribute غير موجود في :other.',
    'in_array_keys' => 'يجب أن يحتوي الحقل :attribute على مفتاح واحد على الأقل من التالي: :values.',
    'integer' => 'يجب أن يكون الحقل :attribute عددًا صحيحًا.',
    'ip' => 'يجب أن يكون الحقل :attribute عنوان IP صالحًا.',
    'ipv4' => 'يجب أن يكون الحقل :attribute عنوان IPv4 صالحًا.',
    'ipv6' => 'يجب أن يكون الحقل :attribute عنوان IPv6 صالحًا.',
    'json' => 'يجب أن يكون الحقل :attribute نصًا صالحًا من نوع JSON.',
    'list' => 'يجب أن يكون الحقل :attribute قائمة.',
    'lowercase' => 'يجب أن يكون الحقل :attribute بأحرف صغيرة.',
    'lt' => [
        'array' => 'يجب أن يحتوي الحقل :attribute على أقل من :value عناصر.',
        'file' => 'يجب أن يكون الحقل :attribute أصغر من :value كيلوبايت.',
        'numeric' => 'يجب أن يكون الحقل :attribute أصغر من :value.',
        'string' => 'يجب أن يكون طول الحقل :attribute أقل من :value أحرف.',
    ],
    'lte' => [
        'array' => 'يجب ألا يحتوي الحقل :attribute على أكثر من :value عناصر.',
        'file' => 'يجب أن يكون الحقل :attribute أصغر من أو يساوي :value كيلوبايت.',
        'numeric' => 'يجب أن يكون الحقل :attribute أصغر من أو يساوي :value.',
        'string' => 'يجب أن يكون طول الحقل :attribute أصغر من أو يساوي :value أحرف.',
    ],
    'mac_address' => 'يجب أن يكون الحقل :attribute عنوان MAC صالحًا.',
    'max' => [
        'array' => 'يجب ألا يحتوي الحقل :attribute على أكثر من :max عناصر.',
        'file' => 'يجب ألا يتجاوز الحقل :attribute :max كيلوبايت.',
        'numeric' => 'يجب ألا يتجاوز الحقل :attribute :max.',
        'string' => 'يجب ألا يتجاوز طول الحقل :attribute :max أحرف.',
    ],
    'max_digits' => 'يجب ألا يحتوي الحقل :attribute على أكثر من :max أرقام.',
    'mimes' => 'يجب أن يكون الحقل :attribute ملفًا من نوع: :values.',
    'mimetypes' => 'يجب أن يكون الحقل :attribute ملفًا من نوع: :values.',
    'min' => [
        'array' => 'يجب أن يحتوي الحقل :attribute على الأقل على :min عناصر.',
        'file' => 'يجب أن يكون الحقل :attribute على الأقل :min كيلوبايت.',
        'numeric' => 'يجب أن يكون الحقل :attribute على الأقل :min.',
        'string' => 'يجب أن يكون طول الحقل :attribute على الأقل :min أحرف.',
    ],
    'min_digits' => 'يجب أن يحتوي الحقل :attribute على الأقل على :min أرقام.',
    'missing' => 'يجب أن يكون الحقل :attribute مفقوداً.',
    'missing_if' => 'يجب أن يكون الحقل :attribute مفقوداً عندما يكون :other هو :value.',
    'missing_unless' => 'يجب أن يكون الحقل :attribute مفقوداً إلا إذا كان :other هو :value.',
    'missing_with' => 'يجب أن يكون الحقل :attribute مفقوداً عندما يكون :values موجوداً.',
    'missing_with_all' => 'يجب أن يكون الحقل :attribute مفقوداً عندما تكون :values موجودة.',
    'multiple_of' => 'يجب أن يكون الحقل :attribute من مضاعفات :value.',
    'not_in' => 'الحقل :attribute المحدد غير صالح.',
    'not_regex' => 'صيغة الحقل :attribute غير صالحة.',
    'numeric' => 'يجب أن يكون الحقل :attribute رقمًا.',
    'password' => [
        'letters' => 'يجب أن يحتوي الحقل :attribute على حرف واحد على الأقل.',
        'mixed' => 'يجب أن يحتوي الحقل :attribute على حرف واحد كبير على الأقل وحرف واحد صغير على الأقل.',
        'numbers' => 'يجب أن يحتوي الحقل :attribute على رقم واحد على الأقل.',
        'symbols' => 'يجب أن يحتوي الحقل :attribute على رمز واحد على الأقل.',
        'uncompromised' => 'لقد ظهر الحقل :attribute المُعطى في تسريب بيانات. يرجى اختيار :attribute مختلف.',
    ],
    'present' => 'يجب توفير الحقل :attribute.',
    'present_if' => 'يجب أن يكون الحقل :attribute موجوداً عندما يكون :other هو :value.',
    'present_unless' => 'يجب أن يكون الحقل :attribute موجوداً إلا إذا كان :other هو :value.',
    'present_with' => 'يجب أن يكون الحقل :attribute موجوداً عندما يكون :values موجوداً.',
    'present_with_all' => 'يجب أن يكون الحقل :attribute موجوداً عندما تكون :values موجودة.',
    'prohibited' => 'الحقل :attribute محظور.',
    'prohibited_if' => 'الحقل :attribute محظور عندما يكون :other هو :value.',
    'prohibited_if_accepted' => 'الحقل :attribute محظور عندما يتم قبول :other.',
    'prohibited_if_declined' => 'الحقل :attribute محظور عندما يتم رفض :other.',
    'prohibited_unless' => 'الحقل :attribute محظور إلا إذا كان :other في :values.',
    'prohibits' => 'الحقل :attribute يمنع :other من التواجد.',
    'regex' => 'صيغة الحقل :attribute غير صالحة.',
    'required' => 'الحقل :attribute مطلوب.',
    'required_array_keys' => 'يجب أن يحتوي الحقل :attribute على مدخلات لـ: :values.',
    'required_if' => 'الحقل :attribute مطلوب عندما يكون :other هو :value.',
    'required_if_accepted' => 'الحقل :attribute مطلوب عندما يتم قبول :other.',
    'required_if_declined' => 'الحقل :attribute مطلوب عندما يتم رفض :other.',
    'required_unless' => 'الحقل :attribute مطلوب إلا إذا كان :other في :values.',
    'required_with' => 'الحقل :attribute مطلوب عندما يكون :values موجودًا.',
    'required_with_all' => 'الحقل :attribute مطلوب عندما تكون :values موجودة.',
    'required_without' => 'الحقل :attribute مطلوب عندما لا يكون :values موجودًا.',
    'required_without_all' => 'الحقل :attribute مطلوب عندما لا تكون أي من :values موجودة.',
    'same' => 'يجب أن يتطابق الحقل :attribute مع :other.',
    'size' => [
        'array' => 'يجب أن يحتوي الحقل :attribute على :size عناصر.',
        'file' => 'يجب أن يكون الحقل :attribute بحجم :size كيلوبايت.',
        'numeric' => 'يجب أن يكون الحقل :attribute مساويًا لـ :size.',
        'string' => 'يجب أن يحتوي الحقل :attribute على :size أحرف.',
    ],
    'starts_with' => 'يجب أن يبدأ الحقل :attribute بأحد القيم التالية: :values.',
    'string' => 'يجب أن يكون الحقل :attribute نصًا.',
    'timezone' => 'يجب أن يكون الحقل :attribute منطقة زمنية صالحة.',
    'unique' => 'قيمة الحقل :attribute مُستخدمة من قبل.',
    'uploaded' => 'فشل في تحميل الحقل :attribute.',
    'uppercase' => 'يجب أن يكون الحقل :attribute بأحرف كبيرة.',
    'url' => 'يجب أن يكون الحقل :attribute رابطًا صحيحًا.',
    'ulid' => 'يجب أن يكون الحقل :attribute ULID صالحًا.',
    'uuid' => 'يجب أن يكون الحقل :attribute UUID صالحًا.',
    'phone_number' => 'رقم الهاتف غير صحيح.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'رسالة-مخصصة',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'name' => 'الاسم',
        'phone' => 'رقم الهاتف',
        'email' => 'البريد الإلكتروني',
        'date_of_birth' => 'تاريخ الميلاد',
        'gender' => 'الجنس',
        'password' => 'كلمة المرور',
        'image' => 'الصورة',
        'country_code' => 'رمز الدولة',
        'terms_and_conditions' => 'الشروط والأحكام',
    ],

    'country_codes' => [
        'US' => 'الولايات المتحدة',
        'SA' => 'المملكة العربية السعودية',
        'AE' => 'الإمارات العربية المتحدة',
        'EG' => 'مصر',
        'KW' => 'الكويت',
        'BH' => 'البحرين',
        'QA' => 'قطر',
        'OM' => 'عمان',
        'JO' => 'الأردن',
        'LB' => 'لبنان',
        'MA' => 'المغرب',
        'TN' => 'تونس',
        'DZ' => 'الجزائر',
        'SD' => 'السودان',
        'IQ' => 'العراق',
        'SY' => 'سوريا',
        'YE' => 'اليمن',
        'PS' => 'فلسطين',
    ],

];
