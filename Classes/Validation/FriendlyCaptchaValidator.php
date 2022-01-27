<?php

namespace BalatD\FriendlyCaptcha\Validation;

/**
 * This file is developed by balatD.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use BalatD\FriendlyCaptcha\Services\FriendlyCaptchaService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FriendlyCaptchaValidator extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator
{
    protected $acceptsEmptyValues = false;

    /**
     * Checks if the given value is valid according to the validator, and returns
     * the error messages object which occurred.
     *
     * @param mixed $value The value that should be validated
     * @return \TYPO3\CMS\Extbase\Error\Result
     */
    public function validate($value)
    {
        $value = trim(\TYPO3\CMS\Core\Utility\GeneralUtility::_POST('frc-captcha-solution'));
        $this->result = new \TYPO3\CMS\Extbase\Error\Result();

        if ($this->acceptsEmptyValues === false || $this->isEmpty($value) === false) {
            $this->isValid($value);
        }
        return $this->result;
    }

    /**
     * Validate the captcha value from the request and add an error if not valid
     *
     * @param mixed $value The value
     */
    public function isValid($value)
    {
        $captcha = GeneralUtility::getContainer()->get(FriendlyCaptchaService::class);

        if ($captcha !== null) {
            $status = $captcha->validateFriendlyCaptcha();

            if ($status == false || $status['error'] !== '') {
                $errorText = $this->translateErrorMessage('error_friendlycaptcha_' . $status['error'], 'db_friendlycaptcha');

                if (empty($errorText)) {
                    $errorText = htmlspecialchars($status['error']);
                }

                $this->addError($errorText, 1519982125);
            }
        }
    }

}
