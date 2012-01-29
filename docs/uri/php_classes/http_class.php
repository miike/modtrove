<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
 /* @(#) $Header: /sources/phpprintipp/phpprintipp/php_classes/http_class.php,v 1.1 2008/06/21 00:30:58 harding Exp $
 *
 *
 * Class http_class - Basic http client with "Basic" authorization mechanism.
 * handle ipv6 addresses and https
 *
 *   Copyright (C) 2006  Thomas HARDING
 *
 *   This library is free software; you can redistribute it and/or
 *   modify it under the terms of the GNU Library General Public
 *   License as published by the Free Software Foundation; either
 *   version 2 of the License, or (at your option) any later version.
 *
 *   This library is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 *   Library General Public License for more details.
 *
 *   You should have received a copy of the GNU Library General Public
 *   License along with this library; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 *   mailto:thomas.harding@laposte.net
 *   Thomas Harding, 56 rue de la bourie rouge, 45 000 ORLEANS -- FRANCE
 *   
 */
    
/*

    This class is intended to implement a subset of Hyper Text Transfer Protocol (HTTP/1.1) on client side.
    (currently: POST operation)
    It is a replacement for http://www.phpclasses.org/browse/package/3.html
    in versions post-
    
    It can perform Basic and Digest authentication.
    It was tested only in clear mode
    
    References needed to debug / add functionnalities:
        - RFC 2616
        - RFC 2617
*/
/*
    TODO: beta tests on servers other than loopback one
*/

/***********************
*
* httpException class
*
************************/

    // {{{ class httpException
class httpException extends Exception {
    
    protected $errno;

    public function __construct($msg,$errno=null) {
        parent :: __construct($msg);
        $this->errno = $errno;
    }

    public function getErrorFormatted() {
    
        $return = sprintf("[http_class]: %s -- "._(" file %s, line %s"),
                $this->getMessage(),
                $this->getFile(),
                $this->getLine());

    return $return;
    }

    public function getErrno() {
    
    return $this->errno ;
    }

}
    // }}}

/*************************
*
* class http_class
*
**************************/

class http_class {

    // {{{ variables declaration
    public $debug;
    public $html_debug;
    public $timeout = 30; // time waiting for connection, seconds
    public $data_timeout = 30; // time waiting for data, milliseconds
    public $force_multipart_form_post;
    public $username;
    public $password;
    public $request_headers = array();
    public $request_body = "Not a useful information";
    public $status;
    public $window_size = 10000; // chunk size of data
    public $with_exceptions = 0; // compatibility mode for old scripts
    public $port;
    public $host;

    private $default_port = 631;
    private $headers;
    private $reply_headers = array();
    private $reply_body = array();
    private $connection;
    private $arguments;
    private $bodystream = array();
    private $last_limit;
    private $connected;
    private $nc = 1;
    private $user_agent = "PRINTIPP/0.81";
    // }}}
            
    // {{{ constructor
    public function __construct() {
        true;
    }
    // }}}

/*********************
*
* Public functions
*
**********************/

    // {{{ GetRequestArguments ($url,&$arguments)
    public function GetRequestArguments ($url,&$arguments) {
        
        $this->arguments = array();
        
        $arguments["URL"] = $this->arguments["URL"] = $url;
        $arguments["RequestMethod"] = $this->arguments["RequestMethod"] = "POST";
        $this->headers["Content-Length"] = 0;
        $this->headers["Content-Type"] = "application/octet-stream";
        $this->headers["Host"] = $this->host;
        $this->headers["User-Agent"] = $this->user_agent;
        //$this->headers["Expect"] = "100-continue"; 
    }
    // }}}

    // {{{ Open ($arguments)
    public function Open ($arguments) {
        $this->connected = false;
        if (!$this->timeout)
            $this->timeout = 30;
        $url = $arguments["URL"];
        $port = $this->default_port;
        
        $url = split (':',$url,2);
        $transport_type = $url[0];
        $unix = false;
        switch($transport_type) {
                case 'http':
                    $transport_type = 'tcp://';
                    break;
                case 'https':
                    $transport_type = 'tls://';
                    break;
                case 'unix':
                    $transport_type = 'unix://';
                    $port = 0;
                    $unix = true;
                    break;
                default:
                    $transport_type = 'tcp://';
                    break;
            }
        $url = $url[1];
        if (!$unix) {
            $url = split("/",preg_replace("#^/{1,}#",'',$url),2);
            $url = $url[0];
            $port = $this->port;
            $error = sprintf(_("Cannot resolve url: %s"),$url);
            $ip = gethostbyname($url);
            $ip = @gethostbyaddr($ip);
            if (!$ip)
                if ($this->with_exceptions)
                    throw new httpException($error);
                else
                {
                    trigger_error($error,E_USER_WARNING);
                    return false;
                }
            if (strstr($url,":")) // we got an ipv6 address
                if (!strstr($url,"[")) // it is not escaped
                    $url = sprintf("[%s]",$url);
        }
        $this->connection = @fsockopen($transport_type.$url, $port, $errno, $errstr, $this->timeout);
        $error = sprintf (_('Unable to connect to "%s%s port %s": %s'),
            $transport_type,
            $url,
            $port,
            $errstr);

        if (!$this->connection)
            if ($this->with_exceptions)
            {
                $this->connected = false;
                throw new httpException($error,$errno);
            }
            else
            {
                trigger_error($error,E_USER_WARNING);
                $this->connected = false;
                return $error;
            }
        $this->connected = true;
    }
    // }}}

    // {{{ SendRequest($arguments)
    public function SendRequest($arguments) {
         
         if (!$this->data_timeout)
            $this->data_timeout = 30;
            
         if(!$result = self::_StreamRequest($arguments))
            return("SendRequest: unknown error");
         
         self::_ReadReply();

         if (!preg_match('#http/1.1 401 unauthorized#',$this->status))
            return;

         
         $headers = array_keys ($this->reply_headers);
         if (!in_array("www-authenticate",$headers))
            return("SendRequest: need authentication but no mechanism provided");

         $authtype = split(' ',$this->reply_headers["www-authenticate"]);
         $authtype = strtolower($authtype[0]);

         switch ($authtype) {
            case 'basic':
                $pass = base64_encode($this->user.":".$this->password);
                $arguments["Headers"]["Authorization"] = "Basic ".$pass;
                break;
            case 'digest':
                $arguments["Headers"]["Authorization"] = self::_BuildDigest();
                break;
            default:
                return sprintf(_("http_class: need '%s' authentication mechanism, but have not, sorry"),$authtype[0]);
         }
        
         self::Close();
         self::Open($arguments);
         
         if(!$result = self::_StreamRequest($arguments))
            return("SendRequest: unknown error");

          self::_ReadReply();
    
    }
    // }}}

    // {{{ ReadReplyHeaders (&$headers)
    public function ReadReplyHeaders (&$headers) {
        if ($this->connected)
            $headers = $this->reply_headers;
    }
    // }}}

    // {{{ ReadReplyBody (&$body,$chunk_size)
    public function ReadReplyBody (&$body,$chunk_size) {
        $body = substr($this->reply_body,$this->last_limit,$chunk_size);
        $this->last_limit += $chunk_size;
    }
    // }}}

    // {{{ Close ()
    public function Close () {
        if (!$this->connected) return;
        fclose ($this->connection);
    }
    // }}}

/*********************
*
*  Private functions
*
**********************/

    // {{{ _StreamRequest ($arguments)
    private function _StreamRequest ($arguments) {
        if (!$this->connected) return;
        
        $this->status = "";
        $this->reply_headers = array();
        $this->reply_body = "";
        
        $this->arguments = $arguments;
        
        $content_length = 0;
        foreach ($this->arguments["BodyStream"] as $argument) {
            
            list($type,$value) = each($argument);
            reset ($argument);
            
            if ($type == "Data")
                $length = strlen($value);
                
            elseif ($type == "File")
                if (is_readable($value))
                    $length = filesize($value);
                else {
                    $length = 0;
                    trigger_error(sprintf(_("%s: file is not readable"),$value),E_USER_WARNING);
                    }
            
            else {
                $length = 0;
                trigger_error(sprintf(_("%s: not a valid argument for content"),$type),E_USER_WARNING);
                }
                
            $content_length += $length;
            }
        
        $this->request_body = sprintf(_("%s Bytes"), $content_length);
        $this->headers["Content-Length"] =  $content_length;
        $this->arguments["Headers"] = array_merge($this->headers,$this->arguments["Headers"]);
        
        //$read = array($this->connection);
        $status = stream_get_meta_data($this->connection);
        if (isset($status['unread-bytes']) && $status['unread-bytes']) {
            // server talks!
            trigger_error(_("http_class: server talk first, quit"),E_USER_WARNING);
            return false;
            } 

        if ($this->arguments["RequestMethod"] != "POST") {
            trigger_error (sprintf(_("%s: method not implemented"),$arguments["RequestMethod"]),E_USER_WARNING);
            return sprintf(_("%s: method not implemented"),$arguments["RequestMethod"]);
            }
    
        $string = sprintf("POST %s HTTP/1.1\r\n",$this->arguments["RequestURI"]);
        $error = fwrite($this->connection,$string);
        $this->request_headers[$string] = '';
        
        //if(stream_select($read, $write = NULL, $except = NULL, 0) === 1) // server talks!
        $status = stream_get_meta_data($this->connection);
        if (isset($status['unread-bytes']) && $status['unread-bytes'])
             return "server-talk";
        if (!$error) {
            trigger_error(_("Error while puts first header"),E_USER_WARNING);
            return _("Stream closed while puts first header");
            }

        foreach ($this->arguments["Headers"] as $header => $value) {
            
            $error = @fwrite($this->connection,sprintf("%s: %s\r\n", $header, $value));
            $this->request_headers[$header] = $value;
            //if(stream_select($read, $write = NULL, $except = NULL, 0) === 1)
            $status = stream_get_meta_data($this->connection);
            if (isset($status['unread-bytes']) && $status['unread-bytes'])
                return "server-talk";
                
            if (!$error) {
                trigger_error(_("Error while puts HTTP headers"),E_USER_WARNING);
                return _("Stream closed while puts HTTP headers");
                }
            }
        
            $error = fwrite($this->connection,"\r\n");
            //fflush($this->connection);
            
            //if($strselect = stream_select($read, $write = NULL, $except = NULL, 0,$this->data_timeout*1000) === 1) 
            //usleep($this->data_timeout*1000);
            $status = stream_get_meta_data($this->connection);
            if (isset($status['unread-bytes']) && $status['unread-bytes'])
                return "server-talk";

            if (!$error) {
                trigger_error(_("Error while ends HTTP headers"),E_USER_WARNING);
                return _("Stream closed while ends HTTP headers");
                }
         
         foreach ($this->arguments["BodyStream"] as $argument) {
            
            list($type,$value) = each($argument);
            reset ($argument);
            
            
            if ($type == "Data") {
                $streamed_length = 0;
                while ($streamed_length < strlen($value)) {
                    //if(stream_select($read, $write = NULL, $except = NULL, 0) === 1)
                    //usleep($this->data_timeout*1000);
                    $status = stream_get_meta_data($this->connection);
                    if (isset($status['unread-bytes']) && $status['unread-bytes'])
                        return "server-talk";
                
                    // not very clean...
                    $error = @fwrite($this->connection,substr($value,$streamed_length,$this->window_size));
                    if (!$error)
                        return "error-while-push-data";
                    $streamed_length += $this->window_size;
                    }
                }

                if (!$error) 
                    return _("error-while-push-data");
             
            elseif ($type == "File") {
                if (is_readable($value)) {
                    $file = fopen($value,'rb');
                    while(!feof($file)) {
                    
                        if(gettype($block = @fread($file,$this->window_size)) != "string") {
                            trigger_error(_("cannot read file to upload"),E_USER_WARNING);
                            return _("cannot read file to upload");
                            }

                        //if(stream_select($read, $write = NULL, $except = NULL, 0) === 1)
                        $status = stream_get_meta_data($this->connection);
                        if (isset($status['unread-bytes']) && $status['unread-bytes'])
                            return "server-talks";
                
                        // not very clean...
                        $error = @fwrite($this->connection,$block);
                        if (!$error) 
                            return "error-while-push-data";
                            
                        }
                    }
                }
                
                //if(stream_select($read, $write = NULL, $except = NULL, 0) === 1)
                 $status = stream_get_meta_data($this->connection);
                 if (isset($status['unread-bytes']) && $status['unread-bytes'])
                    return "server-talks";
                
            }

    return true;
    }
    // }}}
    
    // {{{ _ReadReply ()
    private function _ReadReply () {
     
     if (!$this->connected) return;

        $this->reply_headers = array();
        $this->reply_body = "";
        
        
        
        $line = "1\r\n";
        $headers = "";
        $body = "";
        while (!feof($this->connection)) {
            $line = fgets($this->connection,1024);
            if (strlen($line) <= 2)
                break;
            $headers .= $line;
            }
        $chunk = true;
        
        $headers = preg_split('#\r\n#',$headers);

        $this->status = strtolower($headers[0]);
      
        foreach($headers as $header) {
            if (!preg_match('#www-authenticate: #i',$header))
                $header = strtolower($header);
            
            $header = preg_split("#: #",$header);
            $header[0] =  strtolower($header[0]);

                $this->reply_headers["{$header[0]}"] = array_key_exists(1,$header) ? $header[1] : "";
            }
            unset ($this->reply_headers['']);
         
        //giving 3 chances to complete reading
        $read = array($this->connection);
        for ($i = 0 ; $i < 2 ; $i++) {
            if (self::_ReadStream() === "completed")
                break;
            //$strselect = stream_select($read, $write = NULL, $except = NULL, 0,$this->data_timeout*1000);
            //if (!$strselect)
            //usleep($this->data_timeout*1000);
            $status = stream_get_meta_data ($this->connection);
            if ($status["unread_bytes"] == 0)
                break;
            }
            
    return true;      
    }
    // }}}
    
    // {{{ _ReadStream ()
    private function _ReadStream () {
        
        $content_length = 0;
        if (array_key_exists("content-length",$this->reply_headers));
            $content_length = $this->reply_headers["content-length"];

        stream_set_blocking ($this->connection, 0 );
        usleep($this->data_timeout * 1000);
        $total = 0;
        $chunk = true;
        while (true) {
            if ($content_length)
                if (strlen($this->reply_body) >= $content_length)
                    return "completed";
            else
                if (!$chunk)
                    break;
            usleep (1000);
            $chunk = @fread($this->connection,$this->window_size);
            $this->reply_body .= $chunk;
            
            $status = stream_get_meta_data ($this->connection);
            if ($status["unread_bytes"] == 0)
                break;
            }
        stream_set_blocking ($this->connection, 1 );
    return true;
    }
    // }}}

    // {{{ _BuildDigest ()
    private function _BuildDigest () {
        
        $auth = $this->reply_headers["www-authenticate"];
        
        list($head, $auth) = split(" ",$auth,2);

        $auth=split(", ",$auth);
        foreach ($auth as $sheme) {
            list($sheme,$value) = split('=',$sheme);
            $fields[$sheme] = trim(trim($value),'"');
        }

        $nc = sprintf('%x',$this->nc);
        $prepend = "";
        while ((strlen($nc) + strlen($prepend)) < 8)
            $prepend .= "0";
        $nc=$prepend.$nc;
        
        $cnonce = "printipp";
        
        $username = $this->user;
        $password = $this->password;
    
        $A1 = $username.":".$fields["realm"].":".$password;
        
        if (array_key_exists("algorithm",$fields)) {
            $algorithm = strtolower($fields["algorithm"]);
            switch ($algorithm) {
                case "md5":
                    break;
                case "md5-sess":
                    $A1 = $username.":".$fields["realm"].":".$password.":".$fields['nonce'].":".$cnonce;
                    break;
                case "token":
                    trigger_error("http_class: digest Authorization: algorithm 'token' not implemented", E_USER_WARNING);
                    return false;
                    break;
                }

            }
        
        $A2 = "POST:".$this->arguments["RequestURI"];

        
        if (array_key_exists("qop",$fields)) {
            $qop = strtolower($fields["qop"]);
            $qop = split(" ",$qop);
            if (in_array("auth",$qop))
                $qop = "auth";
            else {
                trigger_error("http_class: digest Authorization: qop others than 'auth' not implemented", E_USER_WARNING);
                return false;
                }
            }

        //echo $A1,":",$fields["nonce"],":",$A2,"<br />";    
        $response = md5(md5($A1).":". $fields["nonce"].":" .md5($A2));

        if (isset($qop) && ($qop == "auth"))
        {
        $response = md5(md5($A1).":".$fields["nonce"].":".$nc.":".$cnonce.":".$qop.":".$A2);
        }
        
        $auth_scheme = sprintf('Digest username="%s", realm="%s", nonce="%s", uri="%s", response="%s"',
                                $username,
                                $fields["realm"],
                                $fields['nonce'],
                                $this->arguments["RequestURI"],
                                $response
                                );
        //echo $auth_scheme,"<br />";
        if (isset($algorithm))
            $auth_scheme .= sprintf(', algorithm="%s"',$algorithm);

        if (isset($qop))
            $auth_scheme .= sprintf(', cnonce="%s"',$cnonce);
        
        if(array_key_exists("opaque",$fields))
            $auth_scheme .= sprintf(', opaque="%s"',$fields['opaque']);
      
        if (isset($qop))
            $auth_scheme .= sprintf(', qop="%s"',$qop);
      
        $auth_scheme .= sprintf(', nc=%s',$nc);
      
        $this->nc++;
        
    return $auth_scheme;
    }
    // }}}

};


/*
 * Local variables:
 * mode: php
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 */
?>
