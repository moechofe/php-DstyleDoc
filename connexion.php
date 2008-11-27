<?php

/**
 * Couche d'abstraction simple pour mysql.
 * Todo:
 *   - Gérer les query( 'insert', array( 1, 2 ) ) et les query( 'select', array( 1, 2 ) )
 */

/** Couche d'abstraction simple pour requête vers serveur mysql.
 *
 * Utilise les extensions PDO, MySQL ou MySQLi si elles sont disponibles.
 */
class mysql_connexion
{
  // {{{ $driver

  /**
   * Le nom de la classe du driver utilisé.
   */
  static public $driver = '';

  // }}}
  // {{{ get_driver()

  /**
   * Retourne l'instance d'un driver PDO, MySQL ou MySQLi.
   * Returns:
   *   mysql_connexion_driver = L'instance du driver.
   */
  static public function get_driver( $host, $user, $pass, $base )
  {
    if( ! self::$driver )
    {
      if( extension_loaded('pdo') and in_array( 'mysql', PDO::getAvailableDrivers() ) )
        self::$driver = 'mysql_connexion_pdo';
      elseif( extension_loaded('mysqli') )
        self::$driver = 'mysql_connexion_mysqli';
      elseif( extension_loaded('mysql') )
        self::$driver = 'mysql_connexion_mysql';
      if( is_null( self::$driver ) )
        throw new mysql_connexion_no_driver;
    }

    return new self::$driver( $host, $user, $pass, $base );
  }

  // }}}
}

// {{{ mysql_connexion_no_driver

/**
 * Exception lancé si aucun driver n'est disponible pour ouvrir la connexion.
 */
class mysql_connexion_no_driver extends RuntimeException
{
  public function __construct( $message )
  {
    parent::__construct( 'N\'a pas pu trouver de driver pour la connexion au server mysql.' );
  }
}

// }}}
// {{{ mysql_connexion_connect_error

/**
 * Exception lancé si la connexion vers le serveur de base de donnée à échouée.
 */
class mysql_connexion_connect_error extends Exception
{
  public function __construct( $message )
  {
    parent::__construct( 'N\'a pas pu se connecter : '.$message );
  }
}

// }}}
// {{{ mysql_connexion_query

/**
 * Exception lancé si une requête vers la base de donnée a provoqué une erreur.
 */
class mysql_connexion_query extends Exception
{
  public function __construct( $query, $message )
  {
    parent::__construct( 'Erreur avec la requete : '.$query." \n".$message );
  }
}

// }}}
// {{{ mysql_connexion_argument

/**
 * Exception lancé si les arguments passés sont mauvais.
 */
class mysql_connexion_argument extends InvalidArgumentException
{
  public function __construct( $method )
  {
    parent::__construct( 'Les arguments passés à la méthode : '.(string)$method.' ne sont pas correct.' );
  }
}

// }}}

/**
 * Classe de driver.
 *
 * Permet à partir d'une unique méthode d'envoyer des requêtes et de traiter les résultats.
 * En utilisant les jokers "?" et "!", les paramètres passés a la fonction seront ajouter dans la requête a l'endroit approprié.
 * Avec le joker "?", les paramètres seront echapé si ils sagit de chaîne de caractéres.
 * Le joker "!" permet de ne jamais échaper les paramètres.
 */
abstract class mysql_connexion_driver
{
  // {{{ __get()

  /**
   * Configure le mode de récupération des résultats.
   */
  public function __get( $property )
  {
    switch( $property )
    {
    case 'asso':
      $this->fetch_mode = ~( ~$this->fetch_mode | self::numerical_key );
      break;

    case 'num':
      $this->fetch_mode |= self::numerical_key;
      break;

    case 'field':
      $this->fetch_mode |= self::one_row;
      $this->fetch_mode |= self::one_field;
      break;

    case 'row':
      $this->fetch_mode |= self::one_row;
      $this->fetch_mode = ~( ~$this->fetch_mode | self::one_field );
      break;

    case 'col':
      $this->fetch_mode = ~( ~$this->fetch_mode | self::one_row );
      $this->fetch_mode |= self::one_field;
      break;

    case 'multi':
      $this->fetch_mode = ~( ~$this->fetch_mode | self::one_row );
      $this->fetch_mode = ~( ~$this->fetch_mode | self::one_field );
      break;

    case 'one':
      $this->fetch_mode |= self::one_by_one;
      break;

    case 'all':
      $this->fetch_mode = ~( ~$this->fetch_mode | self::one_by_one );
      break;

    case 'key':
      $this->fetch_mode |= self::first_field_for_key;
      break;

    case 'inc':
      $this->fetch_mode = ~( ~$this->fetch_mode | self::first_field_for_key );
      break;

    case 'keep':
      $this->fetch_mode |= self::keep_open;
      break;

    case 'close':
      $this->fetch_mode = ~( ~$this->fetch_mode | self::keep_open );
      break;

    case 'save':
      $this->set_fetch_mode();
      break;

    }

    return $this;
  }

  // }}}
  // {{{ query()

  /**
   * Execute une requete et retourne les resultats ou le last_insert_id.
   * Se connecte au serveur, contruit la requète avec les arguments, execute la requète, retourne les résultat et ferme la connexion.
   * Params:
   *   string $query = La requète avec des jokers "?" ou "!".
   *   mixed,... Les paramètres a binder avec les jokers de la requète.
   * Returns:
   *   array,boolean,string,integer = Le résultat de la requète.
   */
  abstract public function query();

  // }}}
  // {{{ $defaut_fetch_mode

  /**
   * Sauvegarde le fetch_mode par défaut.
   */
  protected $defaut_fetch_mode = 0x0;

  // }}}
  // {{{ set_fetch_mode()

  /**
   * Change le mode de recupèration des résultats par défaut.
   *
   * Params:
   *   integer $mode = Le mode par défaut.
   */
  public function set_fetch_mode( $mode = null )
  {
    if( ! is_null($mode) )
      $this->fetch_mode = (integer) $mode;
    $this->defaut_fetch_mode = $this->fetch_mode;
    return $this;
  }

  // }}}
  // {{{ get_fetch_mode()

  /**
   * Renvoie le mode de r�cup�ration des r�sultats par d�fault.
   */
  public function get_fetch_mode()
  {
    return $this->defaut_fetch_mode;
  }

  // }}}
  // {{{ $fetch_mode

  /**
   * Le mode de fetch utilis�.
   * @var integer
   */
  protected $fetch_mode = 0x0;

  // }}}
  // {{{ numerical_key

  /**
   * Retourne des clefs numeric plutot que des clefs associative.
   *
   * @var integer
   */
  const numerical_key = 0x1;

  // }}}
  // {{{ one_row

  /**
   * Retourne une seul ligne de r�sultat.
   * @var integer
   */
  const one_row = 0x2;

  // }}}
  // {{{ one_by_one

  /**
   * Indique de ne pas fetcher les donn�es.
   *
   * Permet de r�cup�rer les lignes une par une.
   * @var integer
   */
  const one_by_one = 0x4;

  // }}}
  // {{{ first_field_for_key

  /**
   * Indique de prendre la valeur du premier champ de chaque ligne et de l'utiliser en temps que clef du tableau représentant les données de chaques lignes du résutat.
   *
   * @var integer
   */
  const first_field_for_key = 0x8;

  // }}}
  // {{{ keep_open

  /**
   * Indique de ne pas refermer la connexion à chaque requête. Pratique pour les transactions.
   *
   * @var integer
   */
  const keep_open = 0x10;

  // }}}
  // {{{ one_field

  /**
   * Retourne la première colonne de la première ligne de résultat.
   * @var integer
   */
  const one_field = 0x20;

  // }}}
  // {{{ $host

  /**
   * Le host du serveur mysql
   * @var string
   */
  protected $host = null;

  // }}}
  // {{{ $user

  /**
   * L'utilisateur mysql
   * @var string
   */
  protected $user = null;

  // }}}
  // {{{ $pass

  /**
   * Le mot de passe de l'utilisateur mysql
   * @var string
   */
  protected $pass = null;

  // }}}
  // {{{ $base

  /**
   * Le nom de la base de donn�e
   * @var string
   */
  protected $base = null;

  // }}}
  // {{{ __construct()

  public function __construct( $host, $user, $pass, $base )
  {
    $this->host = $host;
    $this->user = $user;
    $this->pass = $pass;
    $this->base = $base;
  }

  // }}}
  // {{{ $query_attempt

  /**
   * Indique combien d'essaye de requ�tes sont effectu�es avant de retourner une erreur.
   *
   * @var integer
   */
  protected $query_attempt = 2;

  // }}}
  // {{{ $query_time_wait

  /**
   * indique combien de microsecond il est nescessaire d'attendre avant d'effectuer un autre essaye de requ�te.
   *
   * @var integer
   */
  protected $query_time_wait = 100;

  // }}}
  // {{{ fill()

  protected function fill( &$data, $row, $mode )
  {
    if( $mode & self::one_field and $mode & self::one_row )
      $data = array_shift($row);
    elseif( $mode & self::one_row )
      $data = $row;
    elseif( $mode & self::one_field and $mode & self::first_field_for_key )
    {
      list($key,$value) = array_slice(array_values($row),0,2);
      $data[$key] = $value;
    }
    elseif( $mode & self::first_field_for_key )
    {
      list($key) = array_slice(array_values($row),0,1);
      $data[$key] = $row;
    }
    elseif( $mode & self::one_field )
      $data[] = array_shift($row);
    else
      $data[] = $row;

    if( $mode & self::one_by_one )
      return false;
    else
      return true;
  }

  // }}}
}

/**
 * Driver de connexion pour mysqli.
 */
class mysql_connexion_mysqli extends mysql_connexion_driver
{
  // {{{ $link

  /**
   * L'instance de connexion MySQLi
   */
  protected $link = null;

  // }}}
  // {{{ $result

  /**
   * L'identifiant du jeu de r�sultat
   *
   * @var integer
   */
  protected $result = null;

  // }}}
  // {{{ bind_param()

  /**
   * Assigne un ? de la requete au param�tre suivant dans la liste.
   * @param array La valeur trouv� par preg_replace()
   * @param array La liste des param�tres
   * @param resource Un lien de connection mysql
   * @return string,numeric Le param�tre suivant dans la liste
   */
  protected function bind_param( $match, &$args, $link )
  {
    if( count($args) < 1 )
      return 'NULL';
    elseif( is_numeric( $arg = array_shift($args) ) )
      return $arg;
    elseif( is_null($arg) )
      return 'NULL';
    elseif( $match == '!' )
      return $arg;
    else
      return '\''.mysqli_escape_string($link,(string)$arg).'\'';
  }

  // }}}
  // {{{ query()

  public function query()
  {
    static $attempt = 1;

    if( ! $this->result )
    {
      $args = func_get_args();
      if( count($args) < 1 )
        throw new mysql_connexion_argument( __FUNCTION__ );
      $query = array_shift( $args );

      if( ! $this->link )
        if( ! $this->link = @mysqli_connect( $this->host, $this->user, $this->pass, $this->base ) )
          throw new mysql_connexion_connect_error(mysqli_connect_error());

      $link = $this->link;
      $query = preg_replace( '/((?<!\w)\?(?!\w)|(?<!\w)!(?!\w)|\b!(?!\w)|(?<!\w)!\b)/e', "self::bind_param('\\1', \$args, \$link)", $query );

      $this->result = @mysqli_query($this->link, $query);
    }

    $data = false;

    if( $this->result )
    {
      $data = array();
      if( is_bool($this->result) )
      {
        if( ! $data = mysqli_insert_id($this->link) )
          $data = true;
      }
      elseif( $this->fetch_mode & self::numerical_key )
      {
	while( $row = mysqli_fetch_row($this->result) )
          if( ! $this->fill( $data, $row, $this->fetch_mode ) )
	    break;
      }
      else
      {
        while( $row = mysqli_fetch_assoc($this->result) )
          if( ! $this->fill( $data, $row, $this->fetch_mode ) )
            break;
      }
    }
    elseif( ++$attempt > (integer)$this->query_attempt )
    {
      $attempt = 1;
      throw new mysql_connexion_query($query, mysqli_error($this->link));
    }
    else
    {
      usleep( (integer)$this->query_time_wait );
      $args = func_get_args();
      return call_user_func_array( array($this,'query'), $args );
    }

    if( $this->fetch_mode & self::one_by_one )
    {
      if( $this->link )
      {
	mysqli_close($this->link);
	$this->link = null;
      }
      if( ! $data )
      {
        $this->result = null;
	$this->fetch_mode = $this->defaut_fetch_mode;
      }
      return array_shift($data);
    }
    elseif( $this->fetch_mode & self::keep_open )
    {
      $this->result = null;
      $this->fetch_mode = $this->defaut_fetch_mode;
      return $data;
    }

    mysqli_close($this->link);
    $this->link = null;
    $this->result = null;
    $this->fetch_mode = $this->defaut_fetch_mode;

    return $data;
  }

  // }}}
}

/**
 * Driver de connexion pour MySQL
 */
class mysql_connexion_mysql extends mysql_connexion_driver
{
  // {{{ $link

  /**
   * La resource de connexion MySQL.
   * Type:
   *   resource
   */
  protected $link = null;

  // }}}
  // {{{ $result

  /**
   * La resource des résultats MySQL.
   * Type:
   *   resource
   */
  protected $result = null;

  // }}}
  // {{{ bind_param()

  /**
   * Assigne le prochain joker de la requète au paramètre suivant dans la liste.
   * Params:
   *   array $match = La valeur trouvé par preg_replace().
   *   array $args = La liste des paramètres.
   *   resource $link = Le lien de la connexion MySQL.
   * Returns:
   *   string,numeric = Le paramètre suivant dans la liste à remplacer.
   */
  static protected function bind_param( $match, &$args, &$link )
  {
    if( count($args) < 1 )
      return 'NULL';
    elseif( is_numeric( $arg = array_shift($args) ) )
      return $arg;
    elseif( is_null($arg) )
      return 'NULL';
    elseif( $match == '!' )
      return $arg;
    else
      return '\''.mysql_real_escape_string((string)$arg, $link).'\'';
  }

  // }}}
  // {{{ query()

  public function query()
  {
    static $attempt = 1;

    if( ! $this->result )
    {
      $args = func_get_args();
      if( count($args) < 1 )
        throw new mysql_connexion_argument( __FUNCTION__ );
      $query = array_shift( $args );

      if( ! $this->link )
      {
        if( ! $this->link = @mysql_connect( $this->host, $this->user, $this->pass ) )
          throw new mysql_connexion_connect_error(mysql_error());
        if( ! @mysql_select_db( $this->base, $this->link ) )
	  throw new mysql_connexion_connect_error(mysql_error());
      }

      $link = $this->link;
      $query = preg_replace( '/((?<!\w)\?(?!\w)|(?<!\w)!(?!\w)|\b!(?!\w)|(?<!\w)!\b)/e', "self::bind_param('\\1', \$args, \$link)", $query );

      $this->result = @mysql_query($query, $this->link);
    }

    $data = false;

    if( $this->result )
    {
      $data = array();
      if( is_bool($this->result) )
      {
        if( ! $data = @mysql_insert_id($this->link) )
          $data = true;
      }
      elseif( $this->fetch_mode & self::numerical_key )
      {
        while( $row = mysql_fetch_row($this->result) )
          if( ! $this->fill( $data, $row, $this->fetch_mode ) )
            break;
      }
      else
      {
        while( $row = mysql_fetch_assoc($this->result) )
          if( ! $this->fill( $data, $row, $this->fetch_mode ) )
            break;
      }
    }
    elseif( ++$attempt > (integer)$this->query_attempt )
    {
      $attempt = 1;
      throw new mysql_connexion_query($query, mysql_error($this->link));
    }
    else
    {
      usleep( (integer)$this->query_time_wait );
      $args = func_get_args();
      return call_user_func_array( array($this,'query'), $args );
    }

    if( $this->fetch_mode & self::one_by_one )
    {
      if( $this->link )
      {
	mysql_close($this->link);
	$this->link = null;
      }
      if( ! $data )
      {
        $this->result = null;
	$this->fetch_mode = $this->defaut_fetch_mode;
      }
      return array_shift($data);
    }
    elseif( $this->fetch_mode & self::keep_open )
    {
      $this->fetch_mode = $this->defaut_fetch_mode;
      $this->result = null;
      return $data;
    }

    mysql_close($this->link);
    $this->link = null;
    $this->result = null;
    $this->fetch_mode = $this->defaut_fetch_mode;

    return $data;
  }

  // }}}
}

/**
 * Driver de connexion pour PDO.
 */
class mysql_connexion_pdo extends mysql_connexion_driver
{
  // {{{ bind_param()

  /**
   * Assigne le prochain joker de la requète au paramètre suivant dans la liste.
   * Params:
   *   array $match = La valeur trouvé par preg_replace().
   *   array $args = La liste des paramètres.
   * Returns:
   *   string,numeric = Le paramètre suivant dans la liste à remplacer.
   */
  static protected function bind_param( $match, &$args, &$newargs )
  {
    if( $match != '!' )
    {
      array_push($newargs, array_shift($args));
      return '?';
    }
    elseif( count($args) < 1 )
      return 'NULL';
    elseif( is_numeric( $arg = array_shift($args) ) )
      return $arg;
    elseif( is_null($arg) )
      return 'NULL';
    else
      return $arg;
  }

  // }}}
  // {{{ $statement

  /**
   * Une r�f�rence vers l'objet de la requ�te PDO.
   */
  protected $statement = null;

  // }}}
  // {{{ $result

  /**
   * Une r�f�rence vers l'objet des r�sultats PDO.
   *
   * @var PDOStatement
   */
  protected $result = null;

  // }}}
  // {{{ query()

  public function query()
  {
    static $attempt = 1;

    try
    {

    if( ! $this->result )
    {

      $args = func_get_args();
      if( count($args) < 1 )
        throw new mysql_connexion_argument( __FUNCTION__ );
      $query = array_shift( $args );

      if( ! $this->statement )
      {
        $this->statement = new PDO( 'mysql:dbname='.$this->base.';host='.$this->host, $this->user, $this->pass );
	$this->statement->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT );
	$this->statement->setAttribute( PDO::ATTR_STRINGIFY_FETCHES, false );
      }

      $newargs = array();
      $query = preg_replace( '/((?<!\w)\?(?!\w)|(?<!\w)!(?!\w)|\b!(?!\w)|(?<!\w)!\b)/e', "self::bind_param('\\1', \$args, \$newargs)", $query );

      if( $this->result = $this->statement->prepare($query) )
      {
	if( ! $this->result->execute($newargs) )
	  $this->result = false;
      }
    }

    $data = false;

    if( $this->result )
    {
      $data = array();
      if( is_bool($this->result) )
      {
        if(  ! $data = $this->statement->lastInsertId() )
          $data = true;
      }
      elseif( $this->fetch_mode & self::numerical_key )
      {
        while( $row = $this->result->fetch(PDO::FETCH_NUM) )
          if( ! $this->fill( $data, $row, $this->fetch_mode ) )
            break;
      }
      else
      {
        while( $row = $this->result->fetch(PDO::FETCH_ASSOC) )
          if( ! $this->fill( $data, $row, $this->fetch_mode ) )
            break;
      }
    }
    elseif( ++$attempt > (integer)$this->query_attempt )
    {
      $attempt = 1;
      throw new mysql_connexion_query($query, join("\n",$this->statement->errorInfo()));
    }
    else
    {
      usleep( (integer)$this->query_time_wait );
      $args = func_get_args();
      return call_user_func_array( array($this,'query'), $args );
    }

    if( $this->fetch_mode & self::one_by_one )
    {
      if( $this->statement ) $this->statement = null;
      if( ! $data )
      {
	$this->result = null;
	$this->fetch_mode = $this->defaut_fetch_mode;
      }
      return array_shift($data);
    }
    elseif( $this->fetch_mode & self::keep_open )
    {
      $this->fetch_mode = $this->defaut_fetch_mode;
      $this->result = null;
      return $data;
    }

    $this->statement = null;
    $this->result = null;
    $this->fetch_mode = $this->defaut_fetch_mode;

    return $data;

    }
    catch( PDOException $e )
    {
      throw new mysql_connexion_connect_error($e->getMessage());
    }

  }

  // }}}
}

?>
