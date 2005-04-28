<?php
/**
 * This file implements the Filelist class.
 *
 * This file is part of the b2evolution/evocms project - {@link http://b2evolution.net/}.
 * See also {@link http://sourceforge.net/projects/evocms/}.
 *
 * @copyright (c)2003-2005 by Francois PLANQUE - {@link http://fplanque.net/}.
 * Parts of this file are copyright (c)2004-2005 by Daniel HAHLER - {@link http://thequod.de/contact}.
 *
 * @license http://b2evolution.net/about/license.html GNU General Public License (GPL)
 * {@internal
 * b2evolution is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * b2evolution is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with b2evolution; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * }}
 *
 * {@internal
 * Daniel HAHLER grants Fran�ois PLANQUE the right to license
 * Daniel HAHLER's contributions to this file and the b2evolution project
 * under any OSI approved OSS license (http://www.opensource.org/licenses/).
 * }}
 *
 * @package evocore
 *
 * {@internal Below is a list of authors who have contributed to design/coding of this file: }}
 * @author blueyed: Daniel HAHLER
 * @author fplanque: Fran�ois PLANQUE
 *
 * @version $Id$
 *
 */
if( !defined('EVO_CONFIG_LOADED') ) die( 'Please, do not access this page directly.' );


/**
 * Includes
 */
require_once dirname(__FILE__).'/_file.class.php';


/**
 * Lists files in a directory.
 *
 * Can work recursively through subdirectories.
 *
 * @package evocore
 */
class Filelist
{
	var $listpath = '';

	var $filterString = NULL;
	var $filterIsRegexp = NULL;

	/**
	 * The list of Files.
	 * @var array of File objects
	 * @access protected
	 */
	var $_entries = array();

	/**
	 * Index on File IDs (id => {@link $_entries} key).
	 *
	 * Note: fplanque>> what's the purpose of the md5 IDs??
	 *
	 * @todo make these direct links to &File objects
	 * @var array
	 * @access protected
	 */
	var $_md5_ID_index = array();

	/**
	 * Index on full paths (path => {@link $_entries} key).
	 * @todo make these direct links to &File objects
	 * @var array
	 * @access protected
	 */
	var $_full_path_index = array();

	/**
	 * Index on sort order (order # => {@link $_entries} key).
	 * @todo make these direct links to &File objects
	 * @var array
	 * @access protected
	 */
	var $_order_index = array();

	/**
	 * Number of entries in the {@link $_entries} array
	 *
	 * Note: $_total_entries = $_total_dirs + $_total_files
	 *
	 * @var integer
	 * @access protected
	 */
	var $_total_entries = 0;

	/**
	 * @var integer Number of directories
	 * @access protected
	 */
	var $_total_dirs = 0;

	/**
	 * @var integer Number of files
	 * @access protected
	 */
	var $_total_files = 0;

	/**
	 * @var integer Number of bytes
	 * @access protected
	 */
	var $_total_bytes = 0;

	/**
	 * Index of the current iterator position.
	 *
	 * This is the key of {@link $_order_index}
	 *
	 * @var integer
	 * @access protected
	 */
	var $_current_idx = -1;

	/**
	 * What column is the list ordered on?
	 *
	 * Possible values are: 'name', 'path', 'type', 'size', 'lastmod', 'perms'
	 *
	 * @var string
	 * @access protected
	 */
	var $_order = NULL;

	/**
	 * Are we sorting ascending (or descending). 
	 * 
	 * NULL is default and means ascending for 'name', descending for the rest
	 *
	 * @todo fplanque>> document possible values!!
	 * @var mixed
	 * @access protected
	 */
	var $_order_asc = NULL;

	/**
	 * Sort dirs not at top
	 * @var boolean
	 */
	var $dirsnotattop = false;


	/**
	 * Get size (width, height) for images?
	 *
	 * @var boolean
	 */
	var $getImageSizes = false;


	/**
	 * include hidden files? (Filemanager user preference)
	 * @var boolean
	 */
	var $showhidden = true;


	/**
	 * User preference: recursive size of dirs?
	 *
	 * The load() & sort() methods use this.
	 *
	 * @var boolean
	 * @access protected
	 */
	var $_use_recursive_dirsize = false;


	/**
	 * to be extended by Filemanager class
	 * @var Log
	 */
	var $Messages;


	/**
	 * Constructor
	 *
	 * @param string the default path for the files
	 * @param string Allow all paths or just the default path (which must be non-empty then)?
	 */
	function Filelist( $path = '', $allowAllPaths = true )
	{
		if( empty($path) )
		{
			$this->listpath = false;
			$this->_allowAllPaths = true;
		}
		else
		{
			$this->listpath = trailing_slash( $path );
			$this->_allowAllPaths = $allowAllPaths;
		}

	}


	/**
	 * Loads the filelist entries.
	 *
	 * @param boolean use flat mode (all files recursive without directories)
	 */
	function load( $flatmode = false )
	{
		if( !$this->listpath )
		{
			return false;
		}

		$this->_total_entries = 0;
		$this->_total_bytes = 0;
		$this->_total_files = 0;
		$this->_total_dirs = 0;

		$this->_entries = array();
		$this->_md5_ID_index = array();
		$this->_full_path_index = array();
		$this->_order_index = array();


		if( $flatmode )
		{
			$toAdd = retrieveFiles( $this->listpath );
		}
		else
		{
			$toAdd = retrieveFiles( $this->listpath, true, true, true, false );
		}

		if( $toAdd === false )
		{
			$this->Messages->add( sprintf( T_('Cannot open directory &laquo;%s&raquo;!'), $this->listpath ), 'fl_error' );
			return false;
		}


		foreach( $toAdd as $entry )
		{
			if( !$this->showhidden && substr($entry, 0, 1) == '.' )
			{ // hidden files (prefixed with .)
				continue;
			}
			if( $this->filterString !== NULL )
			{ // Filter: must match filename
				$name = basename( $entry );

				if( $this->filterIsRegexp )
				{
					if( !preg_match( '#'.str_replace( '#', '\#', $this->filterString ).'#', $name ) )
					{ // does not match the regexp filter
						continue;
					}
				}
				else
				{
					if( !my_fnmatch( $this->filterString, $name ) )
					{
						continue;
					}
				}
			}

			$this->addFileByPath( $entry, true );
		}
	}


	/**
	 * Add a File object to the list (by reference).
	 *
	 * @param File File object (by reference)
	 * @param boolean Has the file to exist to get added?
	 * @return boolean true on success, false on failure
	 */
	function addFile( & $File, $mustExist = false )
	{
		if( !is_a( $File, 'file' ) )
		{
			return false;
		}

		if( $mustExist && !$File->exists() )
		{
			return false;
		}


		$this->_entries[$this->_total_entries] = & $File;
		$this->_md5_ID_index[$File->get_md5_ID()] = $this->_total_entries;
		$this->_full_path_index[$File->get_full_path()] = $this->_total_entries;
		// add file to the end:
		$this->_order_index[$this->_total_entries] = $this->_total_entries;

		// Count 1 more entry (file or dir)
		$this->_total_entries++;

		if( $File->is_dir() )
		{	// Count 1 more directory
			$this->_total_dirs++;

			// fplanque>> TODO: get this outta here??
			if( $this->_use_recursive_dirsize )
			{ // We want to use recursive directory sizes
				// won't be done in the File constructor
				$File->setSize( get_dirsize_recursive( $File->get_full_path() ) );
			}
		}
		else
		{	// Count 1 more file
			$this->_total_files++;
		}
		
		// Count total bytes in this dir
		$this->_total_bytes += $File->get_size();

		return true;
	}


	/**
	 * Add a file to the list, by filename.
	 *
	 * This is a stub for {@link addFile()}.
	 *
	 * @param string|File file name / full path or {@link File} object
	 * @param boolean Has the file to exist to get added?
	 * @return boolean true on success, false on failure (path not allowed,
	 *                 file does not exist)
	 * @todo optimize (blueyed)
	 */
	function addFileByPath( $path, $mustExist = false )
	{
		global $FileCache;

		$basename = basename($path);
		$dirname = dirname($path).'/';

		if( $basename != $path && !$this->_allowAllPaths )
		{ // path attached and not all paths allowed
			if( $dirname != $this->listpath )
			{ // not this list's path
				return false;
			}
		}

		$NewFile = & $FileCache->get_by_path( $path  );

		return $this->addFile( $NewFile, $mustExist );
	}


	/**
	 * Sort the entries by sorting the internal {@link $_order_index} array.
	 *
	 * @param string The order to use ('name', 'type', 'lastmod', .. )
	 * @param boolean Ascending (true) or descending
	 * @param boolean Sort directories at top?
	 */
	function sort( $order = NULL, $orderasc = NULL, $dirsattop = NULL )
	{
		if( !$this->_total_entries )
		{
			return false;
		}

		if( $order !== NULL )
		{
			$this->_order = $order;
		}
		if( $orderasc !== NULL )
		{
			$this->_order_asc = $orderasc;
		}
		if( $dirsattop !== NULL )
		{
			$this->dirsnotattop = !$dirsattop;
		}

		usort( $this->_order_index, array( $this, '_sortCallback' ) );


		// Restart the list
		$this->restart();
	}


	/**
	 * usort callback function for {@link sort()}, because we cannot eval() right there
	 *
	 * @access protected
	 * @return integer
	 */
	function _sortCallback( $a, $b )
	{
		$FileA =& $this->_entries[$a];
		$FileB =& $this->_entries[$b];

		// What colmun are we sorting on?
		switch( $this->_order )
		{
			case 'size':
				if( $this->_use_recursive_dirsize )
				{	// We are using recursive directory sizes:
					$r = $FileA->get_size() - $FileB->get_size();
				}
				else
				{
					$r = $FileA->is_dir() && $FileB->is_dir() ?
									strcasecmp( $FileA->get_name(), $FileB->get_name() ) :
									( $FileA->get_size() - $FileB->get_size() );
				}
				break;

			case 'path': // group by dir
				$r = strcasecmp( $FileA->get_dir(), $FileB->get_dir() );
				if( $r == 0 )
				{
					$r = strcasecmp( $FileA->get_name(), $FileB->get_name() );
				}
				break;

			case 'lastmod':
				$r = $FileB->get_lastmod_ts() - $FileA->get_lastmod_ts();
				break;

			case 'perms':
				// This will use literal representation ( 'r', 'r+w' / octal )
				$r = strcasecmp( $FileA->get_perms(), $FileB->get_perms() );
				break;

			default:
			case 'name':
				$r = strcasecmp( $FileA->get_name(), $FileB->get_name() );
				if( $r == 0 )
				{ // same name: look at path
					$r = strcasecmp( $FileA->get_dir(), $FileB->get_dir() );
				}
				break;
		}


		if( !$this->_order_asc )
		{ // switch order
			$r = -$r;
		}

		if( !$this->dirsnotattop )
		{
			if( $FileA->is_dir() && !$FileB->is_dir() )
			{
				$r = -1;
			}
			elseif( $FileB->is_dir() && !$FileA->is_dir() )
			{
				$r = 1;
			}
		}

		return $r;
	}


	/**
	 * Restart the list
	 */
	function restart()
	{
		$this->_current_idx = -1;
	}


	/**
	 * Are we sorting ascending?
	 *
	 * @param string The type (empty for current order type)
	 * @return integer 1 for ascending sorting, 0 for descending
	 */
	function isSortingAsc( $type = '' )
	{
		if( empty($type) )
		{
			$type = $this->_order;
		}

		if( $this->_order_asc === NULL )
		{ // default
			return ( $type == 'name' || $type == 'path' ) ? 1 : 0;
		}
		else
		{
			return ( $this->_order_asc ) ? 1 : 0;
		}
	}


	/**
	 * Is a filter active?
	 *
	 * @return boolean
	 */
	function isFiltering()
	{
		return $this->filterString !== NULL;
	}


	/**
	 * Is a File in the list?
	 *
	 * @param File the File object to look for
	 * @return boolean
	 */
	function holdsFile( $File )
	{
		return isset( $this->_md5_ID_index[ $File->get_md5_ID() ] );
	}


	/**
	 * Get the order the list is sorted by.
	 *
	 * @return NULL|string
	 */
	function getOrder()
	{
		return $this->_order;
	}


	/**
	 * Return the current filter
	 *
	 * @param boolean add a note when it's a regexp or no filter?
	 * @return string the filter
	 */
	function getFilter( $note = true )
	{
		if( $this->filterString === NULL )
		{
			return $note ?
							T_('No filter') :
							'';
		}
		else
		{
			return $this->filterString
							.( $note && $this->filterIsRegexp ?
									' ('.T_('regular expression').')' :
									'' );
		}
	}


	/**
	 * Get the number of entries.
	 *
	 * @return integer
	 */
	function count()
	{
		return $this->_total_entries;
	}


	/**
	 * Get the number of directories.
	 *
	 * @return integer
	 */
	function countDirs()
	{
		return $this->_total_dirs;
	}


	/**
	 * Get the number of files.
	 *
	 * @return integer
	 */
	function countFiles()
	{
		return $this->_total_files;
	}


	/**
	 * Get the number of bytes of all files.
	 *
	 * @return integer
	 */
	function countBytes()
	{
		return $this->_total_bytes;
	}


	/**
	 * Get the next entry and increment internal counter.
	 *
	 * @param string can be used to query only 'file's or 'dir's.
	 * @return boolean File object (by reference) on success, false on end of list
	 */
	function & getNextFile( $type = '' )
	{
		/**
		 * @debug return the same file 10 times, useful for profiling
		static $debugMakeLonger = 0;
		if( $debugMakeLonger-- == 0 )
		{
			$this->_current_idx++;
			$debugMakeLonger = 9;
		}
		*/

		if( !isset($this->_order_index[$this->_current_idx + 1]) )
		{
			return false;
		}
		$this->_current_idx++;

		$index = $this->_order_index[$this->_current_idx];

		if( $type != '' )
		{
			if( $type == 'dir' && !$this->_entries[ $index ]->is_dir() )
			{ // we want a dir
				return $this->getNextFile( 'dir' );
			}
			elseif( $type == 'file' && $this->_entries[ $index ]->is_dir() )
			{ // we want a file
				return $this->getNextFile( 'file' );
			}
		}

		return $this->_entries[ $index ];
	}


	/**
	 * Get a file by it's full path.
	 *
	 * @param string the full path
	 * @return mixed File object (by reference) on success, false on failure.
	 */
	function &getFileByPath( $path )
	{
		$path = str_replace( '\\', '/', $path );

		if( isset( $this->_full_path_index[ $path ] ) )
		{
			return $this->_entries[ $this->_full_path_index[ $path ] ];
		}
		else
		{
			return false;
		}
	}


	/**
	 * Get a file by it's ID.
	 *
	 * @param string the ID (MD5 of path and name)
	 * @return mixed File object (by reference) on success, false on failure.
	 */
	function &getFileByID( $md5id )
	{
		if( isset( $this->_md5_ID_index[ $md5id ] ) )
		{
			return $this->_entries[ $this->_md5_ID_index[ $md5id ] ];
		}
		else
		{
			return false;
		}
	}


	/**
	 * Get a file by index.
	 *
	 * @param integer Index of the entries (starting with 0)
	 * @return false|File
	 */
	function &getFileByIndex( $index )
	{
		if( isset( $this->_order_index[ $index ] ) )
		{
			return $this->_entries[ $this->_order_index[ $index ] ];
		}
		else
		{
			return false;
		}
	}


	/**
	 * Get the path (and name) of a {@link File} relative to the {@link $listpath list's path}.
	 *
	 * @param File the File object
	 * @param boolean appended with name? (folders will get an ending slash)
	 * @return string path (and optionally name)
	 */
	function getFileSubpath( &$File, $withName = true, $rootDir = NULL )
	{
		if( $rootDir === NULL )
		{
			$rootDir = $this->listpath;
		}
		$path = substr( $File->get_dir(), strlen($rootDir) );

		if( $withName )
		{
			$path .= $File->get_name();
			if( $File->is_dir() )
			{
				$path .= '/';
			}
		}

		return $path;
	}


	/**
	 * Unsets a {@link File} from the entries list.
	 *
	 * @return boolean true on success, false if not found in list.
	 */
	function removeFromList( &$File )
	{
		if( isset( $this->_md5_ID_index[ $File->get_md5_ID() ] ) )
		{ // unset indexes and entry
			$index = $this->_full_path_index[ $File->get_full_path() ];
			unset( $this->_full_path_index[ $File->get_full_path() ] );

			foreach( $this->_order_index as $lKey => $lValue )
			{
				if( $lValue == $index )
				{
					while( isset( $this->_order_index[++$lKey] ) )
					{
						$this->_order_index[ $lKey - 1 ] = $this->_order_index[ $lKey ];
					}
					unset( $this->_order_index[$lKey - 1] );
				}
			}
			unset( $this->_entries[ $this->_md5_ID_index[ $File->get_md5_ID() ] ] );
			unset( $this->_md5_ID_index[ $File->get_md5_ID() ] );

			return true;
		}
		return false;
	}


	/**
	 * Get the list of File entries.
	 *
	 * You can use a method on each object to get this as result instead of the object
	 * itself.
	 *
	 * @param string Use this method on every File and put the result into the list.
	 * @return array The array with the File objects or method results
	 */
	function getFilesArray( $method = NULL )
	{
		$r = array();

		if( is_string($method) )
		{
			foreach( $this->_order_index as $index )
			{
				$r[] =& $this->_entries[ $index ]->$method();
			}
		}
		else
		{
			foreach( $this->_order_index as $index )
			{
				$r[] =& $this->_entries[ $index ];
			}
		}

		return $r;
	}


	/**
	 * Get a MD5 checksum over the entries.
	 * Used to identify a unique filelist.
	 *
	 * @return string md5 hash
	 */
	function toMD5()
	{
		return md5( serialize( $this->_entries ) );
	}


	/**
	 * Attempt to load meta data for all files in the list.
	 *
	 * Will attempt only once per file and cache the result.
	 */
	function load_meta( $force_creation = false )
	{
		global $DB, $Debuglog, $FileCache;

		$to_load = array();

		foreach( $this->_entries as $loop_File )
		{	// For each file:
			// echo $loop_File->get_full_path();

			if( $loop_File->meta != 'unknown' )
			{ // We have already loading meta data:
				continue;
			}

			$to_load[] = $DB->quote( $loop_File->get_full_path() );
		}

		if( ! count( $to_load ) )
		{	// We don't need to load anything...
			return false;
		}

		if( ! $rows = $DB->get_results( 'SELECT *
																			 FROM T_files
																			WHERE file_root_type = \'absolute\'
																				AND file_root_ID = 0
																				AND file_path IN ('.implode( ',', $to_load ).')',
																			OBJECT, 'Load FileList meta data' ) )
		{ // We haven't found any meta data...
			return false;
		}

		// Go through rows of loaded meta data...
		foreach( $rows as $row )
		{
			// Retrieve matching File object:
			$loop_File = & $FileCache->get_by_path( $row->file_path );

			// Associate meta data to File object:
			$loop_File->load_meta( false, $row );
		}

		return true;
	}
}

/*
 * $Log$
 * Revision 1.24  2005/04/28 20:44:20  fplanque
 * normalizing, doc
 *
 * Revision 1.23  2005/04/27 19:05:46  fplanque
 * normalizing, cleanup, documentaion
 *
 * Revision 1.21  2005/04/19 16:23:02  fplanque
 * cleanup
 * added FileCache
 * improved meta data handling
 *
 * Revision 1.20  2005/02/28 09:06:33  blueyed
 * removed constants for DB config (allows to override it from _config_TEST.php), introduced EVO_CONFIG_LOADED
 *
 * Revision 1.19  2005/01/26 17:55:23  blueyed
 * catching up..
 *
 * Revision 1.17  2005/01/08 22:10:43  blueyed
 * really fixed filelist (hopefully)
 *
 * Revision 1.16  2005/01/08 12:54:03  blueyed
 * fixed/refactored sort()
 *
 * Revision 1.15  2005/01/08 01:24:19  blueyed
 * filelist refactoring
 *
 * Revision 1.14  2005/01/06 15:45:35  blueyed
 * Fixes..
 *
 * Revision 1.13  2005/01/06 11:31:45  blueyed
 * bugfixes
 *
 * Revision 1.12  2005/01/06 10:15:45  blueyed
 * FM upload and refactoring
 *
 * Revision 1.11  2005/01/05 03:04:01  blueyed
 * refactored
 *
 * Revision 1.5  2004/11/03 00:58:02  blueyed
 * update
 *
 * Revision 1.4  2004/10/24 22:55:12  blueyed
 * upload, fixes, ..
 *
 * Revision 1.3  2004/10/21 00:14:44  blueyed
 * moved
 *
 * Revision 1.2  2004/10/16 01:31:22  blueyed
 * documentation changes
 *
 * Revision 1.1  2004/10/13 22:46:32  fplanque
 * renamed [b2]evocore/*
 *
 * Revision 1.12  2004/10/12 10:27:18  fplanque
 * Edited code documentation.
 *
 */
?>