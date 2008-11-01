<?php

class PolicyManager implements ILocationPolicy, ILinkPolicy
{
    private static $instance = null;
    
    /**
     * The location policy
     *
     * @var ILocationPolicy
     */
    private $locationPolicy = null;
    
    /**
     * The link policy
     *
     * @var ILinkPolicy
     */
    private $linkPolicy = null;
    
    public function get($policy)
    {
        switch ($policy) {
            case 'location':
                return $this->locationPolicy;
            case 'link':
                return $this->linkPolicy;
            default:
                return null;
        }
    }
    
    public function set($policy, $handler)
    {
        switch ($policy) {
            case 'link':
                if (!($handler instanceof ILinkPolicy)) {
                    throw new IllegalArgumentException("handler for $policy does not adhere to ILinkPolicy");
                }
                
                $this->linkPolicy = $handler;
            case 'location':
                if (!($handler instanceof ILocationPolicy)) {
                    throw new IllegalArgumentException("handler for $policy does not adhere to ILocationPolicy");
                }
                
                $this->locationPolicy = $handler;
            default:
                throw new IllegalArgumentException("unknown policy $policy");
        }
    }
    
    public function getTemplatesDir()
    {
        return $this->locationPolicy->getTemplatesDir();
    }
    
    public function getLogsDir()
    {
        return $this->locationPolicy->getLogsDir();
    }
    
    public function getCacheDir()
    {
        return $this->locationPolicy->getCacheDir();
    }
    
    public function logs()
    {
        $this->locationPolicy->logs();
    }

    /**
     * @see ILinkPolicy::parse()
     *
     */
    public function parse()
    {
        $this->linkPolicy->parse();
    }

    /**
     * @see ILinkPolicy::getActionURI()
     * @return string
     */
    public function getActionURI($component, $action, $options=array(), $stage=Stage::VIEW)
    {
        return $this->linkPolicy->getActionURI($component, $action, $options, $stage);
    }
    
    /**
     * Gets a PolicyManager instance
     *
     * @return PolicyManager
     */
    public static function getInstance()
    {
        if (!PolicyManager::$instance) {
            PolicyManager::$instance = new PolicyManager();
        }
        
        return PolicyManager::$instance;
    }
    
    private function __construct() {
        $this->locationPolicy = new DefaultLocationPolicy();
        $this->linkPolicy = new DefaultLinkPolicy();
    }
}

?>