<?php

namespace Divido\Helpers;

/**
 * Class MapApplicantsToFormData
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class MapApplicantsToFormData
{
    static public function getFormData($applicants): object
    {
        $applicant = (!empty($applicants->value)) ? current($applicants->value) : (object) [];

        $formData = (object) [
            'progress' => '',
            'step' => '',
            'page' => '',
            'firstName' => '',
            'middleNames' => '',
            'lastName' => '',
            'gender' => '',
            'phoneNumber' => '',
            'secondaryPhoneNumber' => '',
            'email' => '',
            'numberOfDependants' => null,
            'householdIncome' => null,
            'grossIncome' => null,
            'homeValue' => null,
            'rent' => null,
            'monthlyCreditCommitments' => null,
            'monthlyMortgage' => null,
            'balanceMortgage' => null,
            'birthName' => null,
            'placeOfBirth' => null,
            'dateOfBirthYear' => null,
            'dateOfBirthMonth' => null,
            'dateOfBirthDay' => null,
            'citizenship' => '',
            'occupancyStatus' => "",
            'occupancySince' => null,
            'maritalStatus' => null,
            'employmentBranch' => null,
            'employmentStatus' => null,
            'employmentSince' => null,
            'education' => null,
            'roles' => (object) [
                'type' => null
            ],
            'sourcesOfIncome' => null,
            'changeCircumstances' => null,
            'changeCircumstancesDetails' => null,
            'declinedCreditInPast' => null,
            'identityNumber' => "",
            'token' => "",
            'ipAddress' => "",
            'insurance' => null,
            'bundledCreditCard' => null,
            'bundledCreditCardInsurance' => null,
            'acceptPromotionsAgreement' => null,
            'fragmented' => null,
            'form_data_version' => null,
            'vault_token' => null,
            'addresses' => [],
            'shippingAddress' => (object) [],
            'employment' => (object) [
                'employerName' => null,
                'jobTitle' => null,
                'phoneNumber' => null,
                'dateLeft' => null,
                'monthsInEmployment' => null,
            ],
            'bank' => (object) [
                'type' => null,
                'sortCode' => null,
                'accountNumber' => null,
                'iban' => null,
                'since' => null,
            ],
            'marketing' => (object) [
                'contactPost' => null,
                'contactEmail' => null,
                'contactSms' => null,
                'contactPhone' => null,
                'personalDetails' => null,
            ],
            'income' => (object) [
                'additionalIncome' => null,
                'netIncome' => null,
            ],
            'spouse' => (object) [
                'grossIncome' => null,
                'netIncome' => null,
            ],
            'debt' => (object) [
                'mortgage' => null,
                'securedLoan' => null,
                'studentLoan' => null,
                'unsecuredLoan' => null,
                'mortgagePercentage' => null,
            ],
            'business' => (object) [
                'type' => null,
                'providerBank' => null,
                'providerSoftware' => null,
                'company' => (object) [
                    'annualBusinessProfit' => null,
                    'annualTurnover' => null,
                    'name' => null,
                    'number' => null,
                    'industry' => null,
                    'tradingAs' => null,
                    'registered' => null,
                    'onlineSalesPercentage' => null,
                    'vatRegisteredStatus' => null,
                    'website' => null
                ]
            ],
        ];

        if (isset($applicant->value->role->value)) {
            $formData->roles = $applicant->value->role->value;
        }
        if (isset($applicant->value->personal_details->value->first_name->value)) {
            $formData->firstName = $applicant->value->personal_details->value->first_name->value;
        }
        if (isset($applicant->value->personal_details->value->middle_names->value)) {
            $formData->middleNames = $applicant->value->personal_details->value->middle_names->value;
        }
        if (isset($applicant->value->personal_details->value->last_name->value)) {
            $formData->lastName = $applicant->value->personal_details->value->last_name->value;
        }
        if (isset($applicant->value->personal_details->value->gender->value)) {
            $formData->gender = $applicant->value->personal_details->value->gender->value;
        }
        if (isset($applicant->value->contact_details->value->phone_numbers->value[0]->value)) {
            $formData->phoneNumber = $applicant->value->contact_details->value->phone_numbers->value[0]->value;
        }
        if (isset($applicant->value->contact_details->value->phone_numbers->value[1]->value)) {
            $formData->secondaryPhoneNumber = $applicant->value->contact_details->value->phone_numbers->value[1]->value;
        }
        if (isset($applicant->value->contact_details->value->email_addresses->value[0]->value)) {
            $formData->email = $applicant->value->contact_details->value->email_addresses->value[0]->value;
        }
        if (isset($applicant->value->living_arrangements->value->number_of_dependants->value)) {
            $formData->numberOfDependants = $applicant->value->living_arrangements->value->number_of_dependants->value;
        }
        if (isset($applicant->value->financial_details->value->household_income->value)) {
            $formData->householdIncome = $applicant->value->financial_details->value->household_income->value / 100;
        }
        if (isset($applicant->value->financial_details->value->gross_income->value)) {
            $formData->grossIncome = $applicant->value->financial_details->value->gross_income->value / 100;
        }
        if (isset($applicant->value->living_arrangements->value->rent->value)) {
            $formData->rent = $applicant->value->living_arrangements->value->rent->value / 100;
        }
        if (isset($applicant->value->financial_details->value->monthly_credit_commitments->value)) {
            $formData->monthlyCreditCommitments = $applicant->value->financial_details->value->monthly_credit_commitments->value / 100;
        }
        if (isset($applicant->value->living_arrangements->value->monthly_mortgage->value)) {
            $formData->monthlyMortgage = $applicant->value->living_arrangements->value->monthly_mortgage->value / 100;
        }
        if (isset($applicant->value->living_arrangements->value->mortgage_balance->value)) {
            $formData->balanceMortgage = $applicant->value->living_arrangements->value->mortgage_balance->value / 100;
        }
        if (isset($applicant->value->personal_details->value->birth_last_name->value)) {
            $formData->birthName = $applicant->value->personal_details->value->birth_last_name->value;
        }
        if (isset($applicant->value->personal_details->value->place_of_birth->value)) {
            $formData->placeOfBirth = $applicant->value->personal_details->value->place_of_birth->value;
        }
        if (isset($applicant->value->personal_details->value->date_of_birth->value)) {
            list($yearOfBirth, $monthsOfBirth, $dayOfBirth) = preg_split("/-/", $applicant->value->personal_details->value->date_of_birth->value);

            $formData->dateOfBirthYear = $yearOfBirth;
            $formData->dateOfBirthMonth = $monthsOfBirth;
            $formData->dateOfBirthDay = $dayOfBirth;
        }
        if (isset($applicant->value->personal_details->value->citizenship->value)) {
            $formData->citizenship = $applicant->value->personal_details->value->citizenship->value;
        }
        if (isset($applicant->value->living_arrangements->value->occupancy_status->value)) {
            $formData->occupancyStatus = $applicant->value->living_arrangements->value->occupancy_status->value;
        }
        if (isset($applicant->value->living_arrangements->value->occupancy_since->value)) {
            $formData->occupancySince = $applicant->value->living_arrangements->value->occupancy_since->value;
        }
        if (isset($applicant->value->personal_details->value->marital_status->value)) {
            $formData->maritalStatus = $applicant->value->personal_details->value->marital_status->value;
        }
        if (isset($applicant->value->employment_details->value[0]->industry->value)) {
            $formData->employmentBranch = $applicant->value->employment_details->value[0]->industry->value;
        }
        if (isset($applicant->value->employment_details->value[0]->employment_status->value)) {
            $formData->employmentStatus = $applicant->value->employment_details->value[0]->employment_status->value;
        }
        if (isset($applicant->value->employment_details->value[0]->date_from->value)) {
            $formData->employmentSince = $applicant->value->employment_details->value[0]->date_from->value;
        }
        if (isset($applicant->value->employment_details->value[0]->education->value)) {
            $formData->education = $applicant->value->employment_details->value[0]->education->value;
        }
        if (isset($applicant->value->financial_details->value->source_of_income->value)) {
            $formData->sourcesOfIncome = $applicant->value->financial_details->value->source_of_income->value;
        }
        if (isset($applicant->value->financial_details->value->change_circumstances->value)) {
            $formData->changeCircumstances = $applicant->value->financial_details->value->change_circumstances->value;
        }
        if (isset($applicant->value->financial_details->value->declined_credit_in_past->value)) {
            $formData->declinedCreditInPast = $applicant->value->financial_details->value->declined_credit_in_past->value;
        }
        if (isset($applicant->value->personal_details->value->identity_number->value)) {
            $formData->identityNumber = $applicant->value->personal_details->value->identity_number->value;
        }
        if (isset($applicant->value->additional_fields->value->stripe->value->token->value)) {
            $formData->token = $applicant->value->additional_fields->value->stripe->value->token->value;
        }
        if (isset($applicant->value->additional_fields->value->ip_address->value)) {
            $formData->ipAddress = $applicant->value->additional_fields->value->ip_address->value;
        }
        if (isset($applicant->value->additional_fields->value->consorsfinans->value->insurance->value)) {
            $formData->insurance = $applicant->value->additional_fields->value->consorsfinans->value->insurance->value;
        }
        if (isset($applicant->value->additional_fields->value->consorsfinans->value->bundled_credit_card->value)) {
            $formData->bundledCreditCard = $applicant->value->additional_fields->value->consorsfinans->value->bundled_credit_card->value;
        }
        if (isset($applicant->value->additional_fields->value->consorsfinans->value->bundled_credit_card_insurance->value)) {
            $formData->bundledCreditCardInsurance = $applicant->value->additional_fields->value->consorsfinans->value->bundled_credit_card_insurance->value;
        }
        if (isset($applicant->value->additional_fields->value->consorsfinans->value->accept_promotions_agreement->value)) {
            $formData->acceptPromotionsAgreement = $applicant->value->additional_fields->value->consorsfinans->value->accept_promotions_agreement->value;
        }
        if (isset($applicant->value->order_details->value->shipping_address->value->postcode->value)) {
            $formData->shippingAddress->postcode = $applicant->value->order_details->value->shipping_address->value->postcode->value;
        }
        if (isset($applicant->value->order_details->value->shipping_address->value->flat->value)) {
            $formData->shippingAddress->flat = $applicant->value->order_details->value->shipping_address->value->flat->value;
        }
        if (isset($applicant->value->order_details->value->shipping_address->value->building_name->value)) {
            $formData->shippingAddress->buildingName = $applicant->value->order_details->value->shipping_address->value->building_name->value;
        }
        if (isset($applicant->value->order_details->value->shipping_address->value->co->value)) {
            $formData->shippingAddress->co = $applicant->value->order_details->value->shipping_address->value->co->value;
        }
        if (isset($applicant->value->order_details->value->shipping_address->value->building_number->value)) {
            $formData->shippingAddress->buildingNumber = $applicant->value->order_details->value->shipping_address->value->building_number->value;
        }
        if (isset($applicant->value->order_details->value->shipping_address->value->street->value)) {
            $formData->shippingAddress->street = $applicant->value->order_details->value->shipping_address->value->street->value;
        }
        if (isset($applicant->value->order_details->value->shipping_address->value->town->value)) {
            $formData->shippingAddress->town = $applicant->value->order_details->value->shipping_address->value->town->value;
        }
        if (isset($applicant->value->order_details->value->shipping_address->value->district->value)) {
            $formData->shippingAddress->district = $applicant->value->order_details->value->shipping_address->value->district->value;
        }
        if (isset($applicant->value->employment_details->value[0]->value->job_title->value)) {
            if (isset($applicant->value->employment_details->value->job_title->value)) {
                $formData->employment->employerName = $applicant->value->employment_details->value->job_title->value;
            }
            if (isset($applicant->value->employment_details->value->phone_number->value)) {
                $formData->employment->phoneNumber = $applicant->value->employment_details->value->phone_number->value;
            }
            if (isset($applicant->value->employment_details->value->date_to->value)) {
                $formData->employment->dateLeft = $applicant->value->employment_details->value->date_to->value;
            }
            if (isset($applicant->value->employment_details->value->months_in_employment->value)) {
                $formData->employment->monthsInEmployment = $applicant->value->employment_details->value->months_in_employment->value;
            }
        }
        if (isset($applicant->value->bank_details->value->identification_type->value)) {
            $formData->bank->type = $applicant->value->bank_details->value->identification_type->value;
        }
        if (isset($applicant->value->bank_details->value->sort_code->value)) {
            $formData->bank->sortCode = $applicant->value->bank_details->value->sort_code->value;
        }
        if (isset($applicant->value->bank_details->value->account_number->value)) {
            $formData->bank->accountNumber = $applicant->value->bank_details->value->account_number->value;
        }
        if (isset($applicant->value->bank_details->value->iban->value)) {
            $formData->bank->iban = $applicant->value->bank_details->value->iban->value;
        }
        if (isset($applicant->value->bank_details->value->date_from->value)) {
            $formData->bank->since = $applicant->value->bank_details->value->date_from->value;
        }
        if (isset($applicant->value->financial_details->value->additional_income->value)) {
            $formData->income->additionalIncome = $applicant->value->financial_details->value->additional_income->value;
        }
        if (isset($applicant->value->financial_details->value->net_income->value)) {
            $formData->income->netIncome = $applicant->value->financial_details->value->net_income->value;
        }
        if (isset($applicant->value->financial_details->value->net_income->value)) {
            $formData->spouse->grossIncome = $applicant->value->financial_details->value->spouse->value->gross_income->value;
        }
        if (isset($applicant->value->financial_details->value->net_income->value)) {
            $formData->spouse->netIncome = $applicant->value->financial_details->value->spouse->value->net_income->value;
        }
        if (isset($applicant->value->business_details->value->business_type->value)) {
            $formData->business->type = $applicant->value->business_details->value->business_type->value;
        }
        if (isset($applicant->value->business_details->value->provider_bank->value)) {
            $formData->business->providerBank = $applicant->value->business_details->value->provider_bank->value;
        }
        if (isset($applicant->value->business_details->value->provider_software->value)) {
            $formData->business->providerSoftware = $applicant->value->business_details->value->provider_software->value;
        }
        if (isset($applicant->value->business_details->value->company->value->annual_business_profit->value)) {
            $formData->business->company->annualBusinessProfit = $applicant->value->business_details->value->company->value->annual_business_profit->value;
        }
        if (isset($applicant->value->business_details->value->company->value->annual_turnover->value)) {
            $formData->business->company->annualTurnover = $applicant->value->business_details->value->company->value->annual_turnover->value;
        }
        if (isset($applicant->value->business_details->value->company->value->name->value)) {
            $formData->business->company->name = $applicant->value->business_details->value->company->value->name->value;
        }
        if (isset($applicant->value->business_details->value->company->value->number->value)) {
            $formData->business->company->number = $applicant->value->business_details->value->company->value->number->value;
        }
        if (isset($applicant->value->business_details->value->company->value->industry->value)) {
            $formData->business->company->industry = $applicant->value->business_details->value->company->value->industry->value;
        }
        if (isset($applicant->value->business_details->value->company->value->trading_as->value)) {
            $formData->business->company->tradingAs = $applicant->value->business_details->value->company->value->trading_as->value;
        }
        if (isset($applicant->value->business_details->value->company->value->registered->value)) {
            $formData->business->company->registered = $applicant->value->business_details->value->company->value->registered->value;
        }
        if (isset($applicant->value->business_details->value->company->value->online_sales_percentage->value)) {
            $formData->business->company->onlineSalesPercentage = $applicant->value->business_details->value->company->value->online_sales_percentage->value;
        }
        if (isset($applicant->value->business_details->value->company->value->vat_registered_status->value)) {
            $formData->business->company->vatRegisteredStatus = $applicant->value->business_details->value->company->value->vat_registered_status->value;
        }
        if (isset($applicant->value->business_details->value->company->value->website->value)) {
            $formData->business->company->website = $applicant->value->business_details->value->company->value->website->value;
        }
        if (isset($applicant->value->financial_details->value->loans->value->mortgage->value)) {
            $formData->debt->mortgage = $applicant->value->financial_details->value->loans->value->mortgage->value / 100;
        }
        if (isset($applicant->value->financial_details->value->loans->value->secured_loan->value)) {
            $formData->debt->securedLoan = $applicant->value->financial_details->value->loans->value->secured_loan->value / 100;
        }
        if (isset($applicant->value->financial_details->value->loans->value->unsecured_loan->value)) {
            $formData->debt->unsecuredLoan = $applicant->value->financial_details->value->loans->value->unsecured_loan->value / 100;
        }
        if (isset($applicant->value->financial_details->value->loans->value->student_loan->value)) {
            $formData->debt->studentLoan = $applicant->value->financial_details->value->loans->value->student_loan->value / 100;
        }
        if (isset($applicant->value->financial_details->value->loans->value->applicant_loan_subtotal_percentage->value)) {
            $formData->debt->mortgagePercentage = $applicant->value->financial_details->value->loans->value->applicant_loan_subtotal_percentage->value * 100;
        }

        $formData->addresses = array_map(
            [self::class, 'mapAddresses'],
            $applicant->value->contact_details->value->addresses->value ?? []
        );

        return $formData;
    }

    private static function mapAddresses(object $applicantAddress): object
    {
        $addressKeys = [
            'building_name' => 'buildingName',
            'building_number' => 'buildingNumber',
            'flat' => 'flat',
            'street' => 'street',
            'town' => 'town',
            'district' => 'district',
            'co' => 'co',
            'postcode' => 'postcode',
            'months_at_address' => 'monthsAtAddress',
            'country_code' => 'country',
        ];

        $formDataAddress = new \stdClass();

        foreach ($addressKeys as $applicantKey => $formDataKey) {
            $formDataAddress->$formDataKey = $applicantAddress->value->$applicantKey->value ?? null;
        }

        return $formDataAddress;
    }
}
