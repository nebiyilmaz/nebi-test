<?php

namespace Divido\Helpers;

use stdClass;

/**
 * Class MapFormDataToApplicants
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2019, Divido
 */
class MapFormDataToApplicants
{
    public function updateElement($element, $elementName, $value = null)
    {
        if (!isset($element->$elementName)) {
            $object = new StdClass();
            $object->value = null;

            if (!$element) {
                $element = new StdClass();
            }

            $element->$elementName = $object;
        }
        if (!is_null($value)) {
            $element->$elementName->value = $value;
        }

        return $element;
    }

    /**
     * @param $formData
     * @param $applicants
     * @return object
     */
    public function getApplicants($formData, $applicants): object
    {
        $applicant = $applicants->value[0]->value;

        if (!empty($formData->roles)) {
            $applicant = $this->updateElement($applicant, 'role', $formData->roles);
            $applicant->role->value = $formData->roles;
        }

        $applicant = $this->updateElement($applicant, 'personal_details');
        $applicant = $this->updateElement($applicant, 'contact_details');
        $applicant = $this->updateElement($applicant, 'living_arrangements');
        $applicant = $this->updateElement($applicant, 'financial_details');
        $applicant = $this->updateElement($applicant, 'employment_details');
        $applicant = $this->updateElement($applicant, 'additional_fields');
        $applicant = $this->updateElement($applicant, 'order_details');

        if (!empty($formData->firstName)) {
            $applicant->personal_details->value = $this->updateElement($applicant->personal_details->value, 'first_name', $formData->firstName);
        }
        if (isset($formData->middleNames)) {
            $applicant->personal_details->value = $this->updateElement($applicant->personal_details->value, 'middle_names', $formData->middleNames);
        }
        if (isset($formData->lastName)) {
            $applicant->personal_details->value = $this->updateElement($applicant->personal_details->value, 'last_name', $formData->lastName);
        }
        if (isset($formData->gender)) {
            $applicant->personal_details->value = $this->updateElement($applicant->personal_details->value, 'gender', $formData->gender);
        }

        if (isset($formData->phoneNumber) || isset($formData->secondaryPhoneNumber)) {
            $applicant->contact_details->value = $this->updateElement($applicant->contact_details->value, 'phone_numbers');

            if (!isset($applicant->contact_details->value->phone_numbers) || !is_array($applicant->contact_details->value->phone_numbers->value)) {
                $object = new StdClass();
                $object->value = [];
                $applicant->contact_details->value->phone_numbers = $object;
            }
        }

        if (isset($formData->phoneNumber)) {
            if (isset($applicant->contact_details->value->phone_numbers->value[0])) {
                $applicant->contact_details->value->phone_numbers->value[0]->value = $formData->phoneNumber;
            } else {
                $object = new StdClass();
                $object->value = $formData->phoneNumber;
                $applicant->contact_details->value->phone_numbers->value[] = $object;
            }
        }
        if (isset($formData->secondaryPhoneNumber)) {
            if (isset($applicant->contact_details->value->phone_numbers->value[1])) {
                $applicant->contact_details->value->phone_numbers->value[1]->value = $formData->secondaryPhoneNumber;
            } else {
                $object = new StdClass();
                $object->value = $formData->secondaryPhoneNumber;
                $applicant->contact_details->value->phone_numbers->value[] = $object;
            }
        }
        if (isset($formData->email)) {
            $applicant->contact_details->value = $this->updateElement($applicant->contact_details->value, 'email_addresses');

            if (!isset($applicant->contact_details->value->email_addresses) || !is_array($applicant->contact_details->value->email_addresses->value)) {
                $object = new StdClass();
                $object->value = [];
                $applicant->contact_details->value->email_addresses = $object;
            }

            if (isset($applicant->contact_details->value->email_addresses->value[0])) {
                $applicant->contact_details->value->email_addresses->value[0]->value = $formData->email;
            } else {
                $object = new StdClass();
                $object->value = $formData->email;
                $applicant->contact_details->value->email_addresses->value[] = $object;
            }
        }

        if (isset($formData->addresses)) {
            $applicantAddresses = array_map(
                [self::class, 'mapAddresses'],
                $formData->addresses
            );

            $applicant->contact_details->value = $this->updateElement(
                $applicant->contact_details->value,
                'addresses',
                $applicantAddresses
            );
        }

        if (isset($formData->shippingAddress)) {
            $applicant->order_details->value = $applicant->order_details->value ?? new stdClass();
            $applicant->order_details->value->shipping_address = $this->mapAddresses($formData->shippingAddress);
        }

        if (isset($formData->numberOfDependants)) {
            $applicant->living_arrangements->value = $this->updateElement($applicant->living_arrangements->value, 'number_of_dependants', $formData->numberOfDependants);
        }
        if (isset($formData->householdIncome)) {
            $applicant->financial_details->value = $this->updateElement($applicant->financial_details->value, 'household_income', $formData->householdIncome*100);
        }
        if (isset($formData->grossIncome)) {
            $applicant->financial_details->value = $this->updateElement($applicant->financial_details->value, 'gross_income', $formData->grossIncome*100);
        }
        if (isset($formData->rent)) {
            $applicant->living_arrangements->value = $this->updateElement($applicant->living_arrangements->value, 'rent', $formData->rent*100);
        }
        if (isset($formData->monthlyCreditCommitments)) {
            $applicant->financial_details->value = $this->updateElement($applicant->financial_details->value, 'monthly_credit_commitments', $formData->monthlyCreditCommitments*100);
        }
        if (isset($formData->monthlyMortgage)) {
            $applicant->living_arrangements->value = $this->updateElement($applicant->living_arrangements->value, 'monthly_mortgage', $formData->monthlyMortgage*100);
        }
        if (isset($formData->balanceMortgage)) {
            $applicant->living_arrangements->value = $this->updateElement($applicant->living_arrangements->value, 'mortgage_balance', $formData->balanceMortgage*100);
        }
        if (isset($formData->birthName)) {
            $applicant->personal_details->value = $this->updateElement($applicant->personal_details->value, 'birth_last_name', $formData->birthName);
        }
        if (isset($formData->placeOfBirth)) {
            $applicant->personal_details->value = $this->updateElement($applicant->personal_details->value, 'place_of_birth', $formData->placeOfBirth);
        }
        if (isset($formData->dateOfBirthYear) || isset($formData->dateOfBirthMonth) || isset($formData->dateOfBirthDay)) {
            $applicant->personal_details->value = $this->updateElement($applicant->personal_details->value, 'date_of_birth', $formData->dateOfBirthYear."-".$formData->dateOfBirthMonth."-".$formData->dateOfBirthDay);
        }
        if (isset($formData->citizenship)) {
            $applicant->personal_details->value = $this->updateElement($applicant->personal_details->value, 'citizenship', $formData->citizenship);
        }
        if (isset($formData->occupancyStatus)) {
            $applicant->living_arrangements->value = $this->updateElement($applicant->living_arrangements->value, 'occupancy_status', $formData->occupancyStatus);
        }
        if (isset($formData->occupancySince)) {
            $applicant->living_arrangements->value = $this->updateElement($applicant->living_arrangements->value, 'occupancy_since', $formData->occupancySince);
        }
        if (isset($formData->maritalStatus)) {
            $applicant->personal_details->value = $this->updateElement($applicant->personal_details->value, 'marital_status', $formData->maritalStatus);
        }
        $employmentDetails = new StdClass();
        if (!is_array($applicant->employment_details->value)) {
            $applicant->employment_details->value = [];
        }
        if (isset($formData->employmentBranch)) {
            $employmentDetails = $this->updateElement($employmentDetails, 'industry', $formData->employmentBranch);
        }
        if (isset($formData->employmentStatus)) {
            $employmentDetails = $this->updateElement($employmentDetails, 'employment_status', $formData->employmentStatus);
        }
        if (isset($formData->employmentSince)) {
            $employmentDetails = $this->updateElement($employmentDetails, 'date_from', $formData->employmentSince);
        }
        if (isset($formData->education)) {
            $employmentDetails = $this->updateElement($employmentDetails, 'education', $formData->education);
        }
        if (isset($formData->education)) {
            $employmentDetails = $this->updateElement($employmentDetails, 'education', $formData->education);
        }

        $applicant->employment_details->value[0] = (object) ['value'=>$employmentDetails];

        if (isset($formData->sourcesOfIncome)) {
            $applicant->financial_details->value = $this->updateElement($applicant->financial_details->value, 'source_of_income', $formData->sourcesOfIncome);
        }
        if (isset($formData->changeCircumstances)) {
            $applicant->financial_details->value = $this->updateElement($applicant->financial_details->value, 'change_circumstances', $formData->changeCircumstances);
        }
        if (isset($formData->declinedCreditInPast)) {
            $applicant->financial_details->value = $this->updateElement($applicant->financial_details->value, 'declined_credit_in_past', $formData->declinedCreditInPast);
        }
        if (isset($formData->identityNumber)) {
            $applicant->personal_details->value = $this->updateElement($applicant->personal_details->value, 'identity_number', $formData->identityNumber);
        }
        if (isset($formData->token)) {
            $applicant->additional_fields->value = $this->updateElement($applicant->additional_fields->value, "stripe");
            $applicant->additional_fields->value->stripe->value = $this->updateElement($applicant->additional_fields->value->stripe->value, 'token', $formData->token);
        }
        if (isset($formData->ipAddress)) {
            $applicant->additional_fields->value = $this->updateElement($applicant->additional_fields->value, 'ip_address', $formData->ipAddress);
        }
        if (isset($formData->insurance)) {
            $applicant->additional_fields->value = $this->updateElement($applicant->additional_fields->value, "consorsfinans");
            $applicant->additional_fields->value->consorsfinans->value = $this->updateElement($applicant->additional_fields->value->consorsfinans->value, 'insurance', $formData->insurance);
        }
        if (isset($formData->bundledCreditCard)) {
            $applicant->additional_fields->value = $this->updateElement($applicant->additional_fields->value, "consorsfinans");
            $applicant->additional_fields->value->consorsfinans->value = $this->updateElement($applicant->additional_fields->value->consorsfinans->value, 'bundled_credit_card', $formData->bundledCreditCard);
        }
        if (isset($formData->bundledCreditCardInsurance)) {
            $applicant->additional_fields->value = $this->updateElement($applicant->additional_fields->value, "consorsfinans");
            $applicant->additional_fields->value->consorsfinans->value = $this->updateElement($applicant->additional_fields->value->consorsfinans->value, 'bundled_credit_card_insurance', $formData->bundledCreditCardInsurance);
        }
        if (isset($formData->acceptPromotionsAgreement)) {
            $applicant->additional_fields->value = $this->updateElement($applicant->additional_fields->value, "consorsfinans");
            $applicant->additional_fields->value->consorsfinans->value = $this->updateElement($applicant->additional_fields->value->consorsfinans->value, 'accept_promotions_agreement', $formData->acceptPromotionsAgreement);
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

        if (empty((array) $applicant->personal_details->value)) {
            unset($applicant->personal_details);
        }
        if (empty((array) $applicant->contact_details->value)) {
            unset($applicant->contact_details);
        }
        if (empty((array) $applicant->living_arrangements->value)) {
            unset($applicant->living_arrangements);
        }
        if (empty((array) $applicant->financial_details->value)) {
            unset($applicant->financial_details);
        }
        if (empty((array) $applicant->additional_fields->value)) {
            unset($applicant->additional_fields);
        }
        if (empty((array) $applicant->order_details->value)) {
            unset($applicant->order_details);
        }
        if (empty((array) $applicant->employment_details->value[0])) {
            unset($applicant->employment_details);
        }

        if (!$applicants->value[0]) {
            $applicants->value[0] = new StdClass();
        }

        $applicants->value[0]->value = $applicant;

        return $applicants;
    }

    /**
     * @param object $formDataAddress
     * @return object
     */
    private function mapAddresses(object $formDataAddress): object
    {
        $addressKeys = [
            'buildingName' => 'building_name',
            'buildingNumber' => 'building_number',
            'flat' => 'flat',
            'street' => 'street',
            'town' => 'town',
            'district' => 'district',
            'co' => 'co',
            'postcode' => 'postcode',
            'monthsAtAddress' => 'months_at_address',
            'country' => 'country_code'
        ];

        $applicantAddress = new \stdClass();
        $applicantAddress->value = new \stdClass();

        foreach ($addressKeys as $formDataKey => $applicantKey) {
            $applicantAddress->value->$applicantKey = new \stdClass();
            $applicantAddress->value->$applicantKey->value = $formDataAddress->$formDataKey ?? "";
            if ($applicantKey === 'months_at_address') {
                $applicantAddress->value->$applicantKey->value = $formDataAddress->$formDataKey ?? null;
            }
        }

        return $applicantAddress;
    }
}
