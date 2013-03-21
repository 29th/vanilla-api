<?php if (!defined('APPLICATION')) exit();

$PluginInfo['VanillaAPI'] = array(

    'Name'          => 'Vanilla API',
    'Description'   => 'RESTful API for Vanilla that responds in JSON and XML. Documentation is available through the <a href="/api">API explorer</a>',
    'Version'       => '0.1.0',
    'Author'        => 'Kasper Kronborg Isager',
    'AuthorEmail'   => 'kasperisager@gmail.com',
    'AuthorUrl'     => 'https://github.com/kasperisager/VanillaAPI',
    'License'       => 'MIT'
    
);

/**
 * To be written
 *
 * @package     API
 * @since       0.1.0
 * @author      Kasper Kronborg Isager <kasperisager@gmail.com>
 * @copyright   Copyright © 2013
 * @license     http://opensource.org/licenses/MIT MIT
 */
class VanillaAPI extends Gdn_Plugin
{
    /**
     * Map the API request to the appropriate controller
     *
     * @package API
     * @since 0.1.0
     * @access public
     */
    public function Gdn_Dispatcher_BeforeDispatch_Handler($Sender)
    {
        $Request        = Gdn::Request();
        $RequestURI     = $Request->RequestURI();
        $RequestMethod  = $Request->RequestMethod();

        // Intercept API requests and store the requested class
        if (preg_match('/^api\/(\w+)/i', $RequestURI, $Class)) {

            // Only deliver data - nothing else is needed
            Gdn::Request()->WithDeliveryType(DELIVERY_TYPE_DATA);

            // Only deliver XML if specifically requested
            $Accept = $Request->Merged('HTTP_ACCEPT');
            $Format = ($Accept == 'application/xml') ? 'xml' : 'json';

            if ($Format == 'xml') {
                Gdn::Request()->WithDeliveryMethod(DELIVERY_METHOD_XML);
            } else {
                Gdn::Request()->WithDeliveryMethod(DELIVERY_METHOD_JSON);
            }
            
            if (!class_exists($Class[1]))
                return;

            $Class = new $Class[1];

            $Params = array(
                'Request'   => $Request->Merged(),
                'URI'       => explode('/', $RequestURI)
            );

            switch(strtolower($RequestMethod)) {

                case 'get':
                    $Data = $Class->Get($Params);
                    break;

                case 'post':
                    $Data = $Class->Post($Params);

                    // Combine the POST request with any custom arguments,
                    // this being because I also use POST for deleteing and
                    // updating resources
                    Gdn::Request()->SetRequestArguments(
                        Gdn_Request::INPUT_POST, array_merge(
                            Gdn::Request()->Post(),
                            $Data['Args']
                        )
                    );

                    // Set the POST request
                    $_POST = Gdn::Request()->Post();

                    break;

                case 'put':
                    // Still trying to figure out how to make PUT work
                    /*
                    $Data = $Class->Put($Params);

                    $_POST = self::ParsePhpInput();

                    // Combine the POST request with any custom arguments
                    Gdn::Request()->SetRequestArguments(
                        Gdn_Request::INPUT_POST, array_merge(
                            $_PUT,
                            $Data['Args']
                        )
                    );

                    $_POST = Gdn::Request()->Post();
                    */
                    break;

                case 'delete':
                    // Still trying to figure out how to make DELETE work
                    /*
                    $Data = $Class->Put($Params);

                    $_DELETE = self::ParsePhpInput();

                    // Combine the POST request with any custom arguments
                    Gdn::Request()->SetRequestArguments(
                        Gdn_Request::INPUT_POST, array_merge(
                            $_DELETE,
                            $Data['Args']
                        )
                    );

                    $_POST = Gdn::Request()->Post();
                    */
                    break;

            }

            Gdn::Request()->WithURI($Data['Map']);
        }
    }

    /**
     * Parse and return PUT/DELETE data
     *
     * @package API
     * @since 0.1.0
     * @access public
     */
    public static function ParsePhpInput()
    {
        // Fetch PUT content and determine Boundary
        $RawData = file_get_contents('php://input');
        $Boundary = substr($RawData, 0, strpos($RawData, "\r\n"));

        // Fetch each part
        $Parts = array_slice(explode($Boundary, $RawData), 1);
        $PutData = array();

        foreach ($Parts as $Part) {
            // If this is the last part, break
            if ($Part == "--\r\n") break; 

            // Separate content from headers
            $Part = ltrim($Part, "\r\n");
            list($RawHeaders, $PutBody) = explode("\r\n\r\n", $Part, 2);

            // Parse the headers list
            $RawHeaders = explode("\r\n", $RawHeaders);
            $headers = array();
            foreach ($RawHeaders as $header) {
                list($Name, $Value) = explode(':', $header);
                $headers[strtolower($Name)] = ltrim($Value, ' '); 
            } 

            // Parse the Content-Disposition to get the field name, etc.
            if (isset($headers['content-disposition'])) {
                $filename = null;
                preg_match(
                    '/^(.+); *name="([^"]+)"(; *filename="([^"]+)")?/', 
                    $headers['content-disposition'], 
                    $Matches
                );
                list(, $Type, $Name) = $Matches;
                isset($Matches[4]) and $filename = $Matches[4]; 

                // handle your fields here
                switch ($Name) {
                    // this is a file upload
                    case 'userfile':
                         file_put_contents($filename, $PutBody);
                         break;

                    // default for all other files is to populate $PutData
                    default: 
                         $PutData[$Name] = substr($PutBody, 0, strlen($PutBody) - 2);
                         break;
                } 
            }

        }

        return $PutData;
    }

    /**
     * No setup required
     *
     * @package API
     * @since 0.1.0
     * @access public
     */
    public function Setup()
    {
        return TRUE;
    }

    /**
     * No cleanup required
     *
     * @package API
     * @since 0.1.0
     * @access public
     */
    public function OnDisable()
    {
        return TRUE;
    }
}