<?php

namespace Jaiwalker\Googlgeanalyticsapi;

/**
 * @author JaiKora <kora.jayaram@gmail.com>
 * @gihub  -  https://github.com/jaiwalker
 */
class config
{
    const 		ANALYTICS_CONFIG_FILE = 'analytics';

    public $key_file_location;
    public $service_account_email;
    public $application_name;



    /**
     * config constructor.
     */
    public function __construct()
    {
         // Only for Codeigniter // -- Un Comment Below lines for codeigniter
       // $this->_ci =& get_instance();
        //log_message('debug', 'Google Api Class Initialized');
       // $this->_ci->config->load(self::ANALYTICS_CONFIG_FILE); // load config file


        $this->key_file_location     = $this->_ci->config->item('key_file_location');
        $this->service_account_email = $this->_ci->config->item('service_account_email');
        $this->application_name      = $this->_ci->config->item('application_name');
    }
}