<?php

namespace Tobbe\TransifexFetcher\Commands;

use Pagekit\Application\Console\Command;
// use Pagekit\Console\Translate\TransifexApi; Not used due to error in the conversion of the response to json.
use Tobbe\TransifexFetcher\Helper\TransifexApi;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Pagekit\Config\Config;

class TransifexFetchCommand extends TransifexBaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'transifex:fetch';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Fetches the translations from transifex and generates .php files';
    
    /**
     * The Transifex Api
     * 
     * @var TransifexApi
     */
    protected $transifexApi;

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if(!$this->loadConfig()) {
            $this->abort('No configuration file exists. Please maintain the configuration using the transifex:config command.');
        }
        
        // check apitoken
        $apitoken = $this->config->get('general.apitoken');
        if(!$apitoken) {
            $this->abort('General configuration not correct or missing. Transifex apitoken unknown. Please maintain the configuration using the transifex:config command.');
        }
        
        // Get the extension config and check it.
        if(!$extensionsConfig = $this->config->get("extension")) {
            $this->abort('Extension configuration missing. Please maintain the configuration using the transifex:config command.');
        }
        
        $availableExtensions = $this->checkExtensionAvailability($extensionsConfig);
        
        while($selectedExtensionName = $this->selectExtension($availableExtensions)) {
            $selectedExtension = $extensionsConfig[$selectedExtensionName];
            
            $this->transifexApi= new TransifexApi($apitoken, $selectedExtension['project']);
            
            $availableResources = $this->getResourceList($selectedExtension['resourceMapping']);
            while($selectedResourceName = $this->selectResource($availableResources)) {
                $this->fetchTranslations($selectedExtensionName, $selectedResourceName, $selectedExtension['resourceMapping'][$selectedResourceName]);
            }
        }
    }
    
    /**
     * Check whether there are errors in the extension configuration and whether it exists and gives a list of all extensions which are ok.
     * 
     * @param array $extensionsConfig
     *   The different configurations for the extensions.
     * @return array
     *   The available extensions without bigger errors.
     */
    protected function checkExtensionAvailability($extensionsConfig) {
        $list = array();
        foreach ($extensionsConfig as $extension => $config) {
            if(!$this->extensionOk($extension)) {
                $this->comment("Extension $extension does not exist and is therefore being ignored. You can correct this by using the transifex:config command.");
            } elseif(!isset($config['project'])) {
                $this->comment("Extension $extension has incomplete configuration (project is missing) and is therefore being ignored. You can correct this by using the transifex:config command.");
            } elseif(!isset($config['resourceMapping']) || count($config['resourceMapping']) < 1) {
                $this->comment("Extension $extension has incomplete configuration (resource mapping is missing) and is therefore being ignored. You can correct this by using the transifex:config command.");
            } else {
                $list[] = $extension;
            }
        }
        return $list;
    }
    
    /**
     * Lets the user select an extension.
     * 
     * @param array $availableExtensions
     *   Names of all extensions which are configured.
     * @return string
     *   The extension selected by the user or null.
     */
    protected function selectExtension($availableExtensions) {
        $choice = $this->userChoice("Please select an extension to fetch the translations:", $availableExtensions);
        return $choice > 0 ? $availableExtensions[$choice - 1] : null;
    }
    
    /**
     * Gets a list of the names of all resources out of the resource domain mapping.
     * 
     * @param array $resources
     *   All resource configurations.
     * @return array
     *   A list of only the resource names.
     */
    protected function getResourceList($resources) {
        $list = array();
        foreach ($resources as $resource => $domain) {
            $list[] = $resource;
        }
        return $list;
    }
    
    /**
     * Lets the user select a resource.
     * 
     * @param array $availableResources
     *   Names of all resources which are configured.
     * @return string
     *   The resource selected by the user or null.
     */
    protected function selectResource($availableResources) {
        $choice = $this->userChoice("Please select a resource to fetch the translations:", $availableResources);
        return $choice > 0 ? $availableResources[$choice - 1] : null;
    }
    
    /**
     * Fetch the translations and store them in all available languages for one specific resource.
     * 
     * @param string $extensionName
     *   The name of the extension.
     * @param string $resourceName
     *   The name of the transifex resource.
     * @param string $resourceDomain
     *   The domain for the translations.
     */
    protected function fetchTranslations($extensionName, $resourceName, $resourceDomain) {
        $locales = $this->transifexApi->fetchLocales($resourceName);
        
        $this->line('');
        $this->info("Updating translations for $extensionName domain $resourceDomain");
        
        $date = date("Y-m-d H:iO");
        $filecontent = <<<EOD
<?php
/* 
 * %s translation for $extensionName
 * Last update at $date
 */
return %s;
EOD;
        
        foreach ($locales as $locale) {
            $this->line("Fetching translations for locale $locale");
            $translations = $this->transifexApi->fetchTranslations($resourceName, $locale);
            
            // New languages don't have a folder yet
            $folder = sprintf('%s/languages/%s/', $this->getPath($extensionName), $locale);
            if (!is_dir($folder)) {
                mkdir($folder, 0755, true);
            }
            
            // Write translation file
            $domain   = 
            $filename = "$folder/$resourceDomain.php";
            $content  = sprintf($filecontent, $locale, var_export($translations, true));
            file_put_contents($filename, $content);
        }
        
        $this->info('All translations are fetched and updated.');
    }
}
