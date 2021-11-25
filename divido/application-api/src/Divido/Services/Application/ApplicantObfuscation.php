<?php

namespace Divido\Services\Application;

use Divido\Helpers\Obfuscate;

/**
 * Class ApplicantObfuscation
 *
 * @author Anders Hallsten <anders.hallsten@divido.com>
 * @copyright (c) 2021, Divido
 */
class ApplicantObfuscation
{
    private $settings;

    /**
     * ApplicantObfuscation constructor.
     * @param array $settings
     */
    function __construct(
        array $settings
    ) {
        $this->settings = $settings;
    }

    /**
     * @param Application $model
     * @return string
     * @throws \JsonException
     */
    public function obfuscate(Application $model)
    {
        $model->setFormData($this->formDataObfuscator($model->getFormData()));

        return $model;
    }

    /**
     * @param $array
     */
    private function form_data_clean_array_walk_recursive(&$array)
    {
        foreach ($array as $key => &$value) {
            if (is_array($value) && count($value) > 0) {
                $this->form_data_clean_array_walk_recursive($value);
            } else if (!empty($value) && in_array($key, ['firstName']) && isset($this->settings['first_name'])) {
                if ($this->settings['first_name'] === 'obfuscate') {
                    $value = Obfuscate::name($value);
                } else if ($this->settings['first_name'] !== 'keep') {
                    $value = null;
                }
            } else if (!empty($value) && in_array($key, ['lastName']) && isset($this->settings['last_name'])) {
                if ($this->settings['last_name'] === 'obfuscate') {
                    $value = Obfuscate::name($value);
                } else if ($this->settings['last_name'] !== 'keep') {
                    $value = null;
                }
            } else if (!empty($value) && in_array($key, ['phoneNumber']) && isset($this->settings['phone_number'])) {
                if ($this->settings['phone_number'] === 'obfuscate') {
                    $value = Obfuscate::phoneNumber($value);
                } else if ($this->settings['phone_number'] !== 'keep') {
                    $value = null;
                }
            } else if (!empty($value) && in_array($key, ['email']) && isset($this->settings['email_address'])) {
                if ($this->settings['email_address'] === 'obfuscate') {
                    $value = Obfuscate::email($value);
                } else if ($this->settings['email_address'] !== 'keep') {
                    $value = null;
                }
            } else if (!empty($value) && in_array($key, ['postcode']) && isset($this->settings['postcode'])) {
                if ($this->settings['postcode'] !== 'keep') {
                    $value = null;
                }
            } else {
                $value = null;
            }

            if (empty($value)) {
                unset($array[$key]);
            }
        }
    }

    /**
     * @param object $formData
     * @return object
     * @throws \JsonException
     */
    private function formDataObfuscator(object $formData): object
    {
        $json = json_encode($formData, JSON_THROW_ON_ERROR, 512);
        $formDataArray = json_decode($json, 1, 512);

        $this->form_data_clean_array_walk_recursive($formDataArray);

        $json = json_encode($formDataArray, JSON_THROW_ON_ERROR, 512);

        return json_decode($json, 0, 512);
    }
}
