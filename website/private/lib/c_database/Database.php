<?php
/**
 * Singleton class to get a database connection.
 * Use this class to get a database connection, then perform queries on it.
 * The class uses the information provided in ini/database.ini and ini/root.ini.
 * TODO: move class into its own repository and make it a real singleton
 * 
 * @author Matl
 */
class Database {
  private static $connection;
  
  
  
  public static function getConnection() {
    if ( ! isset ( $connection ) ) {
      self::initialiseConnection();
    }
    return self::$connection;
  }
  
  
  
  /**
   * Gets the database name, host and the root's username and password from ini files.
   */
  public static function initialiseConnection() {
    $db = parse_ini_file( 'ini/database.ini' );
    $root = parse_ini_file( 'ini/root.ini' );
    self::$connection = new PDO( 'mysql:host=' . $db['host'] . ';dbname=' . $db['name'] . ';charset=utf8', $root['username'], $root['password'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION) );
    // TODO: remove array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
  }
  
  
  
  /**
   * This method performs a prepared statement on the database using the given prepared statement
   * and the values to feed it with.
   */
  public static function query( $preparedQuery, $values ) {
    $request = self::getConnection()->prepare( $preparedQuery );
    $success = $request->execute( $values );
    return $request;
  }
}
