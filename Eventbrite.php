<?php
/*
	The MIT License

	Permission is hereby granted, free of charge, to any person obtaining a copy
	of this software and associated documentation files (the "Software"), to deal
	in the Software without restriction, including without limitation the rights
	to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	copies of the Software, and to permit persons to whom the Software is
	furnished to do so, subject to the following conditions:

	The above copyright notice and this permission notice shall be included in
	all copies or substantial portions of the Software.

	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
	THE SOFTWARE.

    The latest version of this API client is available here:
    https://github.com/ryanjarvinen/eventbrite.php

    Author: @ryanjarvinen

	With contributions by Stas Sușcov
*/

class Eventbrite {
	/**
	 * Default Eventbrite API URL
	 */
    var $api_endpoint = "https://www.eventbrite.com/json/";
	
	/**
	 * Eventbrite API key (REQUIRED)
     *    http://www.eventbrite.com/api/key/
	 * Eventbrite user_key (OPTIONAL, only needed for reading/writing private user data)
     *     http://www.eventbrite.com/userkeyapi
     *
     * Alternate athentication parameters:
	 *   Eventbrite user email
	 *   Eventbrite user password
	 */
	
	/**
	 * Constructor to initialize the object
	 *
	 * @param String $api_key, your Eventbrite application key
	 * @param String $user, your Eventbrite user_key
	 *
	 * OR
     *
	 * @param String $api_key, your Eventbrite application key
	 * @param String $user, the Eventbrite user's email address
	 * @param String $password, the Eventbrite user's password
	 */
	function Eventbrite( $app_key = null, $user = null, $password = null ) {
	    $this->api_url = parse_url($this->api_endpoint);
		$this->app_key = $app_key;
        if( $password ){
          $this->username = $user;
          $this->password = $password;
        }
        else {
		  $this->user_key = $user;
        }
	}

	/**
	 * Dynamic methods handler
	 */
	function __call( $method, $args ) {
		
		// Build query
		$query_data = array();
		
		// Add auth to querystring
		if( isset($this->app_key ))
		    $query_data['app_key'] = $this->app_key;
		if( isset($this->user_key ))
			$query_data['user_key'] = $this->user_key;
		elseif( isset( $this->username ) && isset( $this->password )) {
			$query_data['user'] = $this->username;
			$query_data['password'] = $this->password;
		}

		// Unpack our arguments
		if( is_array( $args ) && array_key_exists( 0, $args ) && is_array( $args[0]) )
			$query_data = array_merge( $query_data, $args[0]);
		
		// Build the http query url
		$query_url = $this->api_url;
		$query_url['path'] .= $method . '?';
		$http_query = $query_url['scheme'] . '://';
		$http_query .= $query_url['host'] . $query_url['path'] ;
		$http_query .= http_build_query( $query_data, '', '&' );
		
		// Call the API
		$response = file_get_contents( $http_query );
		
        // parse our response
		if( $response ){
			$response = json_decode( $response );
		
    		if( isset( $response->error ) && isset($response->error->error_message) ){
	    		throw new Exception( $response->error->error_message );
            }
        }
		return $response;
	}

    /*
     * Widgets:
     */
    public static function ticketWidget( $evnt ) {
        return '<div style="width:100%; text-align:left;" ><iframe src="http://www.eventbrite.com/tickets-external?eid=' . $evnt->id . '&ref=etckt" frameborder="0" height="192" width="100%" vspace="0" hspace="0" marginheight="5" marginwidth="5" scrolling="auto" allowtransparency="true"></iframe><div style="font-family:Helvetica, Arial; font-size:10px; padding:5px 0 5px; margin:2px; width:100%; text-align:left;" ><a style="color:#ddd; text-decoration:none;" target="_blank" href="http://www.eventbrite.com/r/etckt" >Online Ticketing</a><span style="color:#ddd;" > for </span><a style="color:#ddd; text-decoration:none;" target="_blank" href="http://www.eventbrite.com/event/' . $evnt->id . '?ref=etckt" >' . addslashes($evnt->title) . '</a><span style="color:#ddd;" > powered by </span><a style="color:#ddd; text-decoration:none;" target="_blank" href="http://www.eventbrite.com?ref=etckt" >Eventbrite</a></div></div>';
    }

    public static function registrationWidget( $evnt ) {
        return '<div style="width:100%; text-align:left;" ><iframe src="http://www.eventbrite.com/event/' . $evnt->id . '?ref=eweb" frameborder="0" height="1000" width="100%" vspace="0" hspace="0" marginheight="5" marginwidth="5" scrolling="auto" allowtransparency="true"></iframe><div style="font-family:Helvetica, Arial; font-size:10px; padding:5px 0 5px; margin:2px; width:100%; text-align:left;" ><a style="color:#ddd; text-decoration:none;" target="_blank" href="http://www.eventbrite.com/r/eweb" >Online Ticketing</a><span style="color:#ddd;" > for </span><a style="color:#ddd; text-decoration:none;" target="_blank" href="http://www.eventbrite.com/event/' . $evnt->id . '?ref=eweb" >' . addslashes($evnt->title) . '</a><span style="color:#ddd;" > powered by </span><a style="color:#ddd; text-decoration:none;" target="_blank" href="http://www.eventbrite.com?ref=eweb" >Eventbrite</a></div></div>';

    }

    public static function calendarWidget( $evnt ) {
        return '<div style="width:195px; text-align:center;" ><iframe src="http://www.eventbrite.com/calendar-widget?eid=' . $evnt->id . '" frameborder="0" height="382" width="195" marginheight="0" marginwidth="0" scrolling="no" allowtransparency="true"></iframe><div style="font-family:Helvetica, Arial; font-size:10px; padding:5px 0 5px; margin:2px; width:195px; text-align:center;" ><a style="color:#ddd; text-decoration:none;" target="_blank" href="http://www.eventbrite.com/r/ecal">Online event registration</a><span style="color:#ddd;" > powered by </span><a style="color:#ddd; text-decoration:none;" target="_blank" href="http://www.eventbrite.com?ref=ecal" >Eventbrite</a></div></div>';
    }

    public static function countdownWidget( $evnt ) {
        return '<div style="width:195px; text-align:center;" ><iframe src="http://www.eventbrite.com/countdown-widget?eid=' . $evnt->id . '" frameborder="0" height="479" width="195" marginheight="0" marginwidth="0" scrolling="no" allowtransparency="true"></iframe><div style="font-family:Helvetica, Arial; font-size:10px; padding:5px 0 5px; margin:2px; width:195px; text-align:center;" ><a style="color:#ddd; text-decoration:none;" target="_blank" href="http://www.eventbrite.com/r/ecount" >Online event registration</a><span style="color:#ddd;" > for </span><a style="color:#ddd; text-decoration:none;" target="_blank" href="http://www.eventbrite.com/event/' . $evnt->id . '?ref=ecount" >' . addslashes($evnt->title) . '</a></div></div>'; 
    }

    public static function buttonWidget( $evnt ) {
        return '<a href="http://www.eventbrite.com/event/' . $evnt->id . '?ref=ebtn" target="_blank"><img border="0" src="http://www.eventbrite.com/registerbutton?eid=' . $evnt->id . '" alt="Register for ' . addslashes($evnt->title) . ' on Eventbrite" /></a>';
    }

    public static function linkWidget( $evnt, $text=null, $color=null ) {
        return '<a href="http://www.eventbrite.com/event/' . $evnt->id . '?ref=elink" target="_blank" style="color:' . ( $color ? $color : "#000000" ) . ';">' . ( $text ? $text : addslashes($evnt->title ) ) . '</a>';
    }
};
?>
