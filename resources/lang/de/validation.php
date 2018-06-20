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

    'accepted'             => 'The :attribute must be accepted.',
    'active_url'           => 'The :attribute is not a valid URL.',
    'after'                => 'The :attribute must be a date after :date.',
    'after_or_equal'       => 'The :attribute must be a date after or equal to :date.',
    'alpha'                => 'The :attribute may only contain letters.',
    'alpha_dash'           => 'The :attribute may only contain letters, numbers, and dashes.',
    'alpha_num'            => 'The :attribute may only contain letters and numbers.',
    'array'                => 'The :attribute must be an array.',
    'before'               => 'The :attribute must be a date before :date.',
    'before_or_equal'      => 'The :attribute must be a date before or equal to :date.',
    'between'              => [
        'numeric' => 'The :attribute must be between :min and :max.',
        'file'    => 'The :attribute must be between :min and :max kilobytes.',
        'string'  => 'The :attribute must be between :min and :max characters.',
        'array'   => 'The :attribute must have between :min and :max items.',
    ],
    'boolean'              => 'The :attribute field must be true or false.',
    'confirmed'            => 'The :attribute confirmation does not match.',
    'date'                 => 'The :attribute is not a valid date.',
    'date_format'          => 'The :attribute does not match the format :format.',
    'different'            => 'The :attribute and :other must be different.',
    'digits'               => 'The :attribute must be :digits digits.',
    'digits_between'       => 'The :attribute must be between :min and :max digits.',
    'dimensions'           => 'The :attribute has invalid image dimensions.',
    'distinct'             => 'The :attribute field has a duplicate value.',
    'email'                => 'The :attribute must be a valid email address.',
    'exists'               => 'The selected :attribute is invalid.',
    'file'                 => 'The :attribute must be a file.',
    'filled'               => 'The :attribute field must have a value.',
    'image'                => 'The :attribute must be an image.',
    'in'                   => 'The selected :attribute is invalid.',
    'in_array'             => 'The :attribute field does not exist in :other.',
    'integer'              => 'The :attribute must be an integer.',
    'ip'                   => 'The :attribute must be a valid IP address.',
    'ipv4'                 => 'The :attribute must be a valid IPv4 address.',
    'ipv6'                 => 'The :attribute must be a valid IPv6 address.',
    'json'                 => 'The :attribute must be a valid JSON string.',
    'max'                  => [
        'numeric' => 'The :attribute may not be greater than :max.',
        'file'    => 'The :attribute may not be greater than :max kilobytes.',
        'string'  => 'The :attribute may not be greater than :max characters.',
        'array'   => 'The :attribute may not have more than :max items.',
    ],
    'mimes'                => 'The :attribute must be a file of type: :values.',
    'mimetypes'            => 'The :attribute must be a file of type: :values.',
    'min'                  => [
        'numeric' => 'The :attribute must be at least :min.',
        'file'    => 'The :attribute must be at least :min kilobytes.',
        'string'  => 'The :attribute must be at least :min characters.',
        'array'   => 'The :attribute must have at least :min items.',
    ],
    'not_in'               => 'The selected :attribute is invalid.',
    'numeric'              => 'The :attribute must be a number.',
    'present'              => 'The :attribute field must be present.',
    'regex'                => 'The :attribute format is invalid.',
    'required'             => 'The :attribute field is required.',
    'required_if'          => 'The :attribute field is required when :other is :value.',
    'required_unless'      => 'The :attribute field is required unless :other is in :values.',
    'required_with'        => 'The :attribute field is required when :values is present.',
    'required_with_all'    => 'The :attribute field is required when :values is present.',
    'required_without'     => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same'                 => 'The :attribute and :other must match.',
    'size'                 => [
        'numeric' => 'The :attribute must be :size.',
        'file'    => 'The :attribute must be :size kilobytes.',
        'string'  => 'The :attribute must be :size characters.',
        'array'   => 'The :attribute must contain :size items.',
    ],
    'string'               => 'The :attribute must be a string.',
    'timezone'             => 'The :attribute must be a valid zone.',
    'unique'               => 'The :attribute has already been taken.',
    'uploaded'             => 'The :attribute failed to upload.',
    'url'                  => 'The :attribute format is invalid.',

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
        'guest.*.sleeps' => [
            'required' => 'Ein Schlafplatz erforderlich.',
            'not_in' => 'Das ausgewählte Schlafplätzen ist nicht verfügbar.',
        ],
        'guest.*.beds' => [
            'required_without' => 'Betten auswählen, falls kein Lager ausgewählt ist.'
        ],
        'guest.*.dormitory' => [
            'required_without' => 'Lager auswählen, falls kein Bett ausgewählt ist.'
        ],
        'guest.*.comments' => [
            'max'   => 'Das Kommentar darf nicht länger als :max Zeichen sein.'
        ],
        /* Custom errors in login & registration page */
        'password' => [
            'required'      => 'Das passwort muss ausgefüllt sein.',
            'string'        => 'Der passwort muss eine Zeichenfolge sein.',
            'min'           => 'Das Passwort muss länger als 6 Zeichen sein',
            'confirmed'     => 'Das passwort bestätigung stimmt nicht überein.',
        ],
        'firstName' => [
            'required'      => 'Das vorname muss ausgefüllt sein.',
            'string'        => 'Der vorname muss eine Zeichenfolge sein.',
            'max'           => 'Die vorname dürfen nicht länger als :max Zeichen sein.'
        ],
        'lastName' => [
            'required'      => 'Das nachname muss ausgefüllt sein.',
            'string'        => 'Der nachname muss eine Zeichenfolge sein.',
            'max'           => 'Die nachname dürfen nicht länger als :max Zeichen sein.'
        ],
        'email' => [
            'required'      => 'Das E-Mail muss ausgefüllt sein.',
            'string'        => 'Der E-Mail muss eine Zeichenfolge sein.',
            'max'           => 'Die E-Mail dürfen nicht länger als :max Zeichen sein.',
            'email'         => 'Die E-Mail muss eine gültige E-Mail-Adresse sein.',
            'unique'        => 'Diese E-Mail wird bereits verwendet',
        ],
        'dataProtection' => [
            'required'      => 'Der datenschutz muss bestätigt werden'
        ],
        'termsService' => [
            'required'      => 'Der AGB muss bestätigt werden'
        ],
        /* Custom errors in cart page */
        'street' => [
            'required'      => 'Die Straße muss eingegeben werden.',
            'string'        => 'Der Straße muss eine Zeichenfolge sein.',
            'max'           => 'Die Straße dürfen nicht länger als :max Zeichen sein.'
        ],
        'city' => [
            'required'      => 'Die Stadt muss eingegeben werden.',
            'string'        => 'Der Stadt muss eine Zeichenfolge sein.',
            'max'           => 'Die Stadt dürfen nicht länger als :max Zeichen sein.'
        ],
        'country' => [
            'required'      => 'Das Land muss eingegeben werden.',
            'not_in'        => 'Das ausgewählte Land ist nicht verfügbar.',
        ],
        'zipcode' => [
            'required'      => 'Die Postleizahl muss eingegeben werden.',
            'string'        => 'Der Postleizahl muss eine Zeichenfolge sein.',
            'max'           => 'Die Postleizahl dürfen nicht länger als :max Zeichen sein.'
        ],
        'mobile' => [
            'max'           => 'Die Handy dürfen nicht länger als :max Zeichen sein.'
        ],
        'phone' => [
            'required'      => 'Die Telefonnummer muss eingegeben werden.',
            'string'        => 'Der Telefonnummer muss eine Zeichenfolge sein.',
            'max'           => 'Die Telefonnummer dürfen nicht länger als :max Zeichen sein.'
        ],
        'payment' => [
            'required'      => 'Das Zahlungs muss ausgefüllt sein.'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [],

];
