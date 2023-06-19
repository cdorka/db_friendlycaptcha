<?php
defined('TYPO3') || die('Access denied.');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'db_friendlycaptcha',
    'Configuration/TypoScript/',
    'FriendlyCaptcha'
);
