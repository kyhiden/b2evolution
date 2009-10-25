<?php
/**
 * This file is part of b2evolution - {@link http://b2evolution.net/}
 * See also {@link http://sourceforge.net/projects/evocms/}.
 *
 * @copyright (c)2009 by Francois PLANQUE - {@link http://fplanque.net/}
 * Parts of this file are copyright (c)2009 by The Evo Factory - {@link http://www.evofactory.com/}.
 *
 * Released under GNU GPL License - {@link http://b2evolution.net/about/license.html}
 *
 * {@internal Open Source relicensing agreement:
 * The Evo Factory grants Francois PLANQUE the right to license
 * The Evo Factory's contributions to this file and the b2evolution project
 * under any OSI approved OSS license (http://www.opensource.org/licenses/).
 * }}
 *
 * @package maintenance
 *
 * {@internal Below is a list of authors who have contributed to design/coding of this file: }}
 * @author efy-maxim: Evo Factory / Maxim.
 * @author fplanque: Francois Planque.
 *
 * @version $Id$
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );


/**
 * @var strings base application paths
 */
global $basepath, $conf_subdir, $skins_subdir, $adminskins_subdir;
global $plugins_subdir, $media_subdir, $backup_subdir, $upgrade_subdir;

/**
 * @var array backup paths
 */
global $backup_paths;

/**
 * @var array backup tables
 */
global $backup_tables;


/**
 * Backup folder/files default settings
 * - 'label' checkbox label
 * - 'note' checkbox note
 * - 'path' path to folder or file
 * - 'included' true if folder or file must be in backup
 * @var array
 */
$backup_paths = array(
	'application_files'   => array (
		'label'    => T_( 'Application files' ), /* It is files root. Please, don't remove it. */
		'path'     => '*',
		'included' => true ),

	'configuration_files' => array (
		'label'    => T_( 'Configuration files' ),
		'path'     => $conf_subdir,
		'included' => true ),

	'skins_files'         => array (
		'label'    => T_( 'Skins' ),
		'path'     => array( 	$skins_subdir,
							$adminskins_subdir ),
		'included' => true ),

	'plugins_files'       => array (
		'label'    => T_( 'Plugins' ),
		'path'     => $plugins_subdir,
		'included' => true ),

	'media_files'         => array (
		'label'    => T_( 'Media folder' ),
		'path'     => $media_subdir,
		'included' => false ),

	'backup_files'        => array (
		'label'    => NULL,		// Don't display in form. Just exclude from backup.
		'path'     => $backup_subdir,
		'included' => false ),

	'upgrade_files'        => array (
		'label'    => NULL,		// Don't display in form. Just exclude from backup.
		'path'     => $upgrade_subdir,
		'included' => false ) );

/**
 * Backup database tables default settings
 * - 'label' checkbox label
 * - 'note' checkbox note
 * - 'tables' tables list
 * - 'included' true if database tables must be in backup
 * @var array
 */
$backup_tables = array(
	'content_tables'      => array (
		'label'    => T_( 'Content tables' ), /* It means collection of all of the tables. Please, don't remove it. */
		'table'   => '*',
		'included' => true ),

	'logs_stats_tables'   => array (
		'label'    => T_( 'Logs & stats tables' ),
		'table'   => array(
			'T_sessions',
			'T_hitlog',
			'T_basedomains',
			'T_track__goalhit',
			'T_track__keyphrase',
			'T_useragents',
		),
		'included' => false ) );


/**
 * Backup class
 * This class is responsible to backup application files and data.
 *
 */
class Backup
{
	/**
	 * All of the paths and their 'included' values defined in backup configuration file
	 * @var array
	 */
	var $backup_paths;

	/**
	 * All of the tables and their 'included' values defined in backup configuration file
	 * @var array
	 */
	var $backup_tables;

	/**
	 * True if pack backup files
	 * @var boolean
	 */
	var $pack_backup_files;


	/**
	 * Constructor
	 */
	function Backup()
	{
		global $backup_paths, $backup_tables;

		// Set default settings defined in backup configuration file

		// Set backup folders/files default settings
		$this->backup_paths = array();
		foreach( $backup_paths as $name => $settings )
		{
			$this->backup_paths[$name] = $settings['included'];
		}

		// Set backup tables default settings
		$this->backup_tables = array();
		foreach( $backup_tables as $name => $settings )
		{
			$this->backup_tables[$name] = $settings['included'];
		}

		$this->pack_backup_files = true;
	}


	/**
	 * Load settings from request
	 */
	function load_from_Request()
	{
		global $backup_paths, $backup_tables, $Messages;

		// Load folders/files settings from request
		foreach( $backup_paths as $name => $settings )
		{
			if( array_key_exists( 'label', $settings ) && !is_null( $settings['label'] ) )
			{	// We can set param
				$this->backup_paths[$name] = param( 'bk_'.$name, 'boolean' );
			}
		}

		// Load tables settings from request
		foreach( $backup_tables as $name => $settings )
		{
			$this->backup_tables[$name] = param( 'bk_'.$name, 'boolean' );
		}

		$this->pack_backup_files = param( 'bk_pack_backup_files', 'boolean', 0 );

		// Check are there something to backup
		if( !$this->has_included( $this->backup_paths ) && !$this->has_included( $this->backup_tables ) )
		{
			$Messages->add( T_( 'There is nothing to backup. Please select at least one option' ), 'error' );
			return false;
		}

		return true;
	}


	/**
	 * Start backup
	 */
	function start_backup()
	{
		global $basepath, $backup_path, $servertimenow;

		// Create current backup path
		$cbackup_path = $backup_path.date( 'Y-m-d-H-i-s', $servertimenow ).'/';

 		echo '<p>'.sprintf( T_('Starting backup to: &laquo;%s&raquo; ...'), $cbackup_path ).'</p>';
 		flush();

 		// Prepare backup directory
 		$success = prepare_maintenance_dir( $backup_path, true );

 		// Backup directories and files
		if( $success && $this->has_included( $this->backup_paths ) )
		{
			$backup_files_path = $this->pack_backup_files ? $cbackup_path : $cbackup_path.'files/';

			// Prepare files backup directory
			if( $success = prepare_maintenance_dir( $backup_files_path ) )
			{	// We can backup files
				$success = $this->backup_files( $backup_files_path );
			}
		}

		// Backup database
		if( $success && $this->has_included( $this->backup_tables ) )
		{
			$backup_tables_path = $this->pack_backup_files ? $cbackup_path : $cbackup_path.'db/';

			// Prepare database backup directory
			if( $success = prepare_maintenance_dir( $backup_tables_path ) )
			{	// We can backup database
				$success = $this->backup_database( $backup_tables_path );
			}
		}

		if( $success )
		{
			echo '<p>'.sprintf( T_('Backup complete. Directory: &laquo;%s&raquo;'), $cbackup_path ).'</p>';
			flush();

			return true;
		}

		@rmdir_r( $cbackup_path );
		return false;
	}


	/**
	 * Backup files
	 * @param string backup directory path
	 */
	function backup_files( $backup_dirpath )
	{
		global $basepath, $backup_paths;

		echo '<h4 style="color:green">'.T_( 'Creating folders/files backup...' ).'</h4>';
		flush();

		// Find included and excluded files

		$included_files = array();

		if( $root_included = $this->backup_paths['application_files'] )
		{
			$included_files = get_filenames( $basepath, true, true, true, false, true, true );
		}

		// Prepare included/excluded paths
		$excluded_files = array();

		foreach( $this->backup_paths as $name => $included )
		{
			foreach( $this->path_to_array( $backup_paths[$name]['path'] ) as $path )
			{
				if( $root_included && !$included )
				{
					$excluded_files[] = $path;
				}
				elseif( !$root_included && $included )
				{
					$included_files[] = $path;
				}
			}
		}

		// Remove excluded list from included list
		$included_files = array_diff( $included_files, $excluded_files );

		if( $this->pack_backup_files )
		{	// Create ZIPped backup
			$zip = new ZipArchive();
			$zip_filepath = $backup_dirpath.'files.zip';

			echo sprintf( T_( 'Archiving files to &laquo;<strong>%s</strong>&raquo;...' ), $zip_filepath ).'<br/>';
			flush();

			if ( $zip->open($zip_filepath, ZIPARCHIVE::CREATE ) !== TRUE)
			{
	    		echo '<p style="color:red">'.sprintf( T_( 'Unable to create &laquo;%s&raquo;' ), $zip_filepath ).'</p>';
	    		flush();

				return false;
			}

			// Add folders and files to ZIP archive.
			foreach( $included_files as $included_file )
			{
				$this->recurse_zip( no_trailing_slash( $included_file ), $zip, '', true );
			}

			$zip->close();
		}
		else
		{	// Copy directories and files to backup directory
			foreach( $included_files as $included_file )
			{
				$this->recurse_copy( no_trailing_slash( $basepath.$included_file ),
										no_trailing_slash( $backup_dirpath.$included_file ) );
			}
		}

		return true;
	}


	/**
	 * Backup database
	 *
	 * @param string backup directory path
	 */
	function backup_database( $backup_dirpath )
	{
		global $DB, $db_config, $backup_tables;

		echo '<h4 style="color:green">'.T_( 'Creating database backup...' ).'</h4>';
		flush();

		// Collect all included tables
		$ready_to_backup = array();
		foreach( $this->backup_tables as $name => $included )
		{
			if( $included )
			{
				$tables = aliases_to_tables( $backup_tables[$name]['table'] );
				if( is_array( $tables ) )
				{
					$ready_to_backup = array_merge( $ready_to_backup, $tables );
				}
				elseif( $tables == '*' )
				{
					foreach( $DB->get_results( 'SHOW TABLES', ARRAY_N ) as $row )
					{
						$ready_to_backup[] = $row[0];
					}
				}
				else
				{
					$ready_to_backup[] = $tables;
				}
			}
		}

		// Ensure there are no duplicated tables
		$ready_to_backup = array_unique( $ready_to_backup );

		// Exclude tables
		foreach( $this->backup_tables as $name => $included )
		{
			if( !$included )
			{
				$tables = aliases_to_tables( $backup_tables[$name]['table'] );
				if( is_array( $tables ) )
				{
					$ready_to_backup = array_diff( $ready_to_backup, $tables );
				}
				elseif( $tables != '*' )
				{
					$index = array_search( $tables, $ready_to_backup );
					if( $index )
					{
						unset( $ready_to_backup[$index] );
					}
				}
			}
		}

		// Create and save created SQL backup script
		$backup_sql_filename = 'db.sql';
		$backup_sql_filepath = $backup_dirpath.$backup_sql_filename;

		// Check if backup file exists
		if( file_exists( $backup_sql_filepath ) )
		{	// Stop tables backup, because backup file exists
			echo '<p style="color:red">'.sprintf( T_( 'Unable to write database dump. Database dump already exists: &laquo;%s&raquo;' ), $backup_sql_filepath ).'</p>';
			flush();

			return false;
		}

		$f = @fopen( $backup_sql_filepath , 'w+' );
		if( $f == false )
		{	// Stop backup, because it can't open backup file for writting
			echo '<p style="color:red">'.sprintf( T_( 'Unable to write database dump. Could not open &laquo;%s&raquo; for writing.' ), $backup_sql_filepath ).'</p>';
			flush();

			return false;
		}

		// Create and save created SQL backup script
		foreach( $ready_to_backup as $table )
		{
			// progressive display of what backup is doing
			echo sprintf( T_( 'Backing up table &laquo;<strong>%s</strong>&raquo; ...' ), $table ).'<br/>';
			flush();

			$row_table_data = $DB->get_row( 'SHOW CREATE TABLE '.$table, ARRAY_N );
			fwrite( $f, $row_table_data[1].";\n\n" );

			$values_list = array();
			foreach( $DB->get_results( 'SELECT * FROM '.$table, ARRAY_N ) as $row )
			{
				$values = '(';
				$num_fields = count( $row );
				for( $index = 0; $index < $num_fields; $index++ )
				{
					$row[$index] = ereg_replace("\n","\\n", addslashes( $row[$index] ) );

	            	if ( isset($row[$index]) )
	            	{
						$values .= '\''.$row[$index].'\'' ;
					}
					else
					{
						$values .= '\'\'';
					}

					if ( $index<( $num_fields-1 ) )
					{
						$values .= ',';
					}
	            }
	            $values_list[] = $values.')';
			}

			if( !empty( $values_list ) )
			{
				fwrite( $f, 'INSERT INTO '.$table.' VALUES '.implode( ',', $values_list ).";\n\n" );
			}

			unset( $values_list );

			// Flush the output to a file
			fflush( $f );
		}

		// Close backup file input stream
		fclose($f);

		if( $this->pack_backup_files )
		{	// Pack created backup SQL script
			$zip = new ZipArchive();
			$zip_filepath = $backup_dirpath.'db.zip';
			if ( $zip->open($zip_filepath, ZIPARCHIVE::CREATE ) !== TRUE)
			{
	    		echo '<p style="color:red">'.sprintf( T_( 'Unable to create &laquo;%s&raquo;' ), $zip_filepath ).'</p>';
	    		flush();

				return false;
			}

			$zip->addFile( $backup_dirpath.$backup_sql_filename, $backup_sql_filename );
			$zip->close();

			unlink( $backup_sql_filepath );
		}

		return true;
	}


	/**
	 * Copy directory recursively
	 * @param string source directory
	 * @param string destination directory
	 * @param array excluded directories
	 */
	function recurse_copy( $src, $dest, $root = true )
	{
		if( is_dir( $src ) )
		{
			$dir = opendir( $src );
			@mkdir( $dest );
			while( false !== ( $file = readdir( $dir ) ) )
			{
				if ( ( $file != '.' ) && ( $file != '..' ) )
				{
					$srcfile = $src.'/'.$file;
					if ( is_dir( $srcfile ) )
					{
						if( $root )
						{ // progressive display of what backup is doing
							echo sprintf( T_( 'Backing up &laquo;<strong>%s</strong>&raquo; ...' ), $srcfile ).'<br/>';
							flush();
						}
						$this->recurse_copy( $srcfile, $dest . '/' . $file, false );
					}
					else
					{ // Copy file
						copy( $srcfile, $dest.'/'. $file );
					}
				}
			}
			closedir( $dir );
		}
		else
		{
			copy( $src, $dest );
		}
	}


	/**
	 * Zip directory recursively
	 * @param string source directory
	 * @param object instance of ZipArchive class
	 * @param string prefix
	 */
	function recurse_zip( $path, &$zip, $prefix = '', $root = false )
	{
		global $basepath;

		if( is_dir( $basepath.$path ) )
		{
			if( $dir = opendir( $basepath.$path ) )
			{
				$path .= '/';

				$file_list = array();
				while ( ( $file = readdir( $dir ) ) !== false )
	            {
	            	if( ($file !== ".") && ($file !== ".."))
                    {	// Skip parent and root directories
	            		$file_list[] = $file;
                    }
	            }

	            if( count( $file_list ) == 0 )
	            {	// Create empty directory
	            	$zip->addEmptyDir( '/'.$path );
	            }

	            foreach( $file_list as $file )
	            {
	            	if( is_dir( $basepath.$path.$file ) )
	            	{
	            		if( $root )
						{ 	// progressive display of what backup is doing
							echo sprintf( T_( 'Backing up &laquo;<strong>%s</strong>&raquo; ...' ), $basepath.$path.$file ).'<br/>';
							flush();
						}
	            		$this->recurse_zip( $path.$file, $zip );
	            	}
	            	else
	            	{
	            		$this->recurse_zip( $path.$file, $zip, '/' );
	            	}
	            }
			}
		}
		else
		{
			$zip->addFile( $basepath.$path, $prefix.$path );
		}
	}


	/**
	 * Include all of the folders and tables to backup.
	 */
	function include_all()
	{
		global $backup_paths, $backup_tables;

		foreach( $backup_paths as $name => $settings )
		{
			if( array_key_exists( 'label', $settings ) && !is_null( $settings['label'] ) )
			{
				$this->backup_paths[$name] = true;
			}
		}

		foreach( $backup_tables as $name => $settings )
		{
			$this->backup_tables[$name] = true;
		}
	}


	/**
	 * Check has data list included directories/files or tables
	 * @param array list
	 * @return boolean
	 */
	function has_included( & $data_list )
	{
		foreach( $data_list as $included )
		{
			if( $included )
			{
				return true;
			}
		}
		return false;
	}


	/**
	 * Convert path to array
	 * @param mixed path
	 * @return array
	 */
	function path_to_array( $path )
	{
		if( is_array( $path ) )
		{
			return $path;
		}
		return array( $path );
	}
}


/*
 * $Log$
 * Revision 1.4  2009/10/21 14:27:39  efy-maxim
 * upgrade
 *
 * Revision 1.3  2009/10/20 14:38:54  efy-maxim
 * maintenance modulde: downloading - unpacking - verifying destination files - backing up - copying new files - upgrade database using regular script (Warning: it is very unstable version! Please, don't use maintenance modulde, because it can affect your data )
 *
 * Revision 1.2  2009/10/19 12:21:04  efy-maxim
 * system ZipArchive
 *
 * Revision 1.1  2009/10/18 20:15:51  efy-maxim
 * 1. backup, upgrade have been moved to maintenance module
 * 2. maintenance module permissions
 *
 */
?>