<?php

call_user_func(function () {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['locallangXMLOverride']['EXT:form/Resources/Private/Language/Database.xlf'][] =
        'EXT:db_friendlycaptcha/Resources/Private/Language/Backend.xlf';

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup('
        module.tx_form.settings.yamlConfigurations {
            1998 = EXT:db_friendlycaptcha/Configuration/Yaml/FormSetup.yaml
        }
    ');
});
