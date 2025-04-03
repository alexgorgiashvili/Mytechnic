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
    'payment_type_required' => 'აირჩიეთ გადახდის მეთოდი',
    'address_required' => 'მისამართის ველი აუცილებელია.',
    'address_name_required' => 'სახელი და გვარი აუცილებელია.',
    'address_personal_number_required' => 'პირადი ნომრის ველი აუცილებელია.',
    'address_phone_no_required' => 'ტელეფონის ნომრის ველი აუცილებელია.',
    'address_address_required' => 'მისამართის ველი აუცილებელია.',
    'address_city_id_required' => 'აირჩიეთ ქალაქი',
    'product_ids_required' => 'პროდუქტების ID ველი აუცილებელია.',
    'product_id_exists' => 'არჩეული პროდუქტის ID არ არის ვალიდური.',

    'accepted' => 'სასურველია :attribute.',
    'accepted_if' => 'სასურველია :attribute როცა :other არის :value.',
    'active_url' => ':attribute არასწორი URL-ია.',
    'after' => ':attribute უნდა იყოს :date-ზე შემდეგი თარიღი.',
    'after_or_equal' => ':attribute უნდა იყოს :date ან მისი ტოლი თარიღი.',
    'alpha' => ':attribute უნდა შეიცავდეს მხარისახელების.',
    'alpha_dash' => ':attribute უნდა შეიცავდეს მხარისახელების, ციფრების, მინუსებისა და ქვეხანგრების.',
    'alpha_num' => ':attribute უნდა შეიცავდეს მხარისახელებისა და ციფრების.',
    'array' => ':attribute უნდა იყოს მასივი.',
    'before' => ':attribute უნდა იყოს :date-მდე თარიღი.',
    'before_or_equal' => ':attribute უნდა იყოს :date-მდე ან მისი ტოლი თარიღი.',
    'between' => [
        'numeric' => ':attribute უნდა იყოს :min-სა და :max-სა შორის.',
        'file' => ':attribute უნდა იყოს :min-სა და :max-სა შორის კილობაიტში.',
        'string' => ':attribute უნდა იყოს :min-სა და :max-სა შორის სიმბოლოში.',
        'array' => ':attribute უნდა შეიცავდეს :min-სა და :max-სა შორის ელემენტს.',
    ],
    'boolean' => ':attribute უნდა იყოს ჭეშმარიტი ან მცდარი.',
    'confirmed' => ':attribute-ის დამოწმება არ ემთხვევა.',
    'date' => ':attribute არ არის სწორი თარიღი.',
    'date_equals' => ':attribute უნდა იყოს :date-თი ტოლი თარიღი.',
    'date_format' => 'თარიღის ფორმატი :format-თან არ ემთხვევა :attribute.',
    'different' => ':attribute და :other შეიბანეთ სხვადასხვა მონაცემები.',
    'digits' => ':attribute უნდა იყოს :digits ციფრი.',
    'digits_between' => ':attribute უნდა იყოს :min-დან :max ციფრის შორის.',
    'dimensions' => ':attribute-ის სურათის ზომები არასწორია.',
    'distinct' => ':attribute-ის ველში ესახება დუბლირებული მნიშვნელობა.',
    'email' => ':attribute უნდა იყოს სწორი email მისამართი.',
    'ends_with' => ':attribute უნდა მთავრობდეს შემდეგიდან ერთ-ერთით: :values.',
    'exists' => 'არჩეული :attribute არასწორია.',
    'file' => ':attribute უნდა იყოს ფაილი.',
    'filled' => ':attribute-ის ველი უნდა შეავსოთ.',
    'gt' => [
        'numeric' => ':attribute უნდა იყოს მეტი, ვიდრე :value.',
        'file' => ':attribute უნდა იყოს :value-ზე მეტი კილობაიტი.',
        'string' => ':attribute უნდა იყოს :value-ზე მეტი სიმბოლო.',
        'array' => ':attribute უნდა შეიცავდეს :value-ზე მეტ ელემენტს.',
    ],
    'gte' => [
        'numeric' => ':attribute უნდა იყოს :value-ზე დიდი ან ტოლი.',
        'file' => ':attribute უნდა იყოს :value-ზე დიდი ან ტოლი კილობაიტი.',
        'string' => ':attribute უნდა იყოს :value-ზე დიდი ან ტოლი სიმბოლო.',
        'array' => ':attribute-ში უნდა ჰქონდეს :value ელემენტი ან მეტი.',
    ],
    'image' => ':attribute უნდა იყოს სურათი.',
    'in' => 'შერჩეული :attribute არასწორია.',
    'in_array' => ':attribute ველი არ არსებობს :other-ში.',
    'integer' => ':attribute უნდა იყოს მთელი რიცხვი.',
    'ip' => ':attribute უნდა იყოს სწორი IP მისამართი.',
    'ipv4' => ':attribute უნდა იყოს სწორი IPv4 მისამართი.',
    'ipv6' => ':attribute უნდა იყოს სწორი IPv6 მისამართი.',
    'json' => ':attribute უნდა იყოს სწორი JSON სტრიქონი.',
    'lt' => [
        'numeric' => ':attribute უნდა იყოს :value-ზე ნაკლები.',
        'file' => ':attribute უნდა იყოს :value-ზე ნაკლები კილობაიტი.',
        'string' => ':attribute უნდა იყოს :value-ზე ნაკლები სიმბოლო.',
        'array' => ':attribute-ში უნდა ჰქონდეს :value-ზე ნაკლები ელემენტი.',
    ],
    'lte' => [
        'numeric' => 'The :attribute უნდა იყოს :value-ზე ნაკლები ან ტოლი.',
        'file' => 'The :attribute უნდა იყოს :value-ზე ნაკლები ან ტოლი კილობაიტი.',
        'string' => 'The :attribute უნდა იყოს :value-ზე ნაკლები ან ტოლი სიმბოლო.',
        'array' => 'The :attribute არ უნდა ჰქონდეს :value-ზე მეტი ელემენტი.',
    ],
    'max' => [
        'numeric' => 'The :attribute არ უნდა იყოს :max-ზე მეტი.',
        'file' => 'The :attribute არ უნდა იყოს :max-ზე მეტი კილობაიტი.',
        'string' => 'The :attribute არ უნდა იყოს :max-ზე მეტი სიმბოლო.',
        'array' => 'The :attribute არ უნდა ჰქონდეს :max-ზე მეტი ელემენტი.',
    ],
    'mimes' => 'The :attribute უნდა იყოს ფაილი ტიპი: :values.',
    'mimetypes' => 'The :attribute უნდა იყოს ფაილი ტიპი: :values.',
    'min' => [
        'numeric' => 'The :attribute უნდა იყოს მინიმუმ :min.',
        'file' => 'The :attribute უნდა იყოს მინიმუმ :min კილობაიტი.',
        'string' => 'The :attribute უნდა იყოს მინიმუმ :min სიმბოლო.',
        'array' => 'The :attribute უნდა ჰქონდეს მინიმუმ :min ელემენტი.',
    ],
    
    'multiple_of' => 'The :attribute უნდა იყოს :value-ს ჯერადი.',
    'not_in' => 'The selected :attribute არასწორია.',
    'not_regex' => 'The :attribute ფორმატი არასწორია.',
    'numeric' => 'The :attribute უნდა იყოს რიცხვი.',
    'password' => 'პაროლი არასწორია.',
    'present' => 'The :attribute ველი უნდა იყოს არსები.',
    'regex' => 'The :attribute ფორმატი არასწორია.',
    'required' => 'ეს ველი აუცილებელია.',
    // 'required' => ':attribute ველი აუცილებელია.',
    'required_if' => 'The :attribute ველი აუცილებელია, როდესაც :other-ი არის :value.',
    'required_unless' => 'The :attribute ველი აუცილებელია, თუკი :other არ არის :values-ში.',
    'required_with' => 'The :attribute ველი აუცილებელია, როდესაც :values არის არსები.',
    'required_with_all' => 'The :attribute ველი აუცილებელია, როდესაც :values არიან ყველაზე.',
    'required_without' => 'The :attribute ველი აუცილებელია, როდესაც :values არ არის არსები.',
    'required_without_all' => 'The :attribute ველი აუცილებელია, როდესაც :values არ არიან არსები.',
    'prohibited' => 'The :attribute ველი არ შეიძლება.',
    'prohibited_if' => 'The :attribute ველი არ შეიძლება, როდესაც :other არის :value.',
    'prohibited_unless' => 'The :attribute ველი არ შეიძლება, თუკი :other არ არის :values-ში.',
    'prohibits' => 'The :attribute ველი უარყოფითია, როცა :other არსებობს.',
    'same' => 'The :attribute და :other უნდა ემთხვევა.',
    'size' => [
        'numeric' => 'The :attribute უნდა იყოს :size.',
        'file' => 'The :attribute უნდა იყოს :size კილობაიტი.',
        'string' => 'The :attribute უნდა იყოს :size სიმბოლო.',
        'array' => 'The :attribute უნდა შეიცავდეს :size ელემენტს.',
    ],
    
    'starts_with' => 'The :attribute უნდა იწყებოდეს შემდეგ მნიშვნელობებიდან ერთით: :values.',
    'string' => 'The :attribute უნდა იყოს სტრინგი.',
    'timezone' => 'The :attribute უნდა იყოს მართვის სარეჟიმი.',
    'unique' => 'The :attribute უკვე არსებობს.',
    'uploaded' => 'The :attribute ატვირთვა ჩაიშალა.',
    'url' => 'The :attribute უნდა იყოს სწორი URL.',
    'uuid' => 'The :attribute უნდა იყოს სწორი UUID.',
    
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
            'rule-name' => 'custom-message',
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

    'attributes' => [],

];
