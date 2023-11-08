<?php
define('ASSETS_VERSION', '1');

include __DIR__ . '/adminer.php';
include __DIR__ . '/functions.php';

function getPlugins()
{
    $plugins = [
        new AdminerDisableJush,
        new AdminerAutocomplete,
        new AdminerSaveMenuPos,
        new AdminerRemoteColor,
        new AdminerDumpJson,
        new AdminerDumpPhpPrototype,
        new AdminerTablesFilter,
        new AdminerLoginWithoutCredentials,
        new AdminerEditCalendar,
        new AdminerEnumOption,
        new AdminerColorfields,
        new AdminerDatabaseHide([
            'information_schema',
            'mysql',
            'performance_schema',
            'sys',
        ]),
        new searchAutocomplete,
        new FillLoginForm('server', '', 'root', '')
    ];

    if (getenv('ADMINER_SERVER') && getenv('ADMINER_USERNAME')) {
        if (!isset($_GET['username'])) {
            $_GET['username'] = '';
        }

        class AdminerCustomization extends AdminerPlugin
        {
            public function credentials()
            {
                $server = getenv('ADMINER_SERVER');
                $username = getenv('ADMINER_USERNAME');
                $password = getenv('ADMINER_PASSWORD');
                return [$server, $username, $password];
            }
        }

        return new AdminerCustomization($plugins);
    }

    return new AdminerPlugin($plugins);
}
function includeAdminerPluginFile()
{
    include_once __DIR__ . '/plugins/plugin.php';
}

function includeOtherPlugins()
{
    $pluginFiles = glob(__DIR__ . '/plugins/*.php');
    foreach ($pluginFiles as $filename) {
        include_once $filename;
    }
}

function adminer_object()
{
    includeAdminerPluginFile();
    includeOtherPlugins();
    return getPlugins();
}

redirectToHttps();
handleFileRequest();
