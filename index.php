<?php

function adminer_object()
{
    // Required to run any plugin.
    include_once "./plugins/plugin.php";

    // Plugins auto-loader.
    foreach (glob("plugins/*.php") as $filename) {
        include_once "./$filename";
    }

    // Specify enabled plugins here.
    $plugins = [
        new AdminerDatabaseHide(["mysql", "information_schema", "performance_schema","sys"]),
        new AdminerLoginServers([
            filter_input(INPUT_SERVER, 'HTTP_HOST') => filter_input(INPUT_SERVER, 'SERVER_NAME')
        ]),
        new AdminerTablesFilter(),
        new AdminerSimpleMenu(),
        new AdminerCollations(),
        new AdminerJsonPreview(),
        new searchAutocomplete(),
        new AdminerTablesHistory(),
        new AdminerDumpJson(),
        new AdminerDumpDate(),
        new AdminerRestoreMenuScroll(),
        new AdminerReadableDates(),
        new AdminerCopy(),

        // AdminerTheme has to be the last one.
        new AdminerTheme('galpratama-blue'),
    ];

    return new AdminerPlugin($plugins);
}

// Include original Adminer or Adminer Editor.
include "./adminer.php";
