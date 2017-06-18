<?php

namespace Tobbe\TransifexFetcher\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TransifexConfigCommand extends TransifexBaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'transifex:config';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Update the configuration for the transifex fetcher';

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->loadConfig();
        
        while($type = $this->userChoice('What do you want to configure?', ['Transifex api token', 'Extension specific options', 'Delete extension specific options'])) {
            switch ($type) {
                case 1:
                    $this->config->set('general.apitoken', $this->userInputSecret('Your transifex api token:'));
                    $this->comment("Transifex apitoken was updated.");
                    break;
                case 2:
                    $this->changeExtensionConfig();
                    break;
                case 3:
                    $this->deleteExtensionConfig();
                    break;
                default:
                    break;
            }
        }
        
        if($this->config->dirty()) {
            if($this->userConfirm("Save the changes?")) {
                $this->saveConfig();
            } else {
                $this->line('');
                $this->line('Changes discarded, nothing saved.');
            }
        } else {
            $this->line('');
            $this->line('No changes, nothing to save.');
        }
    }
    
    /**
     * Change the extension specific configuration.
     */
    protected function changeExtensionConfig() {
        $extension = '';
        
        do {
            $extension = $this->userInput("The extension to configure (leave empty to cancel):");
        } while($extension != null && !$this->extensionOk($extension));
        
        if($extension == null) {
            return;
        }
        
        while($type = $this->userChoice('What do you want to configure?', ['Transifex project', 'Transifex resource and domain mapping'])) {
            switch ($type) {
                case 1:
                    $this->config->set("extension.$extension.project", $this->userInput('Your transifex project?'));
                    $this->comment("Transifex project for extension $extension was updated: " . $this->config->get("extension.$extension.project"));
                    break;
                case 2:
                    $resource = $this->userInput('Your transifex resource name:');
                    if($resource) {
                        $domain = $this->userInput('The domain name. The domain name is "messages" by default and must only be changed if a different domain is used (https://pagekit.com/docs/developer/translation#working-with-message-domains).');
                        $domain = $domain == null ? 'messages' : $domain;
                        
                        $this->config->set("extension.$extension.resourceMapping.$resource", $domain);
                        $this->comment("Transifex resource $resource for extension $extension was updated to a new domain: " . $this->config->get("extension.$extension.resourceMapping.$resource"));
                    }
                    break;
                default:
                    break;
            }
        }
    }
    
    /**
     * Delete the configuration for one extension.
     */
    protected function deleteExtensionConfig() {
        $configuredExtensions = array();
        foreach ($this->config->get('extension') as $extension => $config) {
            $configuredExtensions[] = $extension;
        }
        
        $choice = $this->userChoice("Please select the extension where you want to delete the configuration for:", $configuredExtensions);
        $extension = $choice > 0 ? $configuredExtensions[$choice - 1] : null;
        
        if($extension !== null && $this->userConfirm("Are you sure to delete the transifex fetcher configuration for extension $extension?")) {
            $this->config->remove("extension.$extension");
            $this->comment("Configuration for $extension was removed.");
        }
    }
    
    /**
     * Save the configuration.
     */
    protected function saveConfig() {
        file_put_contents($this->configPath, $this->config->dump());
        
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($this->configPath);
        }
        
        $this->line('');
        $this->info("Configuration saved.");
    }
}
