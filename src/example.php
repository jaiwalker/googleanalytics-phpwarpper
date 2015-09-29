<?php

namespace Jaiwalker\Googleanalyticsapi;

/**
 * @author JaiKora <kora.jayaram@gmail.com>
 * @gihub  -  https://github.com/jaiwalker
 */
class example
{
    
    /**
     * example constructor.
     */
    public function __construct()
    {
        $this->Aanalytics = new Analytics();

        $this->analytics->getFirstprofileId();

        // Day and Page View
        //$datanew = $analytics->dimension(array('day'))->metric(array('pageviews'))->limit(200)->get_data();//raw //array

        // Users and Page Views Over time
        // $datanew = $analytics->metric(array('pageviews','sessions'))->limit(200)->get_data();

        //Mobile Traffic
        //$datanew = $analytics->dimension(array('mobileDeviceInfo','source'))->metric(array('pageviews','sessions','sessionDuration'))->limit(200)->get_data();

        // New vs Returning Sessions
        //$datanew = $analytics->dimension(array('userType'))->metric(array('sessions'))->limit(200)->get_data();

        // Time On SIte
        // $datanew = $analytics->metric(array('sessions','sessionDuration'))->limit(200)->get_data();


        //referring Sites
        //$datanew = $analytics->dimension(array('source'))->metric(array('pageviews','sessionDuration','exits'))->filter('medium','==','referral')->sort_by('pageviews')   ->get_data();

        //Session Vs Page View
        //$data = $analytics->metric(array('sessions','pageViews'))->limit(30)->outputAs('dataTable')->raw(); //raw //array

        //var_dump( $datanew ); die();

    }


   // send raw //
    private function getPaginationInfo(&$results)
    {
        $html = "
    <pre>
    Items per page =  .'$results->getItemsPerPage()'.
    Total results  = .'$results->getTotalResults()'.
    Previous Link  = .'$results->getPreviousLink()'.
    Next Link      = .'$results->getNextLink().'
    </pre>";

        print $html;


    }


   // send raw //
    public function printQueryParameters(&$results)
    {
      $query ='';
      $query = $results->getQuery();
       $html ='';
      $html = '<pre>';
         foreach ($query as $paramName => $value) {
          $html .= "$paramName = $value\n";
      }
      $html .= '</pre>';

      print $html;
    }

     // send raw //
    private function printDataTable(&$results)
    {   $table ='';
        if (count($results->getRows()) > 0) {

            $table .= '<table>';

            // Print headers.
            $table .= '<tr>';

            foreach ($results->getColumnHeaders() as $header) {
                $table .= '<th>' . $header->name . '</th>';
            }
            $table .= '</tr>';

            // Print table rows.
            foreach ($results->getRows() as $row) {
                $table .= '<tr>';
                foreach ($row as $cell) {
                    $table .= '<td>' . htmlspecialchars($cell, ENT_NOQUOTES) . '</td>';
                }
                $table .= '</tr>';
            }
            $table .= '</table>';

        } else {
            $table .= '<p>No Results Found.</p>';
        }
        print $table;
    }





    
    
}