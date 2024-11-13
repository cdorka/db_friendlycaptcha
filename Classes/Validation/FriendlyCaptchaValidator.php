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
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class FriendlyCaptchaValidator extends AbstractValidator
{
    protected $acceptsEmptyValues = false;

    /**
     * Checks if the given value is valid according to the validator, and returns
     * the error messages object which occurred.
     */
    public function validate(mixed $value): Result
    {
        $value = trim($this->getRequest()->getParsedBody()['frc-captcha-solution'] ?? '');

        $this->result = new Result();

        if ($this->acceptsEmptyValues === false || $this->isEmpty($value) === false) {
            $this->isValid($value);
        }
        return $this->result;
    }

    /**
     * Validate the captcha value from the request and add an error if not valid
     *
     * @param mixed $value
     * @return void
     */
    public function isValid(mixed $value): void
    {
        $captcha = GeneralUtility::makeInstance(FriendlyCaptchaService::class);

        if ($captcha !== null) {
            $status = $captcha->validateFriendlyCaptcha();

            if ($status == false || $status['error'] !== '') {
                $errorText = $this->translateErrorMessage('error_friendlycaptcha_' . $status['error'], 'db_friendlycaptcha');

                if (empty($errorText)) {
                    // Avoid passing null to htmlspecialchars to prevent deprecation notices
                    if ($status['error'] !== null) {
                        $errorText = htmlspecialchars(
                            $status['error'],
                            ENT_QUOTES | ENT_HTML5,
                            'UTF-8'
                        );
                    } else {
                        // Fallback for missing frontend validation, the case when field value is empty
                        $errorText = $this->translateErrorMessage('validation.error.1347992453', 'form');
                    }
                }

                $this->addError($errorText, 1519982125);
            }
        }
    }

    protected function getRequest(): ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'];
    }

}
