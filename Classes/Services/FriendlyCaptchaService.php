<?php

namespace BalatD\FriendlyCaptcha\Services;

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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\Exception\MissingArrayPathException;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class FriendlyCaptchaService
{
    protected array $configuration = [];

    public function __construct(
        protected ConfigurationManagerInterface $configurationManager,
        protected TypoScriptService $typoScriptService,
        protected ContentObjectRenderer $contentRenderer
    ) {
        $this->initialize();
    }

    protected function initialize(): void
    {
        $configuration = GeneralUtility::makeInstance(
            ExtensionConfiguration::class
        )->get('db_friendlycaptcha');

        if (!is_array($configuration)) {
            $configuration = [];
        }

        $typoScriptConfiguration = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
            'friendlycaptcha'
        );

        if (!empty($typoScriptConfiguration) && is_array($typoScriptConfiguration)) {
            ArrayUtility::mergeRecursiveWithOverrule(
                $configuration,
                $this->typoScriptService->convertPlainArrayToTypoScriptArray($typoScriptConfiguration),
                true,
                false
            );
        }

        if (!is_array($configuration) || empty($configuration)) {
            throw new MissingArrayPathException(
                'Please configure plugin.tx_db_friendlycaptcha. before rendering the friendlycaptcha',
                1417680291
            );
        }

        $this->configuration = $configuration;
    }

    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    /**
     * Build Friendly Captcha Frontend HTML-Code
     */
    public function getFriendlyCaptcha(): string
    {
        return $this->contentRenderer->stdWrap(
            $this->configuration['public_key'],
            $this->configuration['public_key.']
        );
    }

    /**
     * Validate Friendly Captcha challenge/response
     */
    public function validateFriendlyCaptcha(): array
    {
        $request = [
            'solution' => trim($this->getRequest()->getParsedBody()['frc-captcha-solution'] ?? ''),
            'secret' => $this->configuration['private_key'],
            'sitekey' => $this->configuration['public_key'],
        ];

        $result = ['verified' => false, 'error' => ''];

        if (empty($request['solution'])) {
            $result['error'] = 'missing-input-solution';
        } else {
            $response = $this->queryVerificationServer($request);

            if (!$response) {
                $result['error'] = 'validation-server-not-responding';
            }

            if ($response['success']) {
                $result['verified'] = true;
            } else {
                $result['error'] = is_array($response['error-codes']) ?
                    reset($response['error-codes']) :
                    $response['error-codes'];
            }
        }

        return $result;
    }

    /**
     * Query Friendly Captcha server for captcha-verification
     */
    protected function queryVerificationServer(array $data): array
    {
        $verifyServerInfo = @parse_url($this->configuration['verify_server']);
        $guzzleClient = new Client();

        if (empty($verifyServerInfo)) {
            return [
                'success' => false,
                'error-codes' => 'friendlycaptcha-not-reachable'
            ];
        }

        $response = $guzzleClient->post(
            $this->configuration['verify_server'],
            [RequestOptions::JSON => $data]
        )->getBody();

        return $response ? json_decode($response, true) : [];
    }

    protected function getRequest(): ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'];
    }
}
