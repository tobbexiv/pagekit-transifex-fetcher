<?php

namespace Tobbe\TransifexFetcher\Commands;

use Pagekit\Application\Console\Command;
use Pagekit\Config\Config;

class TransifexBaseCommand extends Command
{
    /**
     * The configuration
     * 
     * @var Config
     */
    protected $config;
    
    /**
     * The path to the configuration file for the communication with transifex.
     * 
     * @var String
     */
    protected $configPath;
    
    /**
     * Load the configuration.
     * 
     * @return boolean
     *   If a configuration file exists.
     */
    protected function loadConfig() {
        
        $this->configPath = $this->getPath("tobbe/transifex-fetcher") . "/config.php";
        
        if($configFileExists = is_file($this->configPath)) {
            $this->config = new Config(include $this->configPath);
        } else {
            $this->config = new Config();
        }
        
        return $configFileExists;
    }

    /**
     * Let the user make a decision between multiple options.
     * 
     * @param string $question
     *   The question to ask to the user.
     * @param array $options
     *   The possible options the user can choose.
     * @return number
     *   The selected choice.
     */
    protected function userChoice($question, $options) {
        $selected = null;
        $itemCount = count($options);
        
        do {
            $this->line('');
            $this->line($question);
            $this->line('0 - Back / finish');
            for ($i = 1; $i <= $itemCount; $i++) {
                $this->line("$i - " . $options[$i - 1]);
            }
            $selected = $this->userInput('Please enter the number of your selection:');
            
            if ($selected != null) {
                $selected = intval($selected);
                
                if ($selected < 0 == true || $selected > $itemCount == true) {
                    $selected = null;
                }
            }
        } while ($selected === null);
        
        return $selected;
    }
    
    /**
     * Ask a yes/no question to the user.
     * 
     * @param string
     *   The question to ask to the user.
     * @return boolean
     *   Whether the user confirmed the question.
     */
    protected function userConfirm($question) {
        $confirm = null;
        
        while($confirm == null || (strcasecmp($confirm, 'y') != 0 && strcasecmp($confirm, 'n') != 0)) {
            $confirm = $this->userInput($question . ' (y/n)');
        }
        
        return strcasecmp($confirm, 'y') == 0;
    }
    
    /**
     * Ask the user for input.
     * 
     * @param string
     *   The question to ask to the user.
     * @return string
     *   The entered string.
     */
    protected function userInput($question) {
        $this->line('');
        $this->line($question);
        return $this->ask('');
    }
    
    /**
     * Ask the user for input, but do not display the user input.
     * 
     * @param string
     *   The question to ask to the user.
     * @return string
     *   The entered string.
     */
    protected function userInputSecret($question) {
        $this->line('');
        $this->line($question);
        return $this->secret('');
    }
    
    /**
     * Checks whether an extension exists and is allowed.
     * 
     * @param string $extension
     *   The extension to check.
     * @param boolean $abortInErrorCase
     *   If the script execution should be aborted in case of an error.
     * @return boolean
     *   If the extension exists and is allowed.
     */
    protected function extensionOk($extension, $abortInErrorCase = false) {
        if ($extension== 'system') {
            // system module
            if($abortInErrorCase) {
                $this->abort("Only use this extension for packages!");
            } else {
                $this->line("Only use this extension for packages!");
                return false;
            }
        }
        
        if(!is_dir($this->container->path() . '/packages/' . $extension)) {
            if($abortInErrorCase) {
                $this->abort("Extension '$extension' does not exist");
            } else {
                $this->line("Extension '$extension' does not exist");
                return false;
            }
        }
        
        return true;
    }

    /**
     * Returns the extension path.
     *
     * @param  string $extension
     *   The extension to get the path for.
     * @return string
     *   The extension path.
     */
    protected function getPath($extension, $abortInErrorCase = true)
    {
        if($this->extensionOk($extension, $abortInErrorCase)) {
            return $this->container->path() . '/packages/' . $extension;
        }
        
        return '';
    }
}
