<?php

/**
 * Couche d'abstraction simple pour mysql.
 *
 * @package connexion
 * @TODO faire en sorte que les requetes accept le parametre ! en plus mais pas \!
 */

/**
 * Couche d'abstraction simple pour requête vers serveur mysql.
 *
 * Utilise les extensions mysql ou mysqli si cette dernière est disponible pour effectuer
 * les requêtes.
 *
 * @package connexion
 */
class mysql_connexion
{
  // {{{ $mailto

  /**
   * Liste de adresse de destination des alertes email.
   * @var string
   */
  static public $mailto = 'mm@orbus.fr';

  // }}}
  // {{{ $driver

  /**
   * Le nom de la classe du driver utilisé.
   * @var string
   */
  static public $driver = null;

  // }}}
  // {{{ get_driver()

  static public function get_driver( $host, $user, $pass, $base )
  {
    if( is_null( self::$driver ) )
    {
      if( isset($_REQUEST['enable_pdo']) and extension_loaded('pdo') and in_array( 'mysql', PDO::getAvailableDrivers() ) )
        self::$driver = 'mysql_connexion_pdo';
      if( extension_loaded('mysqli') )
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
 * @package connexion
 * @subpackage exception
 */
class mysql_connexion_no_driver extends RuntimeException
{
  public function __construct( $message )
  {
    parent::__construct( 'N\'a pas pu trouvé de driver pour la connexion à mysql.' );
  }
}

// }}}
// {{{ mysql_connexion_connection_error

/**
 * Exception lancé si la connexion vers le serveur de base de donnée
 * @package connexion
 * @subpackage exception
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
 * Exception lancé si une requête vers la base de donnée à provoqué une erreur
 * @package connexion
 * @subpackage exception
 */
class mysql_connexion_query extends Exception
{
  public function __construct( $query, $message )
  {
    parent::__construct( 'Erreur avec la requ&ecirc;te : '.$query." \n".$message );
  }
}

// }}}
// {{{ mysql_connexion_argument

/**
 * Exception lancé si les arguments passé au script sont mauvais
 * @package connexion
 * @subpackage exception
 */
class mysql_connexion_argument extends InvalidArgumentException
{
  public function __construct( $method )
  {
    parent::__construct( 'Les arguments passés à la méthode : '.(string)$method.' ne sont pas correct.' );
  }
}

// }}}
// {{{ mysql_connexion_bad_driver

/**
 * Exception lancé si le driver n'est pas conforme.
 * @package connexion
 * @subpackage exception
 */
class mysql_connexion_bad_driver extends RuntimeException
{
  public function __construct( $driver )
  {
    parent::__construct( 'Le driver '.(string)$driver.' n\'est pas un driver valide.' );
  }
}

// }}}

/**
 * Classe de driver.
 *
 * Permet à partir d'une unique méthode d'envoyer des requêtes et de traiter les résultats.
 * En utilisant les jokers "?" et "!", les paramètres passés à la fonction seront ajouter dans la requête à l'endroit approprié.
 * Avec le joker "?", les paramètres seront echapé si ils sagit de chaîne de caractères.
 * Le joker "!" permet de ne jamais échaper les paramètres.
 *
 * <code>
 * <?php
 *   $mysql->query( 'SELECT 1' );
 * ?>
 * </code>
 *
 * <code>
 * <?php
 *   $mysql->query( 'SELECT * FROM table WHERE id = ?', (integer)$_GET['id'] );
 * ?>
 * </code>
 *
 * <code>
 * <?php
 *   $mysql->query( 'INSERT !table (name) VALUES(?),(?)', $prefix, (string)$value[1], (string)$value[2] );
 * ?>
 * </code>
 *
 * Récupérer les résultats :
 *
 * <code>
 * <?php
 *   $last_insert_id = $mysql->query( 'INSERT...' ... );
 * ?>
 * </code>
 *
 * <code>
 * <?php
 *   $data = $mysql->query( 'SELECT * FROM table' );
 * ?>
 * </code>
 *
 * Changer le mode de récupération des résultats :
 *
 * <code>
 * <?php
 *   $mysql->row->query( 'SELECT * FROM table LIMIT 1' );
 * ?>
 * </code>
 *
 * <code>
 * <?php
 *   $mysql->asso->multi->query( 'SELECT id, name FROM table' );
 * ?>
 * </code>
 *
 * <code>
 * <?php
 *   while( $row = $mysql->one->query( 'SELECT * FROM table' ) )
 *   null;
 * ?>
 * </code>
 */
abstract class mysql_connexion_driver
{
  // {{{ __get()

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

    case 'row':
      $this->fetch_mode |= self::one_row;
      break;

    case 'multi':
      $this->fetch_mode = ~( ~$this->fetch_mode | self::one_row );
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

    case 'col':
      $this->fetch_mode = ~( ~$this->fetch_mode | self::one_row );
      $this->fetch_mode |= self::one_field;
      break;

    case 'field':
      $this->fetch_mode |= self::one_field;
      break;

    case 'fields':
      $this->fetch_mode = ~( ~$this->fetch_mode | self::one_field );
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
   * Execute une requete et retourne les resultats ou last_insert_id
   * Se connect au serveur, contruit la requête avec les arguments, execute la requête, retourne les résultat.
   *
   * Deux syntaxes sont possible :
   * <code>
   *   self::query( $statement );
   *   self::query( $prepare, $arg1, $arg2, ... );
   * </code>
   *
   * La deuxième syntaxe s'assure d'echapper les chaines de caractère des paramètres et de les entourer par des guillemets. Pour cela, la requête doit contenir des '?' qui seront remplacés par les valeurs des arguments.
   *
   * Effectue une requête sans résultat :
   * <code>
   * <?php
   *   self::query( 'INSERT table SET field = ?', (integer) $value );
   *   // executera <pre>INSERT table SET field = 3</pre>
   *
   *   self::query( 'INSERT table SET field = ?', (string) $value );
   *   // executera <pre>INSERT table SET field = '3'</pre>
   * ?>
   * </code>
   *
   * Si une requête de type insert ou update est envoyé, le last_insert_id sera retourné. Si aucun id à été modifier, true sera retourné.
   *
   * @param string La requête avec des "?"
   * @param mixed,... Les paramètres a binder avec les "?" de la requête.
   * @return array,boolean,string,integer
   */
  abstract public function query();

  // }}}
  // {{{ $defaut_fetch_mode

  /**
   * Sauvegarde le fetch_mode par défaut.
   *
   * @var integer
   */
  protected $defaut_fetch_mode = 0x0;

  // }}}
  // {{{ set_fetch_mode()

  /**
   * Change le mode de recupération des résultats par défaut.
   *
   * @param integer
   * @return mysql_connexion_driver
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
   * Renvoie le mode de récupération des résultats par défault.
   */
  public function get_fetch_mode()
  {
    return $this->defaut_fetch_mode;
  }

  // }}}
  // {{{ $fetch_mode

  /**
   * Le mode de fetch utilisé.
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
   * Retourne une seul ligne de résultat.
   * @var integer
   */
  const one_row = 0x2;

  // }}}
  // {{{ one_by_one

  /**
   * Indique de ne pas fetcher les données.
   *
   * Permet de récupérer les lignes une par une.
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
   * Le nom de la base de donnée
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
   * Indique combien d'essaye de requêtes sont effectuées avant de retourner une erreur.
   *
   * @var integer
   */
  protected $query_attempt = 2;

  // }}}
  // {{{ $query_time_wait

  /**
   * indique combien de microsecond il est nescessaire d'attendre avant d'effectuer un autre essaye de requête.
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
      list($key) = array_slice(array_values($row),0,1);
      $data[$key] = $key;
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
 * @package connexion
 * @subpackage driver
 */
class mysql_connexion_mysqli extends mysql_connexion_driver
{
  // {{{ $result

  /**
   * L'identifiant du jeu de résultat
   *
   * @var integer
   */
  protected $result = null;

  // }}}
  // {{{ bind_param()

  /**
   * Assigne un ? de la requete au paramètre suivant dans la liste.
   * @param array La valeur trouvé par preg_replace()
   * @param array La liste des paramètres
   * @param resource Un lien de connection mysql
   * @return string,numeric Le paramètre suivant dans la liste
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

      if( ! $link = @mysqli_connect( $this->host, $this->user, $this->pass, $this->base ) )
        throw new mysql_connexion_connect_error(mysqli_connect_error());

      $query = preg_replace( '/((?<!\w)\?(?!\w)|(?<!\w)!(?!\w)|\b!(?!\w)|(?<!\w)!\b)/e', "self::bind_param('\\1', \$args, \$link)", $query );

      $this->result = @mysqli_query($link, $query);
    }

    $data = false;

    if( $this->result )
    {
      $data = array();
      if( is_bool($this->result) )
      {
        if( ! $data = mysqli_insert_id($link) )
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
      throw new mysql_connexion_query($query, mysqli_error($link));
    }
    else
    {
      usleep( (integer)$this->query_time_wait );
      $args = func_get_args();
      return call_user_func_array( array($this,'query'), $args );
    }

    if( $this->fetch_mode & self::one_by_one )
      return $data;
    elseif( $this->fetch_mode & self::keep_open )
    {
      $this->result = null;
      return $data;
    }

    mysqli_close($link);
    $this->result = null;
    $this->fetch_mode = $this->defaut_fetch_mode;

    return $data;
  }

  // }}}
}

/**
 * Driver de connexion pour mysql
 * @package connexion
 * @subpackage driver
 */
class mysql_connexion_mysql extends mysql_connexion_driver
{
  // {{{ $resource

  /**
   * Une référence vers la ressource mysql
   *
   * @var resource
   */
  protected $resource = null;

  // }}}
  // {{{ bind_param()

  /**
   * Assigne un ? de la requete au paramètre suivant dans la liste.
   * @param array La valeur trouvé par preg_replace()
   * @param array La liste des paramètres
   * @param resource Un lien de connection mysql
   * @return string,numeric Le paramètre suivant dans la liste
   */
  static protected function bind_param( $match, &$args, $link )
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

    if( ! $this->resource )
    {

      $args = func_get_args();
      if( count($args) < 1 )
        throw new mysql_connexion_argument( __FUNCTION__ );
      $query = array_shift( $args );

      if( ! $link = @mysql_connect( $this->host, $this->user, $this->pass ) )
        throw new mysql_connexion_connect_error(mysql_error());
      if( ! @mysql_select_db( $this->base, $link ) )
        throw new mysql_connexion_connect_error(mysql_error());

      $query = preg_replace( '/((?<!\w)\?(?!\w)|(?<!\w)!(?!\w)|\b!(?!\w)|(?<!\w)!\b)/e', "self::bind_param('\\1', \$args, \$link)", $query );

      $this->resource = @mysql_query($query, $link);

    }

    $data = false;

    if( $this->resource )
    {
      $data = array();
      if( is_bool($this->resource) )
      {
        if( ! $data = @mysql_insert_id($link) )
          $data = true;
      }
      elseif( $this->fetch_mode & self::numerical_key )
      {
        while( $row = mysql_fetch_row($this->resource) )
          if( ! $this->fill( $data, $row, $this->fetch_mode ) )
            break;
      }
      else
      {
        while( $row = mysql_fetch_assoc($this->resource) )
          if( ! $this->fill( $data, $row, $this->fetch_mode ) )
            break;
      }
    }
    elseif( ++$attempt > (integer)$this->query_attempt )
    {
      $attempt = 1;
      throw new mysql_connexion_query($query, mysql_error($link));
    }
    else
    {
      usleep( (integer)$this->query_time_wait );
      $args = func_get_args();
      return call_user_func_array( array($this,'query'), $args );
    }

    if( $this->fetch_mode & self::one_by_one )
      return $data;
    elseif( $this->fetch_mode & self::keep_open )
    {
      $this->result = null;
      return $data;
    }

    mysql_close($link);
    $this->resource = null;
    $this->fetch_mode = $this->defaut_fetch_mode;

    return $data;
  }

  // }}}
}

/**
 * Driver de connexion pour pdo
 * @package connexion
 * @subpackage driver
 */
class mysql_connexion_pdo extends mysql_connexion_driver
{
  // {{{ $result

  /**
   * Une référence vers l'obejct des résultats PDO.
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

    if( ! $this->resource )
    {

      $args = func_get_args();
      if( count($args) < 1 )
        throw new mysql_connexion_argument( __FUNCTION__ );
      $query = array_shift( $args );

      $link = new PDO( 'mysql:dbname='.$this->base.';host='+$this->host, $this->user, $this->pass );
      $link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT );

      if( $this->result = $link->prepare($query) )
        $this->result->execute($args);

    }

    $data = false;

    if( $this->result )
    {
      $data = array();
      if( is_bool($this->result) )
      {
        if(  ! $data = $link->lastInsertId() )
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
      throw new mysql_connexion_query($query, join("\n",$link->errorInfo));
    }
    else
    {
      usleep( (integer)$this->query_time_wait );
      $args = func_get_args();
      return call_user_func_array( array($this,'query'), $args );
    }

    if( $this->fetch_mode & self::one_by_one )
      return $data;
    elseif( $this->fetch_mode & self::keep_open )
    {
      $this->result = null;
      return $data;
    }

    mysql_close($link);
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
