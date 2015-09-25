<?php

namespace Jaiwalker\Googlgeanalyticsapi;

use Google_Client;

/**
 * @author  JaiKora <kora.jayaram@gmail.com>
 * @github  -  https://github.com/jaiwalker
 */
class Analytics
{
    /**
     * @var \CI_Controller
     */
    protected $_ci;

    /**
     * @var
     */
    protected $analytics;

    /**
     * @var
     */
    protected $profileId;

    /**
     * @var array
     */
    protected $errors = array();
    /**
     * @var string
     */
    protected $dimension = '';
    /**
     * @var string
     */
    protected $metric = '';
    /**
     * @var string
     */
    protected $segment = '';
    /**
     * @var string
     */
    protected $dsegment = '';
    /**
     * @var bool
     */
    protected $sort_by = false;
    /**
     * @var bool
     */
    protected $startDate = false;
    /**
     * @var bool
     */
    protected $endDate = false;
    /**
     * @var string
     */
    protected $max_results = '';

    /**
     * @var bool
     */
    protected $filters = false;
    /**
     * @var bool
     */
    protected $debug = true;
    protected $outputType;


    /**
     * Analytics constructor.
     *
     * @internal param $_ci
     */
    public function __construct()
    {
        $this->_ci =& get_instance();
        log_message('debug', 'Google Api Class Initialized');

        $this->initialize();
        // $this->getFirstprofileId();
    }

    /**
     * @return $this
     */
    public function initialize()
    {

        // Creates and returns the Analytics service object.

        // Load the Google API PHP Client Library.

        // Use the developers console and replace the values with your
        // service account email, and relative location of your key file.


        // Create and configure a new client object.
        $client = new Google_Client();
        $client->setApplicationName("My Project");
        $analytics = new Google_Service_Analytics($client);

        // Read the generated client_secrets.p12 key.
        $key  = file_get_contents($key_file_location);
        $cred = new Google_Auth_AssertionCredentials($service_account_email, array(Google_Service_Analytics::ANALYTICS_READONLY), $key);
        $client->setAssertionCredentials($cred);
        if ($client->getAuth()->isAccessTokenExpired()) {
            $client->getAuth()->refreshTokenWithAssertion($cred);
        }

        $this->analytics = $analytics;

        return $this;
    }


    /**
     * @param null $profileid
     * todo: validate Given profile id  set it up
     *
     * @return $this
     * @throws \Exception
     */
    function getFirstprofileId($profileid = null)
    {
        // Get the user's first view (profile) ID.

        // Get the list of accounts for the authorized user.
        $accounts = $this->analytics->management_accounts->listManagementAccounts();

        if (count($accounts->getItems()) > 0) {
            $items          = $accounts->getItems();
            $firstAccountId = $items[0]->getId();

            // Get the list of properties for the authorized user.
            $properties = $this->analytics->management_webproperties->listManagementWebproperties($firstAccountId);

            if (count($properties->getItems()) > 0) {
                $items           = $properties->getItems();
                $firstPropertyId = $items[0]->getId();

                // Get the list of views (profiles) for the authorized user.
                $profiles = $this->analytics->management_profiles->listManagementProfiles($firstAccountId, $firstPropertyId);

                if (count($profiles->getItems()) > 0) {
                    $items = $profiles->getItems();

                    // Return the first view (profile) ID.
                    $this->profileId = $items[0]->getId();

                    return $this;
                    // return

                } else {
                    throw new Exception('No views (profiles) found for this user.');
                }
            } else {
                throw new Exception('No properties found for this user.');
            }
        } else {
            throw new Exception('No accounts found for this user.');
        }
    }

    /**
     * Get Set profile id
     *
     * @return mixed
     */
    public function getProfileID()
    {
        return $this->profileId;
    }


    /**
     * dimension function.
     *
     * @access public
     *
     * @param mixed $arr_or_str
     *
     * @return $this
     */
    function dimension($arr_or_str)
    {
        $this->dimension = $this->_values_converter($arr_or_str);

        return $this;
    }


    /**
     * metric function.
     *
     * @access public
     *
     * @param mixed $arr_or_str
     *
     * @return $this
     */
    function metric($arr_or_str)
    {
        $this->metric = $this->_values_converter($arr_or_str);

        return $this;
    }

    /**
     * limit function.
     *
     * @access public
     *
     * @param int $results . (default: 50)
     *
     * @return $this
     *
     * todo : Implement Off set for Pagination
     */
    function limit($results = 50)
    {
        $this->max_results = $results;

        return $this;
    }
    
    /**
     * Set Output type  - 'json','dataTable','Table'
     * @param string $type
     *
     * @return void
     */
    public function outputAs($type='json')
    {
         $this->outputType = $type;

         return $this;
    }

    public function raw()
    {
        $results = $this->_build();

        return $results;
    }

    /**
     * get_object function.
     *
     * @access   public
     * @internal param mixed $config
     */
    function get_data()
    {
        $results = $this->_build();

        $data = new stdClass();
        $data->rows = $results->getRows();
        $data->sampledate = $results->getContainsSampledData();
        $data->selflink = $results->getSelfLink();

        return $data;
    }

    /**
     * Get profile Information
     *
     * Todo : Pagination https://developers.google.com/analytics/devguides/reporting/core/v3/coreDevguide?hl=en
     *
     * @return \stdClass
     */
    public function getProfileInformation()
    {
        $profileInfo = $this->_build();

        $profile                       = new stdClass();
        $profile->accountID            = $profileInfo->getAccountId();
        $profile->webPropertyID        = $profileInfo->getWebPropertyId();
        $profile->interalWebPropertyID = $profileInfo->getInternalWebPropertyId();
        $profile->id                   = $profileInfo->getProfileId();
        $profile->tableId              = $profileInfo->getTableId();
        $profile->name                 = $profileInfo->getProfileName();

        return $profile;
    }



    /**
     * _values_converter function.
     * This is  to convert Necessary  array or string into respective format
     * @access private
     */
    function _values_converter($arr_or_str)
    {
        if (is_string($arr_or_str) && strpos($arr_or_str, ',')) {
            $arr_or_str = explode(',', $arr_or_str);
        }

        if (is_array($arr_or_str)) {
            foreach ($arr_or_str as $key => $string) {
                $arr_or_str[$key] = $this->_ga_prefix(trim($string));
            }
            $output = implode(',', $arr_or_str);
        } else {
            $output = $this->_ga_prefix(trim($arr_or_str));
        }

        return $output;
    }


//    /**
//     * @param null $params
//     *
//     * @return mixed
//     */
//    function getResults($params = null)
//    {
//        // Calls the Core Reporting API and queries for the number of sessions
//        // for the last seven days.
//
//        $startDateDateDate = date('Y-m-d', strtotime('-31 days')); // 31 days from now
//        $endDate           = date('Y-m-d'); // todays date
//
//        $metrics = "ga:users";
//
//        $optParams = array("dimensions" => "ga:date");
//
//        return $this->analytics->data_ga->get('ga:' . $this->profileId, $startDateDate, 'today', 'ga:OrganicSearches');
//        //       return $analytics->data_ga->get(
//        //           'ga:' . $profileId,
//        //           '1daysAgo',
//        //           'today',
//        //           'ga:users');
//    }

//    /**
//     * @param string $startDate
//     * @param string $endDate
//     * @param int    $max
//     *
//     * @return mixed
//     */
//    public function getTopkeywords($startDate = 'yesterday', $endDate = 'yesterday', $max = 25)
//    {
//        if ($this->analytics) {
//            try {
//                //return $this->analytics->data_ga->get('startDate-date=yesterday&end-date=yesterday&metrics=ga%3AnewUsers%2Cga%3AbounceRate%2Cga%3AsessionDuration');
//                return $this->analytics->data_ga->get('ga:' . $this->profileId, $startDate, $endDate, 'ga:visits', array(
//                        'dimensions'  => 'ga:source,ga:keyword',
//                        'sort'        => '-ga:visits,ga:keyword',
//                        'filters'     => 'ga:medium==organic',
//                        'max-results' => $max,
//                ));
//            } catch (Exception $ex) {
//                $this->errors[] = $ex->getMessage();
//            }
//
//        }
//
//    }

//    /**
//     * @param string $startDateDate
//     * @param string $endDate
//     * @param string $type
//     *
//     * @return mixed
//     */
//    public function getTotal($startDateDate = 'yesterday', $endDate = 'yesterday', $type = 'users')
//    {
//        if ($this->analytics) {
//            //  $analytics = new Google_AnalyticsService($this->client);
//            try {
//                $startDateDate = date('Y-m-d', strtotime('-31 days')); // 31 days from now
//                $optParams     = array('max-results' => '100');
//                $results       = $this->analytics->data_ga->get('ga:' . $this->profileId, $startDateDate, 'yesterday', 'ga:' . $type, $optParams);
//
//                return $results;
//            } catch (Exception $ex) {
//                $this->errors[] = $ex->getMessage();
//            }
//        }
//    }


    /**
     * @return mixed
     */
    protected function _build()
    {
        if (!$this->startDate) {
            $this->startDate = $this->_parse_time('1 month ago');
        }
        if (!$this->endDate) {
            $this->endDate = $this->_parse_time('yesterday');
        }
        if (!$this->sort_by) {
            $this->sort_by = '-' . $this->metric;
        }


        if ($this->dimension) {
            $dimensions = $this->dimension;
        }
        if ($this->metric) {
            $metric = $this->metric;
        }
        //if ($this->segment) $url .= "&segment=gaid::".$this->segment;
        //else if ($this->dsegment) $url .= "&segment=dynamic::".$this->dsegment;
        $sort = $this->sort_by;
        if ($this->max_results) {
            $max = $this->max_results;
        }
        if ($this->filters) {
            $filter = $this->filters;
        }
     $optParams = array(
                'dimensions'  => $dimensions,
                'filters'     => $filter,
                'max-results' => $max,
        );

        if($this->outputType){
                $optParams['output'] = $this->outputType;
        }


        return $this->analytics->data_ga->get('ga:' . $this->profileId, $this->startDate, $this->endDate, $metric, $optParams);
        
    }


    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }


    /**
     * _ga_prefix function.
     * Just to add prefix where ever needed
     *
     * @access private
     */
    function _ga_prefix($string)
    {
        if ($string[2] != ':') {
            return 'ga:' . $string;
        }

        return $string;
    }


    /**
     * filter function.
     *
     * @access   public
     *
     * @param       $dim_or_met
     * @param mixed $filter_comparison
     * @param mixed $filter_value
     *
     * @return $this
     * @internal param mixed $dimension_or_metric
     */
    function filter($dim_or_met, $filter_comparison, $filter_value)
    {
        $this->filters = $this->_ga_prefix($dim_or_met) . urlencode($filter_comparison . $filter_value);

        return $this;
    }
    

    /**
     * and_filter function.
     *
     * @access   public
     *
     * @param       $dim_or_met
     * @param mixed $filter_comparison
     * @param mixed $filter_value
     *
     * @return $this
     * @internal param mixed $dimension_or_metric
     */
    function and_filter($dim_or_met, $filter_comparison, $filter_value)
    {
        $this->filters .= ';' . $this->_ga_prefix($dim_or_met) . urlencode($filter_comparison . $filter_value);

        return $this;
    }


    /**
     * or_filter function.
     *
     * @access   public
     *
     * @param       $dim_or_met
     * @param mixed $filter_comparison
     * @param mixed $filter_value
     *
     * @return $this
     * @internal param mixed $dimension_or_metric
     */
    function or_filter($dim_or_met, $filter_comparison, $filter_value)
    {
        $this->filters .= ',' . $this->_ga_prefix($dim_or_met) . urlencode($filter_comparison . $filter_value);

        return $this;
    }

    // --------------------------------------------------------------------
    /**
     * when function just sets the  default Start date and End Date
     *
     * @access public
     *
     * @param string $startDate . (default: '1 month ago')
     * @param string $end       . (default: 'yesterday')
     *
     * @return $this
     */
    function when($startDate = '2 day ago', $end = 'yesterday')
    {
        $this->startDate = $this->_parse_time($startDate);
        $this->endDate   = $this->_parse_time($end);

        return $this;
    }

    /**
     * sort_by function.
     * sort the way  you wan t
     *
     * @access   public
     *
     * @param      $arr_or_str
     * @param bool $reverse
     *
     * @return $this
     * @internal param mixed $sort
     */
    function sort_by($arr_or_str, $reverse = false)
    {
        $this->sort_by = $this->_values_converter($arr_or_str);

        if (!$reverse) {
            $this->sort_by = '-' . $this->sort_by;
        }

        return $this;
    }

    /**
     * @param $time
     *
     * @return bool|string
     */
    function _parse_time($time)
    {
        if (!is_numeric($time)) //on suppose que le format est compatible strtotime
        {
            if ($time === 'today') {
                return date('Y-m-d');
            } else {
                return date('Y-m-d', strtotime($time));
            }
        } else {
            return date('Y-m-d', $time);
        }
    }


    /**
     * @param $array
     *
     * @return \stdClass
     */
    function _array_to_object($array)
    {
        $object = new stdClass();
        foreach ($array as $key => $value) {
            $object->$key = is_array($value) ? $this->_array_to_object($value) : $value;
        }

        return $object;
    }





    // --------------------------------------------------------------------
    /**
     * offset function.
     *
     * @access public
     *
     * @param int $index . (default: 10)
     *
     * @return $this
     */
    function offset($index = 10)
    {
        $this->startDate_index = $index;

        return $this;
    }





    // --------------------------------------------------------------------
    /**
     * segment function.
     *
     * @access public
     *
     * @param mixed $int
     *
     * @return $this
     */
    function segment($int)
    {
        $this->segment = $int;

        return $this;
    }


}