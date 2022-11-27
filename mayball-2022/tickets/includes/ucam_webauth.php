<?php

// This PHP module implements an agent for the University of Cambridge
// Web Authentication System
//
// See http://raven.cam.ac.`uk/ for more details
//
// Copyright (c) 2004, 2005, 2008 University of Cambridge
//
// This module is free software; you can redistribute it and/or modify
// it under the terms of the GNU Lesser General Public License as
// published by the Free Software Foundation; either version 2.1 of the
// License, or (at your option) any later version.
//
// The module is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
// Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public
// License along with this toolkit; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
// USA
//
// $Id: ucam_webauth.php,v 1.6 2008-04-29 08:44:32 jw35 Exp $
//
// Version 0.51

class UcamAuthenticationModuleFailure extends Exception
{
}

class UcamAuthenticationAuthenticationFailure extends Exception
{
}

class UcamWebauth
{

    public static function getConfiguredAuth()
    {
        global $config;

        $args = array(
            "hostname" => $_SERVER['HTTP_HOST'],
            "key_dir" => $_SERVER['DOCUMENT_ROOT'] . "/" . $config['relative_path_to_raven_keys'],
            "cookie_key" => $config['cookie_key'],
            "cookie_name" => isset($config['cookie_name']) ? $config['cookie_name'] : "Ucam-WebAuth-Session",
            "cookie_path" => "/",
            "cookie_domain" => ""
        );
        if (isset($config["raven_demo"]) && $config["raven_demo"]) {
            $args["auth_service"] = "http://demo.raven.cam.ac.uk/auth/authenticate.html";
        }
        return new UcamWebauth($args);
    }

    public static function ravenAuthenticate()
    {
        $auth = UcamWebauth::getConfiguredAuth();

        $authComplete = $auth->authenticate();

        if (!$authComplete) {
            throw new UcamAuthenticationModuleFailure();
        }

        if (!$auth->success()) {
            throw new UcamAuthenticationAuthenticationFailure();
        }

        return $auth;
    }

    public $PROTOCOL_VERSION = '3';
    public $SESSION_TICKET_VERSION = '3';
    public $DEFAULT_AUTH_SERVICE = 'https://raven.cam.ac.uk/auth/authenticate.html';
    public $DEFAULT_KEY_DIR = '/etc/httpd/conf/webauth_keys';
    public $DEFAULT_COOKIE_NAME = 'Ucam-WebAuth-Session';
    public $DEFAULT_TIMEOUT_MESSAGE = 'your logon to the site has expired';
    public $DEFAULT_HOSTNAME = null; // must be supplied explicitly
    public $COMPLETE = 1;
    public $TESTSTRING = 'Test';
    public $WLS_LOGOUT = 'Not-authenticated';
    public $REQUIRE_CURRENT_STUDENT = false;

    // array index constants

    public $SESSION_TICKET_VER = 0;
    public $SESSION_TICKET_STATUS = 1;
    public $SESSION_TICKET_MSG = 2;
    public $SESSION_TICKET_ISSUE = 3;
    public $SESSION_TICKET_EXPIRE = 4;
    public $SESSION_TICKET_ID = 5;
    public $SESSION_TICKET_PRINCIPAL = 6;
    public $SESSION_TICKET_AUTH = 7;
    public $SESSION_TICKET_SSO = 8;
    public $SESSION_TICKET_PARAMS = 9;
    public $SESSION_TICKET_SIG = 10;

    public $WLS_TOKEN_VER = 0;
    public $WLS_TOKEN_STATUS = 1;
    public $WLS_TOKEN_MSG = 2;
    public $WLS_TOKEN_ISSUE = 3;
    public $WLS_TOKEN_ID = 4;
    public $WLS_TOKEN_URL = 5;
    public $WLS_TOKEN_PRINCIPAL = 6;
    public $WLS_TOKEN_PTAGS = 7;
    public $WLS_TOKEN_AUTH = 8;
    public $WLS_TOKEN_SSO = 9;
    public $WLS_TOKEN_LIFE = 10;
    public $WLS_TOKEN_PARAMS = 11;
    public $WLS_TOKEN_KEYID = 12;
    public $WLS_TOKEN_SIG = 13;

    public $do_session;
    public $cookie_key;
    public $cookie_path;
    public $session_ticket;
    public $auth_service;
    public $description;
    public $response_timeout;
    public $clock_skew;
    public $hostname;
    public $key_dir;
    public $max_session_life;
    public $timeout_message;
    public $cookie_name;
    public $cookie_domain;
    public $fail;
    //var $status;
    //var $headers;
    public $forced_reauth_message;
    public $interact;

    public $error_message = array(
        '200' => 'OK',
        '410' => 'Authentication cancelled at user\'s request',
        '510' => 'No mutually acceptable types of authentication available',
        '520' => 'Unsupported authentication protocol version',
        '530' => 'Parameter error in authentication request',
        '540' => 'Interaction with the user would be required',
        '550' => 'Web server and authentication server clocks out of sync',
        '560' => 'Web server not authorized to use the authentication service',
        '570' => 'Operation declined by the authentication service'
    );

    public function __construct($args)
    {
        if (isset($args['auth_service'])) {
            $this->auth_service = $args['auth_service'];
        } else {
            $this->auth_service = $this->DEFAULT_AUTH_SERVICE;
        }

        if (isset($args['description'])) {
            $this->description = $args['description'];
        }

        if (isset($args['response_timeout'])) {
            $this->response_timeout = $args['response_timeout'];
        } else {
            $this->response_timeout = 30;
        }

        if (isset($args['clock_skew'])) {
            $this->clock_skew = $args['clock_skew'];
        } else {
            $this->clock_skew = 5;
        }

        if (isset($args['key_dir'])) {
            $this->key_dir = $args['key_dir'];
        } else {
            $this->key_dir = $this->DEFAULT_KEY_DIR;
        }

        if (isset($args['do_session'])) {
            $this->do_session = $args['do_session'];
        } else {
            $this->do_session = true;
        }

        if (isset($args['max_session_life'])) {
            $this->max_session_life = $args['max_session_life'];
        } else {
            $this->max_session_life = 2 * 60 * 60;
        }

        if (isset($args['timeout_message'])) {
            $this->timeout_message = $args['timeout_message'];
        } else {
            $this->timeout_message = $this->DEFAULT_TIMEOUT_MESSAGE;
        }

        if (isset($args['cookie_key'])) {
            $this->cookie_key = $args['cookie_key'];
        }

        if (isset($args['cookie_name'])) {
            $this->cookie_name = $args['cookie_name'];
        } else {
            $this->cookie_name = $this->DEFAULT_COOKIE_NAME;
        }

        if (isset($args['cookie_path'])) {
            $this->cookie_path = $args['cookie_path'];
        } else {
            $this->cookie_path = '';
        }
        // *** SHOULD BE PATH RELATIVE PATH TO SCRIPT BY DEFAULT ***

        if (isset($args['hostname'])) {
            $this->hostname = $args['hostname'];
        } else {
            $this->hostname = $this->DEFAULT_HOSTNAME;
        }

        // COOKIE PATH CAN BE NULL, DEFAULTS TO CURRENT DIRECTORY (TEST)

        if (isset($args['cookie_domain'])) {
            $this->cookie_domain = $args['cookie_domain'];
        } else {
            $this->cookie_domain = '';
        }

        if (isset($args['fail'])) {
            $this->fail = $args['fail'];
        } else {
            $this->fail = '';
        }

        if (isset($args['forced_reauth_message'])) {
            $this->forced_reauth_message = $args['forced_reauth_message'];
        }

        if (isset($args['interact'])) {
            $this->interact = $args['interact'];
        }
    }

    // get/set functions for agent attributes

    public function auth_service($arg = null)
    {
        if (isset($arg)) {
            $this->auth_service = $arg;
        }

        return $this->auth_service;
    }

    public function description($arg = null)
    {
        if (isset($arg)) {
            $this->description = $arg;
        }

        return $this->description;
    }

    public function response_timeout($arg = null)
    {
        if (isset($arg)) {
            $this->response_timeout = $arg;
        }

        return $this->response_timeout;
    }

    public function clock_skew($arg = null)
    {
        if (isset($arg)) {
            $this->clock_skew = $arg;
        }

        return $this->clock_skew;
    }

    public function key_dir($arg = null)
    {
        if (isset($arg)) {
            $this->key_dir = $arg;
        }

        return $this->key_dir;
    }

    public function do_session($arg = null)
    {
        if (isset($arg)) {
            $this->do_session = $arg;
        }

        return $this->do_session;
    }

    public function max_session_life($arg = null)
    {
        if (isset($arg)) {
            $this->max_session_life = $arg;
        }

        return $this->max_session_life;
    }

    public function timeout_message($arg = null)
    {
        if (isset($arg)) {
            $this->timeout_message = $arg;
        }

        return $this->timeout_message;
    }

    public function cookie_key($arg = null)
    {
        if (isset($arg)) {
            $this->cookie_key = $arg;
        }

        return $this->cookie_key;
    }

    public function cookie_name($arg = null)
    {
        if (isset($arg)) {
            $this->cookie_name = $arg;
        }

        return $this->cookie_name;
    }

    public function cookie_path($arg = null)
    {
        if (isset($arg)) {
            $this->cookie_path = $arg;
        }

        return $this->cookie_path;
    }

    public function cookie_domain($arg = null)
    {
        if (isset($arg)) {
            $this->cookie_domain = $arg;
        }

        return $this->cookie_domain;
    }

    public function fail($arg = null)
    {
        if (isset($arg)) {
            $this->fail = $arg;
        }

        return $this->fail;
    }

    public function hostname($arg = null)
    {
        if (isset($arg)) {
            $this->hostname = $arg;
        }

        return $this->hostname;
    }

    public function forced_reauth_message($arg = null)
    {
        if (isset($arg)) {
            $this->forced_reauth_message = $arg;
        }

        return $this->forced_reauth_message;
    }

    public function interact($arg = null)
    {
        if (isset($arg)) {
            $this->interact = $arg;
        }

        return $this->interact;
    }

    // read-only functions to access the authentication state

    public function status()
    {
        if (isset($this->session_ticket[$this->SESSION_TICKET_STATUS])) {
            return $this->session_ticket[$this->SESSION_TICKET_STATUS];
        }

        return null;
    }

    public function success()
    {
        if (isset($this->session_ticket[$this->SESSION_TICKET_STATUS])) {
            return ($this->session_ticket[$this->SESSION_TICKET_STATUS] == '200');
        }

        return null;
    }

    public function msg()
    {
        if (isset($this->session_ticket[$this->SESSION_TICKET_MSG])) {
            return $this->session_ticket[$this->SESSION_TICKET_MSG];
        }

        return null;
    }

    public function issue()
    {
        if (isset($this->session_ticket[$this->SESSION_TICKET_ISSUE])) {
            return $this->session_ticket[$this->SESSION_TICKET_ISSUE];
        }

        return null;
    }

    public function expire()
    {
        if (isset($this->session_ticket[$this->SESSION_TICKET_EXPIRE])) {
            return $this->session_ticket[$this->SESSION_TICKET_EXPIRE];
        }

        return null;
    }

    public function id()
    {
        if (isset($this->session_ticket[$this->SESSION_TICKET_ID])) {
            return $this->session_ticket[$this->SESSION_TICKET_ID];
        }

        return null;
    }

    public function principal()
    {
        if (isset($this->session_ticket[$this->SESSION_TICKET_PRINCIPAL])) {
            return $this->session_ticket[$this->SESSION_TICKET_PRINCIPAL];
        }

        return null;
    }

    public function auth()
    {
        if (isset($this->session_ticket[$this->SESSION_TICKET_AUTH])) {
            return $this->session_ticket[$this->SESSION_TICKET_AUTH];
        }

        return null;
    }

    public function sso()
    {
        if (isset($this->session_ticket[$this->SESSION_TICKET_SSO])) {
            return $this->session_ticket[$this->SESSION_TICKET_SSO];
        }

        return null;
    }

    public function params()
    {
        if (isset($this->session_ticket[$this->SESSION_TICKET_PARAMS])) {
            return $this->session_ticket[$this->SESSION_TICKET_PARAMS];
        }

        return null;
    }

    // authentication functions

    public function using_https()
    {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on");
    }

    public function load_key($key_id)
    {
        $key_filename = $this->key_dir . '/' . $key_id . '.crt';
        if (file_exists($key_filename)) {
            $key_file = fopen($key_filename, 'r');
            $key = fread($key_file, filesize($key_filename));
            fclose($key_file);
            return $key;
        }
        return null;
    }

    public function check_sig($data, $sig, $key_id)
    {

        // **** TODO : remove trailing slash from key_dir

        $key_filename = $this->key_dir . '/' . $key_id . '.crt';
        $key_file = fopen($key_filename, 'r');
        // Band-aid test for the most obvious cause of error - whole
        // thing needs improvement
        if (!$key_file) {
            error_log('Failed to open key file ' . $key_filename, 0);
            return false;
        }
        $key_str = fread($key_file, filesize($key_filename));
        $key = openssl_get_publickey($key_str);
        fclose($key_file);
        $result = openssl_verify(rawurldecode($data), $this->wls_decode(rawurldecode($sig)), $key);
        openssl_free_key($key);
        return $result;
    }

    public function hmac_sha1($key, $data)
    {
        $blocksize = 64;
        if (strlen($key) > $blocksize) {
            $key = pack('H*', sha1($key));
        }

        $key = str_pad($key, $blocksize, chr(0x00));
        $ipad = str_repeat(chr(0x36), $blocksize);
        $opad = str_repeat(chr(0x5c), $blocksize);
        $hmac = pack('H*', sha1(($key ^ $opad) . pack('H*', sha1(($key ^ $ipad) . $data))));
        return $this->wls_encode(bin2hex($hmac));
    }

    public function hmac_sha1_verify($key, $data, $sig)
    {
        return ($sig == $this->hmac_sha1($key, $data));
    }

    public function url()
    {
        $hostname = urlencode($this->hostname);
        $port = $_SERVER['SERVER_PORT'];
        if ($this->using_https()) {
            $protocol = 'https://';
            if ($port == '443') {
                $port = '';
            }
        } else {
            $protocol = 'http://';
            if ($port == '80') {
                $port = '';
            }
        }
        $port = urlencode($port); // should be redundant, of course
        $url = $protocol . $hostname;
        if ($port != '') {
            $url .= ':' . $port;
        }

        /*
        $url .= $_SERVER['SCRIPT_NAME'];
        if (isset($_SERVER['PATH_INFO'])) $url .= $_SERVER['PATH_INFO'];
        if (isset($_SERVER['QUERY_STRING']) and $_SERVER['QUERY_STRING'] != '')
        {
        $url .= '?' . $_SERVER['QUERY_STRING'];
        }
         */
        // jes91: must remove /webroot/ because Cake's mod_rewrite will trip over
        // if an attempt is made to literally access a Cake controller. Easiest
        // way to do this is to just use REQUEST_URI.
        // In any case, can't see why you would not want to use
        // REQUEST_URI in all circumstances, at least on Apache v2.
        $url .= $_SERVER['REQUEST_URI'];
        return $url;
    }

    public function full_cookie_name()
    {
        if ($this->using_https()) {
            return $this->cookie_name . '-S';
        }
        return $this->cookie_name;
    }

    public function time2iso($t)
    {
        //return gmstrftime('%Y%m%d', $t).'T'.gmstrftime('%H%M%S', $t).'Z';
        return gmdate('Ymd\THis\Z', $t);
    }

    public function iso2time($t)
    {
        return gmmktime(
            substr($t, 9, 2),
            substr($t, 11, 2),
            substr($t, 13, 2),
            substr($t, 4, 2),
            substr($t, 6, 2),
            substr($t, 0, 4)
        );
    }

    public function wls_encode($str)
    {
        $result = base64_encode($str);
        $result = preg_replace(array('/\+/', '/\//', '/=/'), array('-', '.', '_'), $result);
        return $result;
    }

    public function wls_decode($str)
    {
        $result = preg_replace(array('/-/', '/\./', '/_/'), array('+', '/', '='), $str);
        $result = base64_decode($result);
        return $result;
    }

    public function authenticate($authassertionid = null, $testauthonly = null)
    {
        // consistency check

        if ($testauthonly and !$this->do_session) {
            $this->session_ticket[$this->SESSION_TICKET_STATUS] = '600';
            $this->session_ticket[$this->SESSION_TICKET_MSG] = 'Requested dummy run but session cookie not managed';
            return true;
        }

        // check that someone defined cookie key and if we are doing session management

        if ($this->do_session and !isset($this->cookie_key)) {
            $this->session_ticket[$this->SESSION_TICKET_STATUS] = '600';
            $this->session_ticket[$this->SESSION_TICKET_MSG] = 'No key defined for session cookie';
            return true;
        }

        // log a warning if being used to authenticate POST requests

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // error_log('Ucam_Webauth PHP Agent invoked for POST request, which it does\'nt really support', 0);
        }

        // Check that the hostname is set explicitly (since we cannot trust
        // the Host: header); if it returns false (i.e. not set).

        if (!isset($this->hostname) or $this->hostname == '') {
            $this->session_ticket[$this->SESSION_TICKET_MSG] =
                'Ucam_Webauth configuration error - mandatory hostname not defined';
            $this->session_ticket[$this->SESSION_TICKET_STATUS] = '600';
            error_log('hostname not set in Ucam_Webauth object, but is mandatory');
            return true;
        }

        /* FIRST: if we are doing session management then see if we already
         * have authentication data stored in a cookie. Note that if the
         * stored status isn't 200 (OK) then we destroy the cookie so that if
         * we come back through here again we will fall through and repeat
         * the authentication. We do this first so that replaying an old
         * authentication response won't trigger a 'stale authentication' error
         */

        // error_log('FIRST STAGE', 0);

        $current_timeout_message = null;

        if ($this->do_session) {

            // error_log('session management ON', 0);

            if (
                isset($_COOKIE[$this->full_cookie_name()]) and
                $_COOKIE[$this->full_cookie_name()] != $this->TESTSTRING and
                $_COOKIE[$this->full_cookie_name()] != $this->WLS_LOGOUT
            ) {

                // error_log('existing authentication cookie found', 0);
                // error_log('cookie: ' . rawurldecode($_COOKIE[$this->full_cookie_name()]));

                //$old_cookie = explode(' ', rawurldecode($_COOKIE[$this->full_cookie_name()]));
                //$this->session_ticket = explode('!', $old__cookie[0]);
                $this->session_ticket = explode('!', rawurldecode($_COOKIE[$this->full_cookie_name()]));

                $values_for_verify = $this->session_ticket;
                $sig = array_pop($values_for_verify);

                if ($this->hmac_sha1_verify(
                    $this->cookie_key,
                    implode('!', $values_for_verify),
                    $sig
                )) {

                    // error_log('existing authentication cookie verified', 0);

                    $issue = $this->iso2time($this->session_ticket[$this->SESSION_TICKET_ISSUE]);
                    $expire = $this->iso2time($this->session_ticket[$this->SESSION_TICKET_EXPIRE]);
                    $now = time();

                    if ($issue <= $now and $now < $expire) {
                        if (!isset($authassertionid) or $authassertionid != $this->session_ticket[$this->SESSION_TICKET_ID]) {
                            if ($this->session_ticket[$this->SESSION_TICKET_STATUS] != '200') {
                                if (!$testauthonly) {
                                    setcookie(
                                        $this->full_cookie_name(),
                                        '',
                                        1,
                                        $this->cookie_path,
                                        $this->cookie_domain,
                                        $this->using_https()
                                    );
                                }
                            }
                            // error_log('AUTHENTICATION COMPLETE', 0);
                            return true;
                        } else {
                            $current_timeout_message = $this->forced_reauth_message;
                            error_log('detection of \'stale\' authassertionid requested and found: ' . $authassertionid, 0);
                            error_log('authentication using current session ticket denied', 0);
                        }
                    } else {
                        $current_timeout_message = $this->timeout_message;
                        error_log('local session cookie expired', 0);
                        error_log('issue/now/expire: ' . $issue . '/' . $now . '/' . $expire);
                    }
                } else {
                    error_log('AUTHENTICATION FAILED, session cookie sig invalid', 0);
                    $this->session_ticket[$this->SESSION_TICKET_MSG] = 'Session cookie signature invalid';
                    $this->session_ticket[$this->SESSION_TICKET_STATUS] = '600';
                    return true;
                }
            }
        }

        /* SECOND: Look to see if we are being invoked as the callback from
         * this WLS. If so, validate the response. If we are not doing session
         * management then we can then just return. If we are doing session
         * management, check that the session cookie already exists with a
         * test value (becuause otherwise we probably don't have cookies
         * enabled), set it, and redirect back to the original URL to clear
         * the browser's location bar of the WLS response.
         */

        // error_log('SECOND STAGE');

        $token = null;

        if (
            isset($_SERVER['QUERY_STRING']) and
            preg_match('/^WLS-Response=/', $_SERVER['QUERY_STRING'])
        ) {

            // error_log('WLS response: ' . $_SERVER['QUERY_STRING'], 0);

            // does this change QUERY_STRING??:
            $token = explode('!', preg_replace('/^WLS-Response=/', '', rawurldecode($_SERVER['QUERY_STRING'])));

            $this->session_ticket[$this->SESSION_TICKET_STATUS] = '200';
            $this->session_ticket[$this->SESSION_TICKET_MSG] = '';
            /*
            $key = $this->load_key($token[$this->WLS_TOKEN_KEYID]);

            if ($key == NULL) {
            $this->session_ticket[$this->SESSION_TICKET_MSG] = 'Failed to load '.$this->key_dir . '/' . $token[$this->WLS_TOKEN_KEYID] . '.crt';
            $this->session_ticket[$this->SESSION_TICKET_STATUS] = '600';
            return $this->COMPLETE;
            }
             */
            //echo '<p>' . implode('!', $token) . '</p>';
            $sig = array_pop($token);
            $key_id = array_pop($token);

            if ($token[$this->WLS_TOKEN_VER] != $this->PROTOCOL_VERSION) {
                $this->session_ticket[$this->SESSION_TICKET_MSG] = 'Wrong protocol version in authentication service reply';
                $this->session_ticket[$this->SESSION_TICKET_STATUS] = '600';
            } else if ($token[$this->WLS_TOKEN_STATUS] != '200') {
                $this->session_ticket[$this->SESSION_TICKET_MSG] = $this->error_message[$token[$this->WLS_TOKEN_STATUS]];
                if (isset($token[$this->WLS_TOKEN_MSG])) {
                    $this->session_ticket[$this->SESSION_TICKET_MSG] .= $token[$this->WLS_TOKEN_MSG];
                }

                $this->session_ticket[$this->SESSION_TICKET_STATUS] = $token[$this->WLS_TOKEN_STATUS];
            } else if ($this->check_sig(implode('!', $token), $sig, $key_id) != 1) {
                $this->session_ticket[$this->SESSION_TICKET_MSG] = 'Invalid WLS token signature';
                $this->session_ticket[$this->SESSION_TICKET_STATUS] = '600';
                return true;
            } else if ($this->REQUIRE_CURRENT_STUDENT and strpos($token[$this->WLS_TOKEN_PTAGS], 'current') === false) {
                $this->session_ticket[$this->SESSION_TICKET_MSG] = 'User must be a current member of the university';
                $this->session_ticket[$this->SESSION_TICKET_STATUS] = '620';
                return true;
            } else {
                $now = time();
                $issue = $this->iso2time($token[$this->WLS_TOKEN_ISSUE]);

                if (!isset($issue)) {
                    $this->session_ticket[$this->SESSION_TICKET_MSG] = 'Unable to read issue time in authentication service reply';
                    $this->session_ticket[$this->SESSION_TICKET_STATUS] = '600';
                } else if ($issue > $now + $this->clock_skew + 1) {
                    $this->session_ticket[$this->SESSION_TICKET_MSG] = 'Authentication service reply aparently issued in the future: ' . $token[$this->WLS_TOKEN_ISSUE];
                    $this->session_ticket[$this->SESSION_TICKET_STATUS] = '600';
                } else if ($now - $this->clock_skew - 1 > $issue + $this->response_timeout) {
                    $this->session_ticket[$this->SESSION_TICKET_MSG] = 'Stale authentication service reply issue at ' . $token[$this->WLS_TOKEN_ISSUE];
                    $this->session_ticket[$this->SESSION_TICKET_STATUS] = '600';
                }

                $response_url = $token[$this->WLS_TOKEN_URL];
                $response_url = preg_replace('/\?.*$/', '', $response_url);
                $this_url = preg_replace('/\?.+$/', '', $this->url());

                if ($this_url != $response_url) {
                    $this->session_ticket[$this->SESSION_TICKET_MSG] = 'URL in response ticket doesn\'t match this URL: ' . $response_url . ' != ' . $this_url;
                    $this->session_ticket[$this->SESSION_TICKET_STATUS] = '600';
                }
            }

            // work out session expiry time

            $expiry = $this->max_session_life;
            if (isset($token[$this->WLS_TOKEN_LIFE]) and $token[$this->WLS_TOKEN_LIFE] > 0 and $token[$this->WLS_TOKEN_LIFE] < $expiry) {
                $expiry = $token[$this->WLS_TOKEN_LIFE];
            }

            // populate session ticket with information collected so far
            $this->session_ticket[$this->SESSION_TICKET_ISSUE] = $this->time2iso(time());
            $this->session_ticket[$this->SESSION_TICKET_EXPIRE] = $this->time2iso(time() + $expiry);
            $this->session_ticket[$this->SESSION_TICKET_ID] = $token[$this->WLS_TOKEN_ID];
            $this->session_ticket[$this->SESSION_TICKET_PRINCIPAL] = $token[$this->WLS_TOKEN_PRINCIPAL];
            $this->session_ticket[$this->SESSION_TICKET_AUTH] = $token[$this->WLS_TOKEN_AUTH];
            $this->session_ticket[$this->SESSION_TICKET_SSO] = $token[$this->WLS_TOKEN_SSO];
            $this->session_ticket[$this->SESSION_TICKET_PARAMS] = $token[$this->WLS_TOKEN_PARAMS];

            // return complete if we are not doing session management

            if (!$this->do_session) {
                return true;
            }

            // otherwise establish a session cookie to maintain the
            // session ticket. First check that the cookie actually exists
            // with a test value, because it should have been created
            // previously and if its not there we'll probably end up in
            // a redirect loop.

            if (
                !isset($_COOKIE[$this->full_cookie_name()]) or
                $_COOKIE[$this->full_cookie_name()] != $this->TESTSTRING
            ) {
                $this->session_ticket[$this->SESSION_TICKET_STATUS] = '610';
                $this->session_ticket[$this->SESSION_TICKET_MSG] = 'Browser is not accepting session cookie';
                return true;
            }

            $this->session_ticket[$this->SESSION_TICKET_VER] = $this->SESSION_TICKET_VERSION;

            // This used to use ksort and implode, but appeared to produce
            // broken results from time to time. This does the work the hard
            // way.

            $cookie = '';
            for ($i = 0; $i < $this->SESSION_TICKET_PARAMS; $i++) {
                if (isset($this->session_ticket[$i])) {
                    $cookie .= $this->session_ticket[$i];
                }

                $cookie .= '!';
            }
            if (isset($this->session_ticket[$this->SESSION_TICKET_PARAMS])) {
                $cookie .= $this->session_ticket[$this->SESSION_TICKET_PARAMS];
            }

            $sig = $this->hmac_sha1($this->cookie_key, $cookie);
            $cookie .= '!' . $sig;
            // error_log('cookie: ' . $cookie);

            // End

            if (!$testauthonly) {
                setcookie(
                    $this->full_cookie_name(),
                    $cookie,
                    0,
                    $this->cookie_path,
                    $this->cookie_domain,
                    $this->using_https()
                );
            }

            // error_log('session cookie established, redirecting...', 0);

            // Clean up the URL in browser location bar, i.e., remove WLS stuff
            // in query string, and, inevitably, redo original request a second time?
            if (!$testauthonly) {
                header('Location: ' . $token[$this->WLS_TOKEN_URL]);
            }

            return false;
        }

        /* THIRD: send a request to the WLS. If we are doing session
         * management then set a test value cookie so we can test that
         * it's still available when we get back.
         */

        // error_log('THIRD STAGE', 0);

        if ($this->do_session) {
            // If the hostname from the request (Host: header) does not match the
            // server's preferred name for itself (which should be what's configured
            // as hostname), cookies are likely to break "randomly" (or more
            // accurately, the cookie may not be sent by the browser since it's for
            // a different hostname) as a result of following links that use the
            // preferred name, or server-level redirects e.g. to fix "directory"
            // URLs lacking the trailing "/". Attempt to avoid that by redirecting
            // to an equivalent URL using the configured hostname.

            if (isset($_SERVER['HTTP_HOST']) and (strtolower($this->hostname) !=
                strtolower($_SERVER['HTTP_HOST']))) {
                error_log('Redirect to tidy up hostname mismatch', 0);
                if (!$testauthonly) {
                    header('Location: ' . $this->url());
                }

                return false;
            }

            // error_log('setting pre-session cookie', 0);
            if (!$testauthonly) {
                setcookie(
                    $this->full_cookie_name(),
                    $this->TESTSTRING,
                    0,
                    $this->cookie_path,
                    $this->cookie_domain,
                    $this->using_https()
                );
            }
        }

        $dest = 'Location: ' . $this->auth_service .
            '?ver=' . $this->PROTOCOL_VERSION .
            '&url=' . rawurlencode($this->url());
        if (isset($this->description)) {
            $dest .= '&desc=' . rawurlencode($this->description);
        }

        //if (isset($this->aauth)) $dest .= '&aauth=' . rawurlencode($this->aauth);
        if (isset($this->interact)) {
            $dest .= '&iact=' . ($this->interact == true ? 'yes' : 'no');
        }

        if (isset($this->params)) {
            $dest .= '&params' . rawurlencode($this->params);
        }

        if (isset($current_timeout_message)) {
            $dest .= '&msg=' . rawurlencode($current_timeout_message);
        }

        if (isset($this->clock_skew)) {
            $dest .= '&date=' . rawurlencode($this->time2iso(time())) .
                '&skew=' . rawurlencode($this->clock_skew);
        }

        if ($this->fail == true) {
            $dest .= '&fail=yes';
        }

        if (!$testauthonly) {
            error_log($dest);
            header($dest);
        }

        return false;
    }
}
