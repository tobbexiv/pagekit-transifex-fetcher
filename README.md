# Pagekit Transifex Fetcher

This extension aims at developers of pagekit extensions and themes that use transifex for translation (free for open source projects). It adds a console command to fetch all translations for an extension from transifex and creates the translation files.

## How to work with the extension

1. Create your own extension you want to translate.
2. Use the `extension:translate` console command to create one (or multiple) *.pot file(s).
3. Push your changes to GitHub.
4. Create a transifex project for your extension and set up autosync from GitHub for your translation files. Now every time you redo step (2) and (3) the changes will automatically be replicated to transifex.
5. Install this extension in your development environment using the built in marketplace. The extension and all its dependancies will be installed automatically.
6. Use the `transifex:config` console command to setup the extension. This needs to be done once.
7. Everytime you want to get the new translations, use the `transifex:fetch` console command to fetch the translations from transifex. The extension will automatically create the locale folders and you can ship the translations immediately.

## Setting up the configuration

There are multiple values in the configuration you need to fill. Some of the configurations are globally for all projects and some are on a per extension base. One way to edit the configuration is to use the `transifex:config` console command. The settings are stored in a config.php file in the folder of this addon. If you know how the file should look like, you can also directly edit the configuration file. The file is added to a .gitignore.

**Global configuration**
- *Transifex api token:* Your transifex api token. This is required for authentification at the transifex api. You can maintain it in your [user settings](https://www.transifex.com/user/settings/api/).

**Extension specific configuration**
- *Extension name*: The name of your extension. Usually `<vendor>/<extension>`. It is the folder struture for your extension in the packages folder. For this addon it would be `tobbe/transifex-fetcher`.
- *Transifex project*: The name of your transifex project. It is the part of the project url you can choose freely in the project settings.
- *Transifex resource name*: The name/slug of the transifex resource. You can find it in the settings of your resource.
- *Translation domain*: The domain the resources belongs to. If you [did not specify it explicitly](https://pagekit.com/docs/developer/translation#working-with-message-domains) in your translation command it is `messages` which will automatically be selected if you leave the field blank. It is used as the file name, also for the *.pot file which is the base for your translations.

## Issues and feature requests

Please use the [issues section](https://github.com/tobbexiv/pagekit-transifex-fetcher/issues) to file any bugs or feature requests.

If you face an issue with error 60 and cURL during the fetching, please follow the instructions you can find e.g. on [Stackoverflow](https://stackoverflow.com/a/43637006) to solve it.