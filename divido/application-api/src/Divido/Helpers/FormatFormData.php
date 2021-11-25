<?php

namespace Divido\Helpers;

/**
 * Class FormatFormData
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class FormatFormData
{
    /** @var array $fields */
    var $fields;

    /**
     * FormatFormData constructor.
     */
    public function __construct()
    {
        $this->fields['basic'] = [
            'progress' => [
                'type' => 'integer',
            ],
            'step' => [
                'type' => 'text',
            ],
            'page' => [
                'name' => 'page',
                'type' => 'text',
            ],
            'title' => [
                'type' => 'text',
            ],
            'firstName' => [
                'type' => 'text',
                "vault" => [
                    "name" => "first_name",
                    "level" => 1,
                    "content_type" => "string",
                    "expires" => "5 years",
                ],
            ],
            'middleNames' => [
                'type' => 'text',
                "vault" => [
                    "name" => "middle_name",
                    "level" => 1,
                    "content_type" => "string",
                    "expires" => "5 years",
                ],
            ],
            'lastName' => [
                'type' => 'text',
                "vault" => [
                    "name" => "last_name",
                    "level" => 1,
                    "content_type" => "string",
                    "expires" => "5 years",
                ],
            ],
            'gender' => [
                'type' => 'select',
                'options' => [
                    'male' => 'Male',
                    'female' => 'Female',
                ],
            ],
            'phoneNumber' => [
                'type' => 'text',
            ],
            'secondaryPhoneNumber' => [
                'type' => 'text',
            ],
            'email' => [
                'type' => 'text',
            ],
            'numberOfDependants' => [
                'type' => "integer",
            ],
            'householdIncome' => [
                'type' => "integer",
            ],
            'grossIncome' => [
                'type' => "integer",
            ],
            'homeValue' => [
                'type' => "integer",
            ],
            'rent' => [
                'type' => "integer",
            ],
            'monthlyCreditCommitments' => [
                'type' => "integer",
            ],
            'monthlyMortgage' => [
                'type' => "integer",
            ],
            'balanceMortgage' => [
                'type' => "integer",
            ],
            'birthName' => [
                'type' => "string",
            ],
            'placeOfBirth' => [
                'type' => "string",
            ],
            'dateOfBirthYear' => [
                'type' => "string",
            ],
            'dateOfBirthMonth' => [
                'type' => "string",
            ],
            'dateOfBirthDay' => [
                'type' => "string",
            ],
            'citizenship' => [
                'type' => 'select',
                'options' => [
                    'AX' => 'Ålandic',
                    'AF' => 'Afghani',
                    'AL' => 'Albanian',
                    'DZ' => 'Algerian',
                    'AS' => 'Samoan',
                    'AD' => 'Andorran',
                    'AO' => 'Angolan',
                    'AI' => 'Anguillan',
                    'AQ' => 'Antarctic',
                    'AG' => 'Antiguan',
                    'AR' => 'Argentine',
                    'AM' => 'Armenian',
                    'AW' => 'Arubian',
                    'AU' => 'Australian',
                    'AT' => 'Austrian',
                    'AZ' => 'Azerbaijani',
                    'BS' => 'Bahameese',
                    'BH' => 'Bahrainian',
                    'BD' => 'Bangladeshi',
                    'BB' => 'Barbadian',
                    'BY' => 'Belarusian',
                    'BE' => 'Belgian',
                    'BZ' => 'Belizean',
                    'BJ' => 'Beninese',
                    'BM' => 'Bermudan',
                    'BT' => 'Bhutanese',
                    'BO' => 'Bolivian',
                    'BA' => 'Bosnian',
                    'BW' => 'Motswana',
                    'BV' => 'Bouvet Island',
                    'BR' => 'Brazilian',
                    'IO' => 'BIOT',
                    'VG' => 'Virgin Islander',
                    'BN' => 'Bruneian',
                    'BG' => 'Bulgarian',
                    'BF' => 'Burkinabe',
                    'MM' => 'Myanmarese',
                    'BI' => 'Burundian',
                    'KH' => 'Cambodian',
                    'CM' => 'Cameroonian',
                    'CA' => 'Canadian',
                    'CV' => 'Cape Verdean',
                    'BQ' => 'Caribbean Netherlands',
                    'KY' => 'Caymanian',
                    'CF' => 'Central African',
                    'TD' => 'Chadian',
                    'CL' => 'Chilean',
                    'CN' => 'Chinese',
                    'CX' => 'Christmas Islander',
                    'CC' => 'Cocossian',
                    'CO' => 'Colombian',
                    'KM' => 'Comoran',
                    'CG' => 'Congolese',
                    'CK' => 'Cook Islander',
                    'CR' => 'Costa Rican',
                    'HR' => 'Croatian',
                    'CU' => 'Cuban',
                    'CW' => 'Curaçaoan',
                    'CI' => 'Ivorian',
                    'CY' => 'Cypriot',
                    'CZ' => 'Czech',
                    'CD' => 'Congolese',
                    'DK' => 'Danish',
                    'DJ' => 'Djiboutian',
                    'DM' => 'Dominican',
                    'DO' => 'Dominican',
                    'EC' => 'Ecuadorean',
                    'EG' => 'Egyptian',
                    'SV' => 'Salvadorean',
                    'GQ' => 'Equatorial Guinean',
                    'ER' => 'Eritrean',
                    'EE' => 'Estonian',
                    'ET' => 'Ethiopian',
                    'FK' => 'Falkland Islander',
                    'FO' => 'Faroese',
                    'FJ' => 'Fijian',
                    'FI' => 'Finnish',
                    'FR' => 'French',
                    'GF' => 'French Guianese',
                    'PF' => 'French Polynesian',
                    'TF' => 'French',
                    'GA' => 'Gabonese',
                    'GM' => 'Gambian',
                    'GE' => 'Georgian',
                    'DE' => 'German',
                    'GH' => 'Ghanaian',
                    'GI' => 'Gibralterian',
                    'GR' => 'Greek',
                    'GL' => 'Greenlander',
                    'GD' => 'Grenadian',
                    'GP' => 'Guadeloupean',
                    'GU' => 'Guamanian',
                    'GT' => 'Guatemalan',
                    'GG' => 'Channel Islander',
                    'GN' => 'Guinean',
                    'GW' => 'Guinean',
                    'GY' => 'Guyanese',
                    'HT' => 'Haitian',
                    'HM' => 'Heard and McDonald Islands',
                    'HN' => 'Honduran',
                    'HK' => 'Hong Konger',
                    'HU' => 'Hungarian',
                    'IS' => 'Icelander',
                    'IN' => 'Indian',
                    'ID' => 'Indonesian',
                    'IR' => 'Iranian',
                    'IQ' => 'Iraqi',
                    'IE' => 'Irish',
                    'IM' => 'Manx',
                    'IL' => 'Israeli',
                    'IT' => 'Italian',
                    'JM' => 'Jamaican',
                    'JP' => 'Japanese',
                    'JE' => 'British',
                    'JO' => 'Jordanian',
                    'KZ' => 'Kazakhstani',
                    'KE' => 'Kenyan',
                    'KI' => 'I-Kiribati',
                    'KW' => 'Kuwaiti',
                    'KG' => 'Kyrgyzstani',
                    'LA' => 'Laotian',
                    'LV' => 'Latvian',
                    'LB' => 'Lebanese',
                    'LS' => 'Mosotho',
                    'LR' => 'Liberian',
                    'LY' => 'Libyan',
                    'LI' => 'Liechtensteiner',
                    'LT' => 'Lithunian',
                    'LU' => 'Luxembourger',
                    'MO' => 'Macanese',
                    'MK' => 'Macedonian',
                    'MG' => 'Malagasy',
                    'MW' => 'Malawian',
                    'MY' => 'Malaysian',
                    'MV' => 'Maldivan',
                    'ML' => 'Malian',
                    'MT' => 'Maltese',
                    'MH' => 'Marshallese',
                    'MQ' => 'Martinican',
                    'MR' => 'Mauritanian',
                    'MU' => 'Mauritian',
                    'YT' => 'Mahoran',
                    'MX' => 'Mexican',
                    'FM' => 'Micronesian',
                    'MD' => 'Moldovan',
                    'MC' => 'Monacan',
                    'MN' => 'Mongolian',
                    'ME' => 'Montenegrin',
                    'MS' => 'Montserratian',
                    'MA' => 'Moroccan',
                    'MZ' => 'Mozambican',
                    'NA' => 'Namibian',
                    'NR' => 'Nauruan',
                    'NP' => 'Nepalese',
                    'NL' => 'Dutch',
                    'NC' => 'New Caledonian',
                    'NZ' => 'New Zealander',
                    'NI' => 'Nicaraguan',
                    'NE' => 'Nigerien',
                    'NG' => 'Nigerian',
                    'NU' => 'Niuean',
                    'NF' => 'Norfolk Islander',
                    'KP' => 'North Korean',
                    'MP' => 'Northern Mariana Islander',
                    'NO' => 'Norwegian',
                    'OM' => 'Omani',
                    'PK' => 'Pakistani',
                    'PW' => 'Palauan',
                    'PS' => 'Palestinian',
                    'PA' => 'Panamanian',
                    'PG' => 'Papua New Guinean',
                    'PY' => 'Paraguayan',
                    'PE' => 'Peruvian',
                    'PH' => 'Filipino',
                    'PN' => 'Pitcairn Islander',
                    'PL' => 'Polish',
                    'PT' => 'Portuguese',
                    'PR' => 'Puerto Rican',
                    'QA' => 'Qatari',
                    'RE' => 'Reunionese',
                    'RO' => 'Romanian',
                    'RU' => 'Russian',
                    'RW' => 'Rwandan',
                    'ST' => 'S„o Tomean',
                    'BL' => 'BarthÈlemois',
                    'SH' => 'Saint Helenian',
                    'KN' => 'Kittian',
                    'LC' => 'Saint Lucian',
                    'MF' => 'St. Martiner',
                    'SX' => 'St. Maartener',
                    'VC' => 'Saint Vincentian',
                    'WS' => 'Samoan',
                    'SM' => 'Sanmarinese',
                    'SA' => 'Saudi Arabian',
                    'SN' => 'Senegalese',
                    'RS' => 'Serbian',
                    'SC' => 'Seychellois',
                    'SL' => 'Sierra Leonean',
                    'SG' => 'Singaporean',
                    'SK' => 'Slovakian',
                    'SI' => 'Slovenian',
                    'SB' => 'Solomon Islander',
                    'SO' => 'Somali',
                    'ZA' => 'South African',
                    'GS' => 'South Georgian South Sandwich Islander',
                    'KR' => 'South Korean',
                    'SS' => 'Sudanese',
                    'ES' => 'Spanish',
                    'LK' => 'Sri Lankan',
                    'PM' => 'Saint-Pierrais',
                    'SD' => 'Sudanese',
                    'SR' => 'Surinamer',
                    'SJ' => 'Svalbard and Jan Mayen',
                    'SZ' => 'Swazi',
                    'SE' => 'Swedish',
                    'CH' => 'Swiss',
                    'SY' => 'Syrian',
                    'TW' => 'Taiwanese',
                    'TJ' => 'Tajikistani',
                    'TZ' => 'Tanzanian',
                    'TH' => 'Thai',
                    'TL' => 'Timorese',
                    'TG' => 'Togolese',
                    'TK' => 'Tokelauan',
                    'TO' => 'Tongan',
                    'TT' => 'Trinidadian',
                    'TN' => 'Tunisian',
                    'TR' => 'Turkish',
                    'TM' => 'Turkmen',
                    'TC' => 'Turks and Caicos Islander',
                    'TV' => 'Tuvaluan',
                    'UG' => 'Ugandan',
                    'UA' => 'Ukrainian',
                    'AE' => 'Emirian',
                    'GB' => 'British',
                    'UM' => 'United States Minor Outlying Islands',
                    'US' => 'American',
                    'VI' => 'Virgin Islander',
                    'UY' => 'Uruguayan',
                    'UZ' => 'Uzbekistani',
                    'VU' => 'Ni-Vanuatu',
                    'VA' => 'Vatican citizen',
                    'VE' => 'Venezuelan',
                    'VN' => 'Vietnamese',
                    'WF' => 'Wallisian',
                    'EH' => 'Western Saharan',
                    'YE' => 'Yemeni',
                    'ZM' => 'Zambian',
                    'ZW' => 'Zimbabwean',
                ],
            ],
            'occupancyStatus' => [
                'type' => "select",
                'options' => [
                    'home_owner' => 'Home owner',
                    'home_owner_with_mortgage' => 'Home owner with mortgage',
                    'living_with_parents' => 'Living with parents',
                    'council' => 'Council',
                    'tenant' => 'Tenant',
                    'freehold_apartment' => 'Freehold Apartment',
                    'no_permanent_residence' => 'No permanent residence',
                    'joint_owner' => 'Joint Owner',
                    'housing_cooperative' => 'Housing cooperatives',
                    'living_with_partner' => 'Living with partner',
                    'employee_housing' => 'Employee housing',
                    'right_of_occupancy' => 'Right of occupancy',
                    'other' => 'Other',
                ],
            ],
            'occupancySince' => [
                'type' => "text",
            ],
            'maritalStatus' => [
                'type' => "select",
                'options' => [
                    'married' => 'Married',
                    'single' => 'Single',
                    'divorced' => 'Divorced',
                    'widowed' => 'Widowed',
                    'cohabiting' => 'Cohabiting',
                    'separated' => 'Separated',
                    'other' => 'Other',
                ],
            ],
            'employmentBranch' => [
                'type' => 'text',
            ],
            'employmentStatus' => [
                'type' => "select",
                'options' => [
                    'full_time' => 'Full time',
                    'full_time_public' => 'Full time (Public sector)',
                    'director' => 'Director',
                    'part_time' => 'Part time',
                    'self_employed' => 'Self employed',
                    'office_bearer' => 'Office bearer',
                    'student' => 'Student',
                    'house_person' => 'House person',
                    'retired' => 'Retired',
                    'unemployed' => 'Unemployed',
                    'temporary_employment' => 'Temporary employment',
                    'benefits' => 'Benefits',
                    'armed_forces' => 'Armed Forces',
                    'farmer' => 'Farmer',
                ],
            ],
            'employmentSince' => [
                'type' => "text",
            ],
            'education' => [
                'type' => "select",
                'options' => [
                    'primary_school' => 'Primary School',
                    'high_school' => 'High School',
                    'college' => 'College',
                    'university' => 'University',
                ],
            ],
            'roles' => [
                'type' => 'text',
            ],
            'sourcesOfIncome' => [
                'type' => "select",
                'options' => [
                    'benefits' => 'Benefits',
                    'partner' => 'Partner',
                    'maintenance' => 'Maintenance',
                    'other' => 'Other',
                ],
            ],
            'changeCircumstances' => [
                'type' => 'boolean',
            ],
            'changeCircumstancesDetails' => [
                'type' => 'text',
            ],
            'declinedCreditInPast' => [
                'type' => 'boolean',
            ],
            'identityNumber' => [
                'type' => 'text',
            ],
            'token' => [
                'type' => 'text',
            ],
            'ipAddress' => [
                'type' => 'text',
            ],
            'insurance' => [
                'type' => 'boolean',
            ],
            'bundledCreditCard' => [
                'type' => 'boolean',
            ],
            'bundledCreditCardInsurance' => [
                'type' => 'boolean',
            ],
            'acceptPromotionsAgreement' => [
                'type' => 'boolean',
            ],
            'fragmented' => [
                'type' => 'boolean',
            ],
            'form_data_version' => [
                'type' => 1,
            ],
            'vault_token' => [
                'type' => 'text',
            ],
        ];

        $this->fields['address'] = [
            'postcode' => [
                'type' => 'text',
            ],
            'flat' => [
                'type' => 'text',
            ],
            'buildingName' => [
                'type' => 'text',
            ],
            'co' => [
                'type' => 'text',
            ],
            'buildingNumber' => [
                'type' => 'text',
            ],
            'street' => [
                'type' => 'text',
            ],
            'town' => [
                'type' => 'text',
            ],
            'district' => [
                'type' => 'text',
            ],
            'monthsAtAddress' => [
                'type' => 'integer',
            ],
            'country' => [
                'type' => 'text',
            ],
        ];

        $this->fields['shippingAddress'] = $this->fields['address'];
        unset($this->fields['shippingAddress']['monthsAtAddress']);

        $this->fields['employment'] = [
            'employerName' => [
                'type' => 'text',
            ],
            'jobTitle' => [
                'type' => 'text',
            ],
            'phoneNumber' => [
                'type' => 'text',
            ],
            'dateLeft' => [
                'type' => 'date',
            ],
            'monthsInEmployment' => [
                'type' => 'integer',
            ],
        ];

        $this->fields['bank'] = [
            'type' => [
                'type' => "select",
                'options' => [
                    'accountNumber' => 'Account number',
                    'iban' => 'iban',
                ],
            ],
            'sortCode' => [
                'type' => 'text',
            ],
            'accountNumber' => [
                'type' => 'text',
            ],
            'iban' => [
                'type' => 'text',
            ],
            'since' => [
                'type' => 'text',
            ],
        ];

        $this->fields['marketing'] = [
            'contactPost' => [
                'type' => 'boolean',
            ],
            'contactEmail' => [
                'type' => 'boolean',
            ],
            'contactSms' => [
                'type' => 'boolean',
            ],
            'contactPhone' => [
                'type' => 'boolean',
            ],
            'personalDetails' => [
                'type' => 'boolean',
            ],
        ];

        $this->fields['income'] = [
            'additionalIncome' => [
                'type' => "integer",
            ],
            'netIncome' => [
                'type' => "integer",
            ],
        ];

        $this->fields['spouse'] = [
            'grossIncome' => [
                'type' => "integer",
            ],
            'netIncome' => [
                'type' => "integer",
            ],
        ];

        $this->fields['debt'] = [
            'mortgage' => [
                'type' => "integer",
            ],
            'securedLoan' => [
                'type' => "integer",
            ],
            'studentLoan' => [
                'type' => "integer",
            ],
            'unsecuredLoan' => [
                'type' => "integer",
            ],
            'mortgagePercentage' => [
                'type' => "integer",
            ]
        ];

        $this->fields['business'] = [
            'type' => [
                'type' => "select",
                'options' => [
                    'limitedCompany' => 'Limited company',
                    'soleTrader' => 'Sole trader',
                ],
            ],
            'providerBank' => [
                'type' => "select",
                'options' => [
                    'barclays' => 'Barclays',
                    'hsbc' => 'HSBC',
                    'lloyds' => 'Lloyds bank',
                    'natwest' => 'NatWest',
                    'santander' => 'Santander',
                    'other' => 'Other',
                ],
            ],
            'providerSoftware' => [
                'type' => "select",
                'options' => [
                    'no' => 'No',
                    'xero' => 'Xero',
                    'kashflow' => 'KashFlow',
                    'sageone' => 'Sage One',
                    'sage50' => 'Sage 50',
                    'other' => 'Other',
                ],
            ],
        ];

        $this->fields['businessCompany'] = [
            'annualBusinessProfit' => [
                'type' => 'integer',
            ],
            'annualTurnover' => [
                'type' => 'integer',
            ],
            'name' => [
                'type' => 'text',
            ],
            'number' => [
                'type' => 'text',
            ],
            'industry' => [
                'type' => 'text',
            ],
            'tradingAs' => [
                'type' => 'text',
            ],
            'registered' => [
                'type' => 'text',
            ],
            'onlineSalesPercentage' => [
                'type' => 'integer',
            ],
            'vatRegisteredStatus' => [
                'type' => 'boolean',
            ],
            'website' => [
                'type' => 'text',
            ],
        ];

    }

    /**
     * @param array $data
     * @param string $section
     * @return array
     */
    private function formatSection($data = [], $section = 'basic')
    {
        $record = [];

        if (isset($this->fields[$section])) {
            foreach ($this->fields[$section] as $name => $field) {
                $type = (isset($field['type'])) ? $field['type'] : 'text';
                $value = (array_key_exists($name, $data)) ? $data[$name] : null;

                if ($type == 'integer') {
                    $value = (!is_null($value) && $value !== '') ? intval(preg_replace("/,/", "", $value)) : null;
                } else if ($type == 'float') {
                    $value = (!is_null($value)) ? floatval(preg_replace("/,/", "", $value)) : null;
                } else if ($type == 'boolean') {
                    if (!is_null($value)) {
                        $value = (boolean) ($value !== false && $value !== '' && $value !== 'false') ? true : false;
                    }
                } else if ($type == 'select') {
                    $value = (isset($data[$name]) && $data[$name] !== null) ? trim($data[$name]) : null;
                    $value = (isset($field['options'][$value])) ? $value : "";
                } else {
                    $value = (isset($data[$name]) && $data[$name] !== null) ? trim($data[$name]) : null;
                }

                $record[$name] = $value;
            }
        }

        return $record;
    }

    /**
     * @param $data
     * @return array
     */
    public function formatData($data)
    {
        $data = json_decode(json_encode($data), 1);
        $record = $this->formatSection($data);

        $record['employments'] = [];
        $record['addresses'] = [];
        $record['shippingAddress'] = $this->formatSection((isset($data['shippingAddress']) && is_array($data['shippingAddress'])) ? $data['shippingAddress'] : [], 'shippingAddress');
        $record['bank'] = $this->formatSection((isset($data['bank']) && is_array($data['bank'])) ? $data['bank'] : [], 'bank');
        $record['marketing'] = $this->formatSection((isset($data['marketing']) && is_array($data['marketing'])) ? $data['marketing'] : [], 'marketing');
        $record['income'] = $this->formatSection((isset($data['income']) && is_array($data['income'])) ? $data['income'] : [], 'income');
        $record['debt'] = $this->formatSection((isset($data['debt']) && is_array($data['debt'])) ? $data['debt'] : [], 'debt');
        $record['spouse'] = $this->formatSection((isset($data['spouse']) && is_array($data['spouse'])) ? $data['spouse'] : [], 'spouse');
        $record['business'] = $this->formatSection((isset($data['business']) && is_array($data['business'])) ? $data['business'] : [], 'business');
        $record['business']['company'] = $this->formatSection((isset($data['business']['company']) && is_array($data['business']['company'])) ? $data['business']['company'] : [], 'businessCompany');

        if (isset($data['employments']) && is_array($data['employments'])) {

            foreach ($data['employments'] as $dataEmployment) {
                $employment = $this->formatSection($dataEmployment, 'employment');
                $employment['addresses'] = [];
                if (isset($dataEmployment['addresses']) && is_array($dataEmployment['addresses'])) {
                    foreach ($dataEmployment['addresses'] as $dataEmploymentAddress) {
                        $employmentAddress = $this->formatSection($dataEmploymentAddress, 'address');
                        $employment['addresses'][] = $employmentAddress;
                    }
                }

                $record['employments'][] = $employment;
            }
        }
        if (count($record['employments']) == 0) {
            $employment = $this->formatSection([], 'employment');
            $employment['addresses'] = [$this->formatSection([], 'address')];
            $record['employments'][] = $employment;
        }

        if (isset($data['addresses']) && is_array($data['addresses'])) {
            foreach ($data['addresses'] as $i => $dataAddress) {
                $address = $this->formatSection($dataAddress, 'address');

                if ($i == 0 || (!empty($address['postcode']) || !empty($address['flat']) || !empty($address['buildingName']) || !empty($address['co']) || !empty($address['buildingNumber']) || !empty($address['street']) || !empty($address['town']) || !empty($address['district']))) {
                    $record['addresses'][] = $address;
                }
            }
        }
        if (count($record['addresses']) == 0) {
            $record['addresses'][] = $this->formatSection([], 'address');
        }

        return json_decode(json_encode($record));
    }
}
