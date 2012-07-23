<?php
/* 
| --------------------------------------------------------------
| 
| Plexis
|
| --------------------------------------------------------------
|
| Author:       Steven Wilson
| Copyright:    Copyright (c) 2012, Plexis Dev Team
| License:      GNU GPL v3
|
*/

// All namespace paths must be Uppercase first letter!
namespace Wowlib;

// Kill the script if its a direct link!
if( !defined('CMS_VERSION') ) die('Unauthorized');

class Wowlib
{
    // Our DB Connections
    protected $CDB;
    protected $WDB;
    
    // Out realm info array
    protected $realm_info;
    
    // Our emulator
    protected $emulator;
    

/*
| ---------------------------------------------------------------
| Constructor
| ---------------------------------------------------------------
|
*/
    public function __construct($data)
    {
        // Load the Loader class
        $this->load = load_class('Loader');
        
        // Turn our connection info into an array
        $world = unserialize($data['world_db']);
        $char = unserialize($data['char_db']);
        
        // Set the connections into the connection variables
        $this->CDB = $this->load->database($char, false, true);
        $this->WDB = $this->load->database($world, false, true);
        
        // Throw an exception if we cant establish connections
        if(!$this->CDB || !$this->WDB)
        {
            throw new \Exception('Failed to load database connections.');
            return;
        }
        
        // Finally set our class realm variable
        $this->realm_info = $data;
        $this->emulator = config('emulator');
    }
    
/*
| ---------------------------------------------------------------
| Extenstion loader
| ---------------------------------------------------------------
|
*/
    public function __get($name)
    {
        // Just return the extension if it exists
        if(isset($this->{$name}) && is_object($this->{$name})) return $this->{$name};
        
        // Create our classname
        $class = ucfirst( strtolower($name) );
        $libname = strtolower($this->realm_info['driver']);
        
        // Check for the extension
		$file = path( ROOT, "third_party", "wowlib", $this->emulator, $libname, $class . ".php" );
        if( !file_exists( $file ) ) 
        {
            // Extension doesnt exists :O
            show_error('Failed to load wowlib extentsion %s', array($name), E_ERROR);
            return false;
        }
        
        // Load the extension file
        require_once( $file );
        
        // Load the class
        $class = "\\Wowlib\\". ucfirst($this->realm_info['driver']) ."\\". $class;
        $this->{$name} = new $class($this->CDB, $this->WDB);
        return $this->{$name};
    }
}
?>