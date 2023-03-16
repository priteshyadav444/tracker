<?php

namespace Tracker\Helper;

class UserInfo
{
    private $browserInfo;
    private $geoInfo;

    public function __construct()
    {
        //use try-catch to prevent error when server is not configured to use browscap (get_browser() function)
        try {
            $this->browserInfo = @get_browser($_SERVER['HTTP_USER_AGENT'], true);
        } catch (\Exception $e) {
            $this->browserInfo = array();
        }

        //or we got some cURL exception, etc.
        try {
            $this->geoInfo = $this->getGeoInfo();

            if (!is_array($this->geoInfo)) {
                throw new \Exception('We do not got a valid JSON answer from Freegeoip service.', 1);
            }
        } catch (\Exception $e) {
            $this->geoInfo = array();
        }
    }

    /**
     * Get user IP
     * @return string
     */
    public function getIP()
    {
        $result = null;

        //for proxy servers
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $result = end(array_filter(array_map('trim', explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']))));
        } else {
            $result = $_SERVER['REMOTE_ADDR'];
        }

        return $result;
    }

    /**
     * Get user reverse DNS
     * @return string
     */
    public function getReverseDNS()
    {
        return gethostbyaddr($this->getIP());
    }

    /**
     * Get current page URL
     * @return string
     */
    public function getCurrentURL()
    {
        return 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : '')
            . '://' . $_SERVER["SERVER_NAME"]
            . ($_SERVER['SERVER_PORT'] != '80' ? $_SERVER['SERVER_PORT'] : '')
            . $_SERVER["REQUEST_URI"];
    }

    /**
     * Get referer URL
     * @return string
     */
    public function getRefererURL()
    {
        return (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '');
    }

    /**
     * Get user browser language
     * @return string
     */
    public function getLanguage()
    {
        return strtoupper(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));
    }

    /**
     * Get user Device info (PC/Mac/Mobile/iPhone/iPad/etc...)
     * @return string
     */
    public function getDevice()
    {
        $result = '';

        $useragent = $_SERVER['HTTP_USER_AGENT'] ?? "";
        if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($useragent, 0, 4))) {
            $result = "mobile";
        } else {
            $result = "desktop";
        }
        return $result;
    }

    /**
     * Get user OS info
     * @return string
     */
    public function getOS()
    {
        $result = '';

        if (is_array($this->browserInfo) && isset($this->browserInfo['platform'])) {
            $result = $this->browserInfo['platform'];
        }

        return $result;
    }

    /**
     * Get user Browser info
     * @return string
     */
    public function getBrowser()
    {
        $u_agent = $_SERVER['HTTP_USER_AGENT'];
        $bname = 'Unknown';
        $platform = 'Unknown';
        $version = "";

        //First get the platform?
        if (preg_match('/linux/i', $u_agent)) {
            $platform = 'linux';
        } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'mac';
        } elseif (preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'windows';
        }

        // Next get the name of the useragent yes seperately and for good reason
        if (preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent)) {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        } elseif (preg_match('/Firefox/i', $u_agent)) {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        } elseif (preg_match('/OPR/i', $u_agent)) {
            $bname = 'Opera';
            $ub = "Opera";
        } elseif (preg_match('/Chrome/i', $u_agent) && !preg_match('/Edge/i', $u_agent)) {
            $bname = 'Google Chrome';
            $ub = "Chrome";
        } elseif (preg_match('/Safari/i', $u_agent) && !preg_match('/Edge/i', $u_agent)) {
            $bname = 'Apple Safari';
            $ub = "Safari";
        } elseif (preg_match('/Netscape/i', $u_agent)) {
            $bname = 'Netscape';
            $ub = "Netscape";
        } elseif (preg_match('/Edge/i', $u_agent)) {
            $bname = 'Edge';
            $ub = "Edge";
        } elseif (preg_match('/Trident/i', $u_agent)) {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        }

        // finally get the correct version number
        $known = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', $known) .
            ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (!preg_match_all($pattern, $u_agent, $matches)) {
            // we have no matching number just continue
        }
        // see how many we have
        $i = count($matches['browser']);
        if ($i != 1) {
            //we will have two since we are not using 'other' argument yet
            //see if version is before or after the name
            if (strripos($u_agent, "Version") < strripos($u_agent, $ub)) {
                $version = $matches['version'][0];
            } else {
                $version = $matches['version'][1];
            }
        } else {
            $version = $matches['version'][0];
        }

        // check if we have a number
        if ($version == null || $version == "") {
            $version = "?";
        }

        return
            "'userAgent' : $u_agent,
            'name'      : $bname,
            'version'   : $version,
            'platform'  : $platform,
            'pattern' : $pattern";
    }

    /**
     * Get user Country Code
     * @return string
     */
    public function getCountryCode()
    {
        $result = '';

        if (is_array($this->geoInfo) && isset($this->geoInfo['country_code'])) {
            $result = $this->geoInfo['country_code'];
        }

        return $result;
    }

    /**
     * Get user Country Name
     * @return string
     */
    public function getCountryName()
    {
        $result = '';

        if (is_array($this->geoInfo) && isset($this->geoInfo['country'])) {
            $result = $this->geoInfo['country'];
        }

        return $result;
    }

    /**
     * Get user Region Code
     * @return string
     */
    public function getRegionCode()
    {
        $result = '';

        if (is_array($this->geoInfo) && isset($this->geoInfo['region_code'])) {
            $result = $this->geoInfo['region_code'];
        }

        return $result;
    }

    /**
     * Get user Region Name
     * @return string
     */
    public function getRegionName()
    {
        $result = '';

        if (is_array($this->geoInfo) && isset($this->geoInfo['region'])) {
            $result = $this->geoInfo['region'];
        }

        return $result;
    }

    /**
     * Get user City
     * @return string
     */
    public function getCity()
    {
        $result = '';

        if (is_array($this->geoInfo) && isset($this->geoInfo['city'])) {
            $result = $this->geoInfo['city'];
        }

        return $result;
    }

    /**
     * Get user Zipcode
     * @return string
     */
    public function getZipcode()
    {
        $result = '';

        if (is_array($this->geoInfo) && isset($this->geoInfo['postal'])) {
            $result = $this->geoInfo['postal'];
        }

        return $result;
    }

    /**
     * Get user Latitude
     * @return string
     */
    public function getLatitude()
    {
        $result = '';

        if (is_array($this->geoInfo) && isset($this->geoInfo['latitude'])) {
            $result = $this->geoInfo['latitude'];
        }

        return $result;
    }

    /**
     * Get user Longitude
     * @return string
     */
    public function getLongitude()
    {
        $result = '';

        if (is_array($this->geoInfo) && isset($this->geoInfo['longitude'])) {
            $result = $this->geoInfo['longitude'];
        }

        return $result;
    }

    /**
     * Check if connection was through proxy
     * @return boolean
     */
    public function isProxy()
    {
        $result = false;

        //for proxy servers
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $addresses = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

            if (count($addresses) > 0) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Get geo information about user. For this we use user IP and external service
     * IpWho (http://ipwho.is/)
     */
    private function getGeoInfo()
    {
        $url = 'http://ipwho.is/' . self::getIP();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response, true);
        return $result;
    }
}
