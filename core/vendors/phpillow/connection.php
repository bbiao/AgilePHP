<?php
/**
 * phpillow CouchDB backend
 *
 * This file is part of phpillow.
 *
 * phpillow is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Lesser General Public License as published by the Free
 * Software Foundation; version 3 of the License.
 *
 * phpillow is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Lesser General Public License for
 * more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with phpillow; if not, write to the Free Software Foundation, Inc., 51
 * Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @package Core
 * @version $Revision: 97 $
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPL
 */

/**
 * Basic couch DB connection handling class
 *
 * Default connection ahndler using PHPs stream wrappers.
 *
 * @package Core
 * @version $Revision: 97 $
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPL
 */
abstract class phpillowConnection
{
    /**
     * CouchDB connection options
     * 
     * @var array
     */
    protected $options = array(
        'host'       => 'localhost',
        'port'       => 5984,
        'ip'         => '127.0.0.1',
        'timeout'    => .01,
        'keep-alive' => true,
        'http-log'   => false,
    );

    /**
     * Currently used database
     *
     * @var string
     */
    protected static $database = null;

    /**
     * Instance of phpillowConnection for singleton implementation.
     *
     * @var phpillowConnection
     */
    protected static $instance = null;

    /**
     * Array containing the list of allowed HTTP methods to interact with couch
     * server.
     *
     * @var array
     */
    protected static $allowedMethods = array(
        'DELETE'    => true,
        'GET'       => true,
        'POST'      => true,
        'PUT'       => true,
    );

    /**
     * Construct a couch DB connection
     *
     * Construct a couch DB connection from basic connection parameters for one
     * given database. Method is protected and should not be called directly.
     * For initializing a connection use the static method createInstance().
     *
     * @param string $host
     * @param int $port
     * @return phpillowConnection
     */
    protected function __construct( $host, $port )
    {
        $this->options['host'] = (string) $host;
        $this->options['port'] = (int) $port;

        // @TODO: Implement this properly
        $this->options['ip']   = '127.0.0.1';
    }

    /**
     * Set option value
     *
     * Set the value for an connection option. Throws an
     * phpillowOptionException for unknown options.
     * 
     * @param string $option 
     * @param mixed $value 
     * @return void
     */
    public function setOption( $option, $value )
    {
        switch ( $option )
        {
            case 'keep-alive':
                $this->options[$option] = (bool) $value;
                break;

            case 'http-log':
                $this->options[$option] = $value;
                break;

            default:
                throw new phpillowOptionException( $option );
        }
    }

    /**
     * Create a new couch DB connection instance.
     *
     * Static method to create a new couch DB connection instance. This method
     * should be used to configure the connection for later use.
     *
     * The host and its port default to localhost:5984.
     *
     * Optionally the class name of the called class can be provided. By
     * default the better working "custom connection" connection handler is
     * instantiated and used. The stream based connection handler is slower and
     * might not work at all.
     *
     * @param string $host
     * @param int $port
     * @param string $called
     * @return void
     */
    public static function createInstance( $host = '127.0.0.1', $port = 5984, $called = "phpillowCustomConnection" )
    {
        // Prevent from reestablishing connection during one run, without
        // explicit cleanup before.
        if ( self::$instance !== null )
        {
            throw new phpillowConnectionException(
                'Connection already established.',
                array()
            );
        }

        // // Deafult to custom connection, if root class has been called. This
        // // currently is the safer default.
        // if ( !$called )
        // {
        //     $called = 'phpillowCustomConnection';
        // }


        // Create connection and store it in static property to be accessible
        // by static getInstance() method.
        self::$instance = new $called( $host, $port );
    }

    /**
     * Set database to use
     *
     * Set the name of database to use. You do not need to provide this as a
     * path, but only its name.
     * 
     * @param string $database 
     * @return void
     */
    public static function setDatabase( $database )
    {
        self::$database = '/' . $database . '/';
    }

    /**
     * Return name of the currently used database
     * 
     * Return name of the currently used database
     * 
     * @return string
     */
    public static function getDatabase()
    {
        if ( self::$database === null )
        {
            throw new phpillowNoDatabaseException();
        }

        return self::$database;
    }

    /**
     * Get configured couch DB connection instance
     *
     * Get configured couch DB connection instance
     *
     * @return phpillowConnection
     */
    public static function getInstance()
    {
        // Check if connection has been properly confugured, and bail out
        // otherwise.
        if ( self::$instance === null )
        {
            throw new phpillowConnectionException(
                'No connection to database configured.',
                array()
            );
        }

        // If a connection has been configured properly, jsut return it
        return self::$instance;
    }

    /**
     * HTTP method request wrapper
     *
     * Wraps the HTTP method requests to interact with teh couch server. The
     * supported methods are:
     *  - GET
     *  - DELETE
     *  - POST
     *  - PUT
     *
     * Each request takes the request path as the first parameter and
     * optionally data as the second parameter. The requests will return a
     * object wrapping the server response.
     *
     * @param string $method
     * @param array $params
     * @return phpillow...
     */
    public function __call( $method, $params )
    {
        // Check if request method is an allowed HTTP request method.
        $method = strtoupper( $method );
        if ( !isset( self::$allowedMethods[$method] ) )
        {
            throw new phpillowInvalidRequestException(
                'Unsupported request method: %method',
                array(
                    'method' => $method,
                )
            );
        }

        // Check if required parameter containing the path is set and valid.
        if ( !isset( $params[0] ) ||
             !is_string( $params[0] ) ||
             ( $params[0][0] !== '/' ) )
        {
            throw new phpillowInvalidRequestException(
                'Absolute path required as first parameter for the request.',
                array()
            );
        }
        $path = $params[0];

        // Check if data has been provided
        $data = ( ( isset( $params[1] ) ) ? (string) $params[1] : null );
        $raw  = ( ( isset( $params[2] ) ) ? (bool) $params[2] : false );

        // Finally perform request and return the result from the server
        return $this->request( $method, $path, $data, $raw );
    }

    /**
     * Perform a request to the server and return the result
     *
     * Perform a request to the server and return the result converted into a
     * phpillowResponse object. If you do not expect a JSON structure, which
     * could be converted in such a response object, set the fourth parameter to
     * true, and you get a response object retuerned, containing the raw body.
     *
     * @param string $method
     * @param string $path
     * @param string $data
     * @param bool $raw
     * @return phpillowResponse
     */
    abstract protected function request( $method, $path, $data, $raw = false );
}

