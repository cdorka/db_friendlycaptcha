<?php

namespace BalatD\FriendlyCaptcha\ViewHelpers\Form;

use BalatD\FriendlyCaptcha\Services\FriendlyCaptchaService;

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
class FriendlycaptchaViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper
{
    protected FriendlyCaptchaService $captchaService;

    public function __construct(FriendlyCaptchaService $captchaService)
    {
        $this->captchaService = $captchaService;
        parent::__construct();
    }

    public function render(): string
    {
        $name = $this->getName();
        $this->registerFieldNameForFormTokenGeneration($name);

        $this->templateVariableContainer->add('configuration', $this->captchaService->getConfiguration());
        $this->templateVariableContainer->add('name', $name);

        $content = $this->renderChildren();

        $this->templateVariableContainer->remove('name');
        $this->templateVariableContainer->remove('configuration');

        return $content;
    }
}
