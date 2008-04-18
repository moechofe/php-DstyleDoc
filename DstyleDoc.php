<?php

// {{{ properties class

class DstyleDoc_Properties
{
  protected function __get( $property )
  {
    if( ! is_callable( array($this,'get_'.(string)$property) ) )
      throw new BadPropertyException($this, (string)$property);

    return call_user_func( array($this,'get_'.(string)$property) );
  }

  protected function __set( $property, $value )
  {
    if( ! is_callable( array($this,'set_'.(string)$property) ) )
      throw new BadPropertyException($this, (string)$property);

    call_user_func( array($this,'set_'.(string)$property), $value );
  }

  protected function __isset( $property )
  {
    if( ! is_callable( array($this,'isset_'.(string)$property) ) )
      throw new BadPropertyException($this, (string)$property);

    return call_user_func( array($this,'isset_'.(string)$property) );
  }

  protected function __unset( $property )
  {
    if( ! is_callable( array($this,'unset_'.(string)$property) ) )
      throw new BadPropertyException($this, (string)$property);

    call_user_func( array($this,'unset_'.(string)$property) );
  }
}

// }}}

class DstyleDoc extends DstyleDoc_Properties
{
  // {{{ $sources

  protected $_sources = array();

  protected function set_source( $files )
  {
    if( file_exists((string)$files) and is_file((string)$files) and is_readable((string)$files) )
      $this->_sources[] = (string)$files;
    elseif( is_array($files) or $files instanceof Iterator )
      foreach( $files as $file )
        $this->source = $file;
  }
  
  protected function get_sources()
  {
    return $this->_sources;
  }

  // }}}
  // {{{ analyse_all()

  protected function analyse_all( DstyleDoc_Converter $converter )
  {
    foreach( $this->sources as $file )
      $this->analyse_file( $converter, $file );
  }

  // }}}
  // {{{ analyse_file()

  protected function analyse_file( DstyleDoc_Converter $converter, $file )
  {
    $line = 1;
    $current = 0;
    $doc = '';
    foreach( token_get_all(file_get_contents($file)) as $token )
    {
      if( is_array($token) )
        list( $token, $source, $line ) = $token;
      else
        list( $token, $source, $line ) = array( 0, $token, $line );

      // skip T_WHITESPACE for speed up
      if( $token === T_WHITESPACE )
        continue;

      $call = token_name($token);
      if( substr($call,0,2)!=='T_' ) $call = 'T_'.$call;

      static $f = 0;
      $ff = (++$f%2)?'BurlyWood':'Goldenrod';
      $s = htmlentities($source); $c = get_class($current);
      echo <<<HTML
<div style='clear:left;float:left;background:Wheat;padding:1px 3px'>$call</div>
<div style='float:right;color:white;background:Brown;padding:1px 3px'>{$c}</div>
<div style='background:{$ff};color:SaddleBrown;padding:1px 3px;'>{$s}</div>
<div style='clear:both'></div>
HTML;

      // processing token
      $current = $this->$call( $converter, $current, $source, $file, $line );

      //var_dump( $current );
    }
  }

  // }}}
  // {{{ token functions

  protected function t_open_tag( DstyleDoc_Converter $converter, $current, $source, $file )
  {
    if( $current instanceof DstyleDoc_Token )
      return $current;
    else
      return DstyleDoc_Token_File::hie( $file );
  }

  protected function t_whitespace( DstyleDoc_Converter $converter, $current )
  {
    if( $current instanceof DstyleDoc_Token )
      return $current;
    else
      return DstyleDoc_Token_Null::hie();
  } 

  protected function t_doc_comment( DstyleDoc_Converter $converter, $current, $source, $file, $line )
  {
    return DstyleDoc_Token_Doc_Comment::hie( $current, $source, $line );
  }

  protected function t_class( DstyleDoc_Converter $converter, $current, $source, $file, $line )
  {
    return DstyleDoc_Token_Class::hie( $current, $file, $line );
  }

  protected function t_string( DstyleDoc_Converter $converter, $current, $source )
  {
    return DstyleDoc_Token_String::hie( $current, $source );
  }

  protected function t_unknown( DstyleDoc_Converter $converter, $current, $source, $file, $line )
  {
    return DstyleDoc_Token_Unknown::hie( $converter, $current, $source, $line );
  }

  protected function t_close_tag( DstyleDoc_Converter $converter, $current )
  {
    if( $current instanceof DstyleDoc_Token )
      return $current;
    else
      return DstyleDoc_Token_Null::hie();
  }

  protected function t_extends( DstyleDoc_Converter $converter, $current, $file, $line )
  {
    return DstyleDoc_Token_Extends::hie( $current, $line );
  }

  protected function t_implements( DstyleDoc_Converter $converter, $current, $file, $line )
  {
    return DstyleDoc_Token_Implements::hie( $current, $line );
  }

  protected function t_interface( DstyleDoc_Converter $converter, $current, $source, $file, $line )
  {
    return DstyleDoc_Token_Interface::hie( $current, $file, $line );
  }

  protected function t_function( DstyleDoc_Converter $converter, $current, $source, $file, $line )
  {
    if( $current instanceof DstyleDoc_Token_Modifier )
    {
      if( $current->class )
        return DstyleDoc_Token_Method::hie( $current, $line );
      else
        return DstyleDoc_Token_Function::hie( $current, $line );
    }
    elseif( $current instanceof DstyleDoc_Token_Class )
      return DstyleDoc_Token_Method::hie( $current, $line );
    else
      return DstyleDoc_Token_Function::hie( $current, $line );
  }

  protected function t_variable( DstyleDoc_Converter $converter, $current, $source, $file, $line )
  {
    return DstyleDoc_Token_Variable::hie( $current, $source, null, $line );
  }

  protected function t_constant_encapsed_string( DstyleDoc_Converter $converter, $current, $source )
  {
    return DstyleDoc_Token_String::hie( $current, substr($source,1,-1) );
  }

  protected function t_lnumber( DstyleDoc_Converter $converter, $current, $source )
  {
    return DstyleDoc_Token_Lnumber::hie( $current, $source );
  }

  protected function t_throw( DstyleDoc_Converter $converter, $current, $file, $line )
  {
    return DstyleDoc_Token_Throw::hie( $current, $line );
  }

  protected function t_new( DstyleDoc_Converter $converter, $current )
  {
    if( $current instanceof DstyleDoc_Token )
      return $current;
    else
      return DstyleDoc_Token_Null::hie();
  }

  protected function t_object_operator( DstyleDoc_Converter $converter, $current )
  {
    if( $current instanceof DstyleDoc_Token )
      return $current;
    else
      return DstyleDoc_Token_Null::hie();
  }

  protected function t_abstract( DstyleDoc_Converter $converter, $current, $source, $file, $line )
  {
    return DstyleDoc_Token_Modifier::hie( $current, $source, $line );
  }

  protected function t_static( DstyleDoc_Converter $converter, $current, $source, $file, $line )
  {
    return DstyleDoc_Token_Modifier::hie( $current, $source, $line );
  }

  protected function t_final( DstyleDoc_Converter $converter, $current, $source, $file, $line )
  {
    return DstyleDoc_Token_Modifier::hie( $current, $source, $line );
  }

  protected function t_public( DstyleDoc_Converter $converter, $current, $source, $file, $line )
  {
    return DstyleDoc_Token_Modifier::hie( $current, $source, $line );
  }

  protected function t_protected( DstyleDoc_Converter $converter, $current, $source, $file, $line )
  {
    return DstyleDoc_Token_Modifier::hie( $current, $source, $line );
  }

  protected function t_private( DstyleDoc_Converter $converter, $current, $source, $file, $line )
  {
    return DstyleDoc_Token_Modifier::hie( $current, $source, $line );
  }

  protected function t_require_once( DstyleDoc_Converter $converter, $current )
  {
    return DstyleDoc_Token_Dependency::hie( $current );
  }

  // }}}
  // {{{ source()

  public function source()
  {
    $args = func_get_args();
    foreach( $args as $arg )
      $this->source = $arg;
    return $this;
  }

  // }}}
  // {{{ convert_with()

  public function convert_with( DstyleDoc_Converter $converter )
  {
    $this->analyse_all( $converter );
    $converter->convert_all();
    return $this;
  }

  // }}}
  // {{{ hie()

  static public function hie()
  {
    return new self;
  }

  // }}}
}

/**
 * Classe de base pour le tokens.
 */
class DstyleDoc_Token extends DstyleDoc_Properties
{
}

/**
 * Interface de conversion en element.
 */
interface DstyleDoc_Token_Elementable
{
  // {{{ to()

  function to( DstyleDoc_Converter $converter );

  // }}}
}

/**
 * Classes de token de type tout le reste.
 */
class DstyleDoc_Token_Unknown extends DstyleDoc_Token
{
  // {{{ hie()

  static public function hie( DstyleDoc_Converter $converter, DstyleDoc_Token $current, $source, $line )
  {
    switch( $source )
    {
    case ',':
      if( $current instanceof DstyleDoc_Token_Variable )
        return DstyleDoc_Token_Function_Tuple::hie( $current->function, $line );
      break;

    case '(':
      if( $current instanceof DstyleDoc_Token_Function and ! $current->is_tupled )
        return DstyleDoc_Token_Function_Tuple::hie( $current, $line );
      elseif( $current instanceof DstyleDoc_Token_Method and ! $current->is_tupled )
        return DstyleDoc_Token_Method_Tuple::hie( $current, $line );
      elseif( $current instanceof DstyleDoc_Token_Function or $current instanceof DstyleDoc_Token_Method )
        return $current;
      break;

    case ')':
      if( $current instanceof DstyleDoc_Token_Variable )
        return $current->function->tupled;
      else
        return $current;
      break;

    case '{':
      if( $current instanceof DstyleDoc_Token_Method )
        return $current->class;
      elseif( $current instanceof DstyleDoc_Class_Ref )
        return $current->class;
      elseif( $current instanceof DstyleDoc_Token_Class )
        return $current;
      break;

    case '}':
      var_dump( $current );
      if( $current instanceof DstyleDoc_Token_Function )
        return $current->to( $converter );
      elseif( $current instanceof DstyleDoc_Token_Class )
        return $current->to( $converter );
      elseif( $current instanceof DstyleDoc_Token_Interface )
        return $current->to( $converter );
      break;
    }
     
    if( $current instanceof DstyleDoc_Token )
      return $current;

    else
      return DstyleDoc_Token_Null::hie();
  }

  // }}}
}

interface DstyleDoc_Token_Valueable
{
  function set_value( $string );
  function get_value();
}

/**
 * Classe de token de type rien.
 * Todo: a virer
 */
class DstyleDoc_Token_Null extends DstyleDoc_Token
{
  // {{{ hie()

  static public function hie()
  {
    return new self;
  }

  // }}}
}

/**
 * Classe de token de type commentaire de documentation.
 */
class DstyleDoc_Token_Doc_Comment extends DstyleDoc_Class_Ref
{
  // {{{ $source

  protected $_source = '';

  protected function set_source( $source )
  {
    $this->_source = (string)$source;
  }
  
  protected function get_source()
  {
    return $this->_source;
  }

  // }}}
  // {{{ __construct()

  protected function __construct( $source, $line )
  {
    $this->source = $source;
    $this->line = $line;
  }

  // }}}
  // {{{ hie()

  static public function hie( DstyleDoc_Token $current, $source, $line )
  {
    if( $current instanceof DstyleDoc_Token_Doc_Comment and $current->file instanceof DstyleDoc_Token_File )
    {
      $current->file->doc_comment = $current;
      $current = $current->file;
    }

    $doc_comment = new self( $source, $line );
  
    if( $current instanceof DstyleDoc_Token_Class )
      $doc_comment->class = $current;
    elseif( $current instanceof DstyleDoc_Token_File )
      $doc_comment->file = $current;

    return $doc_comment;
  }

  // }}}
  // {{{ __toString()

  public function __toString()
  {
    return $this->source;
  }

  // }}}
}

/**
 * Classe abstratite d'un token lié à un ligne dans un script.
 */
abstract class DstyleDoc_Token_Fileable extends DstyleDoc_Token
{
  // {{{ $line

  protected $_line = 0;
  protected function set_line( $line )
  {
    $this->_line = (integer)$line;
  }
  protected function get_line()
  {
    return $this->_line;
  }

  // }}}
  // {{{ $file

  protected $_file = 0;

  protected function set_file( $file )
  {
    if( is_string($file) or $file instanceof DstyleDoc_Token_File )
      $this->_file = $file;
  }

  protected function get_file()
  {
    return $this->_file;
  }

  // }}}
  // {{{ __construct()

  protected function __construct( $file, $line )
  {
    if( $file instanceof DstyleDoc_Token_Fileable )
      $this->file = $file->file;
    elseif( is_string($file) )
      $this->file = $file;
    $this->line = $line;
  }

  // }}}
}

/**
 * Classe abstraite d'un token qui contient de la documentation.
 */
abstract class DstyleDoc_Token_Doc_Commentable extends DstyleDoc_Token_Fileable
{
  // {{{ $doc_comment

  protected $_doc_comment = null;
  
  protected function set_doc_comment( DstyleDoc_Token_Doc_Comment $doc_comment )
  {
    $this->_doc_comment = $doc_comment;
  }

  protected function get_doc_comment()
  {
    return $this->_doc_comment;
  }

  // }}}
  // {{{ __construct()

  protected function __construct( DstyleDoc_Token $doc_comment, $line )
  {
    parent::__construct( $doc_comment, $line );
/*    if( $doc_comment instanceof DstyleDoc_Token_Doc_Comment and $doc_comment->file instanceof DstyleDoc_Token_File )
      parent::__construct( $doc_comment->file, $line );
    else
      parent::__construct( null, $line );*/

    if( $doc_comment instanceof DstyleDoc_Token_Doc_Comment )
      $this->doc_comment = $doc_comment;
  }

  // }}}
}

/**
 * Classe d'un fichier
 */
class DstyleDoc_Token_File extends DstyleDoc_Token_Doc_Commentable implements DstyleDoc_Token_Elementable
{
  // {{{ hie()

  static public function hie( $file )
  {
    return new self( $file );
  }

  // }}}
  // {{{ __construct()

  protected function __construct( $file )
  {
    parent::__construct( DstyleDoc_Token_Null::hie(), 0 );
    $this->file = $file;
  }

  // }}}
  // {{{ to()

  public function to( DstyleDoc_Converter $converter )
  {
    $converter->file = $this->file;

    $converter->file->doc_comment = $this->doc_comment;
  }

  // }}}
}

/**
 * Classe d'un token de type classe.
 */
class DstyleDoc_Token_Class extends DstyleDoc_Token_Doc_Commentable implements DstyleDoc_Token_Valueable, DstyleDoc_Token_Elementable
{
  // {{{ $abstract

  protected $_abstract = false;

  protected function set_abstract( $abstract )
  {
    $this->_abstract = $abstract;
  }

  protected function get_abstract()
  {
    return $this->_abstract;
  }

  // }}}
  // {{{ $final

  protected $_final = false;

  protected function set_final( $final )
  {
    $this->_final = $final;
  }

  protected function get_final()
  {
    return $this->_final;
  }

  // }}}
  // {{{ $name

  protected $_name = '';

  protected function set_name( $string )
  {
    $this->_name = (string)$string;
  }

  protected function get_name()
  {
    return $this->_name;
  }

  // }}}
  // {{{ $parent

  protected $_parent = '';

  protected function set_parent( $parent )
  {
    $this->_parent = (string)$parent;
  }
  
  protected function get_parent()
  {
    return $this->_parent;
  }

  // }}}
  // {{{ $implements

  protected $_implements = array();

  protected function set_implement( $implement )
  {
    $this->_implements[] = (string)$implement;
  }

  protected function get_implements()
  {
    return $this->_implements;
  }

  // }}}
  // {{{ $methods

  protected $_methods = array();

  protected function set_method( DstyleDoc_Token_Method $method )
  {
    $this->_methods[] = $method;
  }

  protected function get_methods()
  {
    return $this->_methods;
  }

  // }}}
  // {{{ set_value()

  public function set_value( $string )
  {
    if( $this->name == '' )
      $this->name = $string;
  }

  // }}}
  // {{{ get_value()

  public function get_value()
  {
    return $this;
  }

  // }}}
  // {{{ hie()

  static public function hie( DstyleDoc_Token $current, $file, $line )
  {
    if( $current instanceof DstyleDoc_Token_Modifier )
    {
      $class = new self( $current->doc_comment, $file, $line );
      $class->abstract = $current->abstract;
      $class->final = $current->final;
    }
    else
      $class = new self( $current, $file, $line );
    return $class;
  }

  // }}}
  // {{{ to()

  public function to( DstyleDoc_Converter $converter )
  {
    $converter->class = $this->name;
    $class = $converter->class;

    $class->file = $this->file->file;
    $class->line = $this->line;
    $class->documentation = (string)$this->doc_comment;

    $class->parent = $this->parent;
    $class->abstract = $this->abstract;
    $class->final = $this->final;

    foreach( $this->implements as $implement )
      $class->implement = $implement;

    foreach( $this->methods as $method )
    {
      $class->method = $method->name;
      $function = $class->method;
      $converter->method = $function;

      $function->file = $method->file->file;
      $function->line = $method->line;
      $function->class = $class;
      $function->documentation = (string)$method->doc_comment;

      $function->abstract = $method->abstract;
      $function->static = $method->static;
      $function->final = $method->final;
      $function->public = $method->public;
      $function->protected = $method->protected;
      $function->private = $method->private;

      foreach( $method->vars as $var )
      {
        $function->param = $var->var;
        $function->param->type = $var->type;
        $function->param->default = $var->default;
      }

      foreach( $method->exceptions as $exception )
        $function->exception = $exception;
    }

    return DstyleDoc_Token_Null::hie();
  }

  // }}}
}

/**
 * Classe de token de liste de variable.
 */
class DstyleDoc_Token_Function_Tuple extends DstyleDoc_Function_Ref implements DstyleDoc_Token_Valueable
{
  // {{{ $var

  protected $_var = null;

  protected function set_var( DstyleDoc_Token_Variable $var )
  {
    $this->_var = $var;
  }

  protected function get_var()
  {
    return $this->_var;
  }

  // }}}
  // {{{ set_value()

  public function set_value( $string )
  {
    $this->var = DstyleDoc_Token_Variable::hie( $this, null, $string, $this->line );
  }

  // }}}
  // {{{ get_value()

  public function get_value()
  {
    return $this->var;
  }

  // }}}
  // {{{ hie()

  static public function hie( DstyleDoc_Token $current, $line )
  {
    return new self( $current, $line );
  }

  // }}}
}

/**
 * Classe de token de liste de variable.
 */
class DstyleDoc_Token_Method_Tuple extends DstyleDoc_Method_Ref implements DstyleDoc_Token_Valueable
{
  // {{{ $var

  protected $_var = null;

  protected function set_var( DstyleDoc_Token_Variable $var )
  {
    $this->_var = $var;
  }

  protected function get_var()
  {
    return $this->_var;
  }

  // }}}
  // {{{ set_value()

  public function set_value( $string )
  {
    $this->var = DstyleDoc_Token_Variable::hie( $this, null, $string );
  }

  // }}}
  // {{{ get_value()

  public function get_value()
  {
    return $this->var;
  }

  // }}}
  // {{{ hie()

  static public function hie( DstyleDoc_Token $current, $line )
  {
    return new self( $current, $line );
  }

  // }}}
}

/**
 * Classe de token d'inclusion de dépendance.
 */
class DstyleDoc_Token_Dependency extends DstyleDoc_Token implements DstyleDoc_Token_Valueable
{
  // {{{ $dependences

  protected $_dependences = array();

  protected function set_dependence( $dependence )
  {
    $this->_dependences[] = $dependence;
  }

  protected function get_dependences()
  {
    return $this->_dependences;
  }

  // }}}
  // {{{ set_value()

  public function set_value( $string )
  {
    $this->dependence = DstyleDoc_Token_Variable::hie( $this, null, $string );
  }

  // }}}
  // {{{ get_value()

  public function get_value()
  {
    return $this->var;
  }

  // }}}
  // {{{ hie()

  static public function hie( DstyleDoc_Token $current )
  {
    var_dump( $current );
    exit;
    return new self( $current );
  }

  // }}}
}

/**
 * Interface des tokens qui peuvent recevoir des variables.
 */
interface DstyleDoc_Token_Variableable
{
  // {{{ set_variable()

  /**
   * Associe une variable à l'objet.
   * Lorsqu'un token de variable est trouvé (ou qu'un token de string a l'endroit ou doit de trouver un token de variable) apparait, cette méthode est appelé pour indiquer à l'object qu'une variable lui est associé.
   */
  function set_variable( DstyleDoc_Token_Variable $var );

  // }}}
  // {{{ get_variable()

  /**
   * Rentourne l'objet courrant.
   * Lorsque la variable à été ajouté, cette méthode est appelé pour retourner l'object qui recevra les ordres du token suivant.
   * Return: DstyleDoc_Token
   */
  function get_variable();

  // }}}
} 

/**
 * Classe d'un token de type fonction.
 */ 
class DstyleDoc_Token_Function extends DstyleDoc_Token_Doc_Commentable implements DstyleDoc_Token_Valueable, DstyleDoc_Token_Variableable, DstyleDoc_Token_Elementable
{
  // {{{ $exception

  protected $_exceptions = array();

  protected function set_exception( $exception )
  {
    $this->_exceptions[] = (string)$exception;
  }

  protected function get_exceptions()
  {
    return $this->_exceptions;
  }

  // }}}
  // {{{ $name

  protected $_name = '';

  protected function get_name()
  {
    return $this->_name;
  }

  protected function set_name( $name )
  {
    $this->_name = (string)$name;
  }

  // }}}
  // {{{ $vars

  protected $_vars = array();

  /**
   * Renvoie toutes les variables associé à la fonction.
   * Return: array( DstyleDoc_Token_Variable )
   */
  protected function get_vars()
  {
    return $this->_vars;
  }

  /**
   * Associe une variable à la fonction.
   */
  protected function set_var( DstyleDoc_Token_Variable $var )
  {
    $this->_vars[] = $var;
  }

  /**
   * Renvoie la dernière variable associé à la fonction.
   * Return: DstyleDoc_Token_Variable
   */
  protected function get_var()
  {
    list($var) = array_reverse($this->_vars);
    return $var;
  }

  // }}}
  // {{{ set_value()

  public function set_value( $string )
  {
    if( $this->name == '' )
      $this->name = $string;
  }

  // }}}
  // {{{ get_value()

  public function get_value()
  {
    return $this;
  }

  // }}}
  // {{{ set_variable()

  public function set_variable( DstyleDoc_Token_Variable $variable )
  {
    $this->var = $variable;
  }

  // }}}
  // {{{ get_variable()

  public function get_variable()
  {
    return $this->var;
  }

  // }}}
  // {{{ hie()

  static public function hie( DstyleDoc_Token $current, $line )
  {
    return new self( $current, $line );
  }

  // }}}
  // {{{ to()

  public function to( DstyleDoc_Converter $converter )
  {
    $converter->function = $this->name;
    $function = $converter->function;

    $function->file = $this->file->file;
    $function->line = $this->line;
    $function->documentation = (string)$this->doc_comment;

    foreach( $this->vars as $var )
    {
      $function->param = $var->var;
      $function->param->type = $var->type;
      $function->param->default = $var->default;
    }

    foreach( $this->exceptions as $exception )
      $function->exception = $exception;

    return $this->file;
  }

  // }}}
  // {{{ $tupled

  protected $_tupled = false;

  protected function get_tupled()
  {
    $this->_tupled = true;
    return $this;
  }

  protected function get_is_tupled()
  {
    return $this->_tupled;
  }

  // }}}
}

/**
 * Classe d'un token de type methode.
 */
class DstyleDoc_Token_Method extends DstyleDoc_Token_Doc_Commentable implements DstyleDoc_Token_Valueable, DstyleDoc_Token_Variableable 
{
  // {{{ $abstract

  protected $_abstract = false;

  protected function set_abstract( $abstract )
  {
    $this->_abstract = $abstract;
  }

  protected function get_abstract()
  {
    return $this->_abstract;
  }

  // }}}
  // {{{ $static

  protected $_static = false;

  protected function set_static( $static )
  {
    $this->_static = $static;
  }

  protected function get_static()
  {
    return $this->_static;
  }

  // }}}
  // {{{ $public

  protected $_public = false;

  protected function set_public( $public )
  {
    $this->_public = $public;
  }

  protected function get_public()
  {
    return $this->_public;
  }

  // }}}
  // {{{ $protected

  protected $_protected = false;

  protected function set_protected( $protected )
  {
    $this->_protected = $protected;
  }

  protected function get_protected()
  {
    return $this->_protected;
  }

  // }}}
  // {{{ $private

  protected $_private = false;

  protected function set_private( $private )
  {
    $this->_private = $private;
  }

  protected function get_private()
  {
    return $this->_private;
  }

  // }}}
  // {{{ $final

  protected $_final = false;

  protected function set_final( $final )
  {
    $this->_final = $final;
  }

  protected function get_final()
  {
    return $this->_final;
  }

  // }}}
  // {{{ $class

  protected $_class = null;

  protected function set_class( $class )
  {
    $this->_class = $class;
  }

  protected function get_class()
  {
    return $this->_class;
  }

  // }}}
  // {{{ $exception

  protected $_exceptions = array();

  protected function set_exception( $exception )
  {
    $this->_exceptions[] = (string)$exception;
  }

  protected function get_exceptions()
  {
    return $this->_exceptions;
  }

  // }}}
  // {{{ $name

  protected $_name = '';

  protected function get_name()
  {
    return $this->_name;
  }

  protected function set_name( $name )
  {
    $this->_name = (string)$name;
  }

  // }}}
  // {{{ $vars

  protected $_vars = array();

  /**
   * Renvoie toutes les variables associé à la fonction.
   * Return: array( DstyleDoc_Token_Variable )
   */
  protected function get_vars()
  {
    return $this->_vars;
  }

  /**
   * Associe une variable à la fonction.
   */
  protected function set_var( DstyleDoc_Token_Variable $var )
  {
    $this->_vars[] = $var;
  }

  /**
   * Renvoie la dernière variable associé à la fonction.
   * Return: DstyleDoc_Token_Variable
   */
  protected function get_var()
  {
    list($var) = array_reverse($this->_vars);
    return $var;
  }

  // }}}
  // {{{ set_value()

  public function set_value( $string )
  {
    if( $this->name == '' )
      $this->name = $string;
  }

  // }}}
  // {{{ get_value()

  public function get_value()
  {
    return $this;
  }

  // }}}
  // {{{ set_variable()

  public function set_variable( DstyleDoc_Token_Variable $variable )
  {
    $this->var = $variable;
  }

  // }}}
  // {{{ get_variable()

  public function get_variable()
  {
    return $this->var;
  }

  // }}}
  // {{{ hie()

  static public function hie( DstyleDoc_Token $current, $line )
  {
    if( $current instanceof DstyleDoc_Token_Modifier )
    {
      $method = new self( $current->class, $line );
      $method->doc_comment = $current->doc_comment;
      $method->abstract = $current->abstract;
      $method->static = $current->static;
      $method->final = $current->final;
      $method->protected = $current->protected;
      $method->public = $current->public;
      $method->private = $current->private;
    }
    else
      $method = new self( $current, $line );
    return $method;
  }

  // }}}
  // {{{ $tupled

  protected $_tupled = false;

  protected function get_tupled()
  {
    $this->class->method = $this;
    $this->_tupled = true;
    return $this;
  }

  protected function get_is_tupled()
  {
    return $this->_tupled;
  }

  // }}}
  // {{{ __construct()

  protected function __construct( DstyleDoc_Token_Class $class, $line )
  {
    parent::__construct( $class, $line );
    $this->class = $class;
  }

  // }}}
}

/**
 * Classe de token de type variable pour fonction.
 */
class DstyleDoc_Token_Variable extends DstyleDoc_Function_Ref implements DstyleDoc_Token_Valueable
{
  // {{{ $var

  protected $_var = '';

  protected function set_var( $var )
  {
    $this->_var = (string)$var;
  }

  protected function get_var()
  {
    return $this->_var;
  }

  // }}}
  // {{{ $type

  protected $_type = '';

  protected function set_type( $type )
  {
    $this->_type = (string)$type;
  }

  protected function get_type()
  {
    return $this->_type;
  }

  // }}}
  // {{{ $default

  protected $_default = '';

  protected function set_default( $default )
  {
    $this->_default = $default;
  }

  protected function get_default()
  {
    return $this->_default;
  }

  // }}}
  // {{{ set_value()

  public function set_value( $string )
  {
    $this->default = $string;
  }

  // }}}
  // {{{ get_value()

  public function get_value()
  {
    return $this;
  }

  // }}}
  // {{{ __construct()

  protected function __construct( DstyleDoc_Token $varable, $source, $type, $line )
  {
    parent::__construct( $varable, $line );
    $this->var = $source;
    $this->type = $type;
  }

  // }}}
  // {{{ hie()

  static public function hie( DstyleDoc_Token $current, $source, $type, $line )
  {
    if( $current instanceof DstyleDoc_Token_Method_Tuple or $current instanceof DstyleDoc_Token_Function_Tuple )
    {
      $current->function->variable = new self( $current->function, $source, $type, $line );
      return $current->function->variable;
    }
    elseif( $current instanceof DstyleDoc_Token_Method or $current instanceof DstyleDoc_Token_Function )
    {
      return $current;
    }
    elseif( $current instanceof DstyleDoc_Token_Variable )
    {
      if( $source )
        $current->var = $source;
      if( $type )
        $current->type = $type;
      return $current;
    }
    elseif( $current instanceof DstyleDoc_Function_Ref )
      return $current->function;
    elseif( $current instanceof DstyleDoc_Token )
      return $current;
    else
      return DstyleDoc_Token_Null::hie();
  }

  // }}}
}

/**
 * Classe abstraite des tokens faisant reference à un token de type classe.
 */
abstract class DstyleDoc_Class_Ref extends DstyleDoc_Token_Fileable
{
  // {{{ $class

  protected $_class = null;

  protected function set_class( DstyleDoc_Token_Class $class )
  {
    $this->_class = $class;
  }

  protected function get_class()
  {
    if( $this->_class instanceof DstyleDoc_Token_Class )
      return $this->_class;
    else
      return DstyleDoc_Token_Null::hie();
  }

  // }}}
  // {{{ __construct()

  protected function __construct( DstyleDoc_Token_Class $class, $line )
  {
    parent::__construct( $class, $line );
    $this->class = $class;
  }

  // }}}
}

/**
 * Classe des tokens faisant reference à un token de type function.
 */
abstract class DstyleDoc_Function_Ref extends DstyleDoc_Token_Fileable
{
  // {{{ $function

  protected $_function = null;

  protected function set_function( DstyleDoc_Token $function )
  {
    if( $function instanceof DstyleDoc_Token_Function or $function instanceof DstyleDoc_Token_Method )
      $this->_function = $function;
  }

  protected function get_function()
  {
    return $this->_function;
  }

  // }}}
  // {{{ __construct()

  protected function __construct( DstyleDoc_Token $function, $line )
  {
    parent::__construct( $function, $line );
    $this->function = $function;
  }

  // }}}
}

/**
 * Classe des tokens faisant reference à un token de type method.
 */
abstract class DstyleDoc_Method_Ref extends DstyleDoc_Function_Ref
{
}

/**
 * Classe de token de modificateur de token de type class.
 */
class DstyleDoc_Token_Modifier extends DstyleDoc_Class_Ref
{
  // {{{ $doc_comment

  protected $_doc_comment = null;

  protected function set_doc_comment( DstyleDoc_Token_Doc_Comment $doc_comment )
  {
    $this->_doc_comment = $doc_comment;
  }

  protected function get_doc_comment()
  {
    return $this->_doc_comment;
  }

  // }}}
  // {{{ $abstract

  protected $_abstract = false;

  protected function set_abstract( $abstract )
  {
    $this->_abstract = $abstract;
  }

  protected function get_abstract()
  {
    return $this->_abstract;
  }

  // }}}
  // {{{ $static

  protected $_static = false;

  protected function set_static( $static )
  {
    $this->_static = $static;
  }

  protected function get_static()
  {
    return $this->_static;
  }

  // }}}
  // {{{ $public

  protected $_public = false;

  protected function set_public( $public )
  {
    $this->_public = $public;
  }

  protected function get_public()
  {
    return $this->_public;
  }

  // }}}
  // {{{ $protected

  protected $_protected = false;

  protected function set_protected( $protected )
  {
    $this->_protected = $protected;
  }

  protected function get_protected()
  {
    return $this->_protected;
  }

  // }}}
  // {{{ $private

  protected $_private = false;

  protected function set_private( $private )
  {
    $this->_private = $private;
  }

  protected function get_private()
  {
    return $this->_private;
  }

  // }}}
  // {{{ $final

  protected $_final = false;

  protected function set_final( $final )
  {
    $this->_final = $final;
  }

  protected function get_final()
  {
    return $this->_final;
  }

  // }}}
  // {{{ hie()

  static public function hie( DstyleDoc_Token $current, $source, $line )
  {
    if( $current instanceof DstyleDoc_Token_Modifier )
      $modifier = $current;
    else
      $modifier = new self( $current, $line );

    if( $current instanceof DstyleDoc_Token_Doc_Comment )
      $modifier->doc_comment = $current;

    if( $current instanceof DstyleDoc_Token_Class )
      $modifier->class = $current;

    if( $current instanceof DstyleDoc_Class_Ref and $current->class )
      $modifier->class = $current->class;

    switch( strtolower($source) )
    {
    case 'abstract':
      $modifier->abstract = true;
      break;

    case 'static':
      $modifier->static = true;
      break;

    case 'final':
      $modifier->final = true;
      break;

    case 'public':
      $modifier->public = true;
      break;

    case 'protected':
      $modifier->protected = true;
      break;

    case 'private':
      $modifier->private = true;
      break;
    }

    return $modifier;
  }

  // }}}
  // {{{ __construct()

  protected function __construct( DstyleDoc_Token $current, $line )
  {
    parent::__construct( $current->class, $line );
  }

  // }}}
}

/**
 * Classe de token de type String.
 */
class DstyleDoc_Token_String extends DstyleDoc_Token
{
  // {{{ hie()

  static public function hie( DstyleDoc_Token $current, $source )
  {
    if( $current instanceof DstyleDoc_Token_Valueable )
    {
      $current->value = $source;
      return $current->value;
    }
    else
      return $current;
  }

  // }}}
}

/**
 * Classe de token de type Number.
 */
class DstyleDoc_Token_Lnumber extends DstyleDoc_Token
{
  // {{{ hie()

  static public function hie( DstyleDoc_Token $current, $source )
  {
    if( $current instanceof DstyleDoc_Token_Valueable )
    {
      $current->value = $source;
      return $current->value;
    }
    else
      return DstyleDoc_Token_Null::hie();
  }

  // }}}
}

/**
 * Classe de token de type Extends.
 */
class DstyleDoc_Token_Extends extends DstyleDoc_Class_Ref implements DstyleDoc_Token_Valueable
{
  // {{{ set_value()

  public function set_value( $string )
  {
    $this->class->parent = $string;
  }

  // }}}
  // {{{ get_value()

  public function get_value()
  {
    return $this->class;
  }

  // }}}
  // {{{ hie()

  static public function hie( DstyleDoc_Token_Class $current, $line )
  {
    return new self( $current, $line );
  }

  // }}}
}

/**
 * Classe du token d'implemtation.
 */
class DstyleDoc_Token_Implements extends DstyleDoc_Class_Ref implements DstyleDoc_Token_Valueable
{
  // {{{ set_value()

  public function set_value( $string )
  {
    $this->class->implement = $string;
  }

  // }}}
  // {{{ get_value()

  public function get_value()
  {
    return $this;
  }

  // }}}
  // {{{ hie()

  static public function hie( DstyleDoc_Token_Class $current, $line )
  {
    return new self( $current, $line );
  }

  // }}}
}

/**
 * Classe du token de lancement d'exception.
 */
class DstyleDoc_Token_Throw extends DstyleDoc_Function_Ref implements DstyleDoc_Token_Valueable
{
  // {{{ set_value()

  public function set_value( $string )
  {
    $this->function->exception = $string;
  }

  // }}}
  // {{{ get_value()

  public function get_value()
  {
    return $this->function;
  }

  // }}}
  // {{{ hie()

  static public function hie( DstyleDoc_Token_Function $current, $line )
  {
    return new self( $current, $line );
  }

  // }}}
}

/**
 * Classe du token d'une interface.
 */
class DstyleDoc_Token_Interface extends DstyleDoc_Token_Doc_Commentable implements DstyleDoc_Token_Valueable, DstyleDoc_Token_Elementable
{
  // {{{ $name

  protected $_name = '';

  protected function get_name()
  {
    return $this->_name;
  }

  protected function set_name( $name )
  {
    $this->_name = (string)$name;
  }

  // }}}
  // {{{ set_value()

  public function set_value( $string )
  {
    $this->name = $string;
  }

  // }}}
  // {{{ get_value()

  public function get_value()
  {
    return $this;
  }

  // }}}
  // {{{ hie()

  static public function hie( DstyleDoc_Token $current, $file, $line )
  {
    $interface = new self( $current, $file, $line );
    return $interface;
  }

  // }}}
  // {{{ to()

  public function to( DstyleDoc_Converter $converter )
  {
    $converter->interface = $this->name;
    $interface = $converter->interface;

    $interface->file = $this->file;
    $interface->line = $this->line;
    $interface->documentation = (string)$this->doc_comment;

    return DstyleDoc_Token_Null::hie();
  }

  // }}}
}

/**
 * Interface de base pour les converteurs.
 */
interface DstyleDoc_Converter_Convert
{
  // {{{ get_file_classes()

  /**
   * Retourne la liste des classes appartenant à un fichier donnée.
   * Return:
   *    array(DstyleDoc_Element_Class) = Un tableau de classe.
   */
  public function get_file_classes( DstyleDoc_Element_File $file );

  // }}}
  // {{{ convert_all()

  /**
   * Converti tous elements.
   */
  function convert_all();

  // }}}
  // {{{ convert_description()

  /**
   * Converti la description longue.
   * Params:
   *    array(string) $description = Toutes les lignes de la description longue.
   */
  function convert_description( $description );

  // }}}
  // {{{ convert_title()

  /**
   * Convertie la description courte.
   * Params:
   *    string $title = La ligne de description courte.
   */
  function convert_title( $title );

  // }}}
  // {{{ convert_link()

  /**
   * Convertie un lien vers un element.
   */

  // }}}
}

/**
 * Convertisseur abstrait
 */
abstract class DstyleDoc_Converter extends DstyleDoc_Properties implements DstyleDoc_Converter_Convert
{
  // {{{ $files

  protected $_files = array();

  protected function set_file( $name )
  {
    $found = false;
    if( ! empty($name) and count($this->_files) )
    {
      reset($this->_files);
      while( true)
      {
        $file = current($this->_files);
        if( $found = ($file->name == $name or $file === $name) or false === next($this->_files) )
          break;
      }
    }

    if( ! $found )
    {
      if( $name instanceof DstyleDoc_Element_File )
        $this->_files[] = $name;
      else
        $this->_files[] = new DstyleDoc_Element_File( $this, $name );
      end($this->_files);
    }
  }

  protected function get_file()
  {
    if( ! count($this->_files) )
    {
      $this->_files[] = new DstyleDoc_Element_File( $this, null );
      return end($this->_files);
    }
    else
      return current($this->_files);
  }
 
  protected function get_files()
  {
    return $this->_files;
  }

  // }}}
  // {{{ $classes

  protected $_classes = array();

  protected function set_class( $name )
  {
    $found = false;
    if( ! empty($name) and count($this->_classes) )
    {
      reset($this->_classes);
      while( true)
      {
        $class = current($this->_classes);
        if( $found = ($class->name == $name or $class === $name) or false === next($this->_classes) )
          break;
      }
    }

    if( ! $found )
    {
      if( $name instanceof DstyleDoc_Element_Class )
        $this->_classes[] = $name;
      else
        $this->_classes[] = new DstyleDoc_Element_Class( $this, $name );
      end($this->_classes);
    }
  }

  protected function get_class()
  {
    if( ! count($this->_classes) )
    {
      $this->_classes[] = new DstyleDoc_Element_Class( $this, null );
      return end($this->_classes);
    }
    else
      return current($this->_classes);
  }
 
  protected function get_classes()
  {
    return $this->_classes;
  }

  // }}}
  // {{{ $interfaces

  protected $_interfaces = array();

  protected function set_interface( $name )
  {
   $found = false;
    if( ! empty($name) and count($this->_interfaces) )
    {
      reset($this->_interfaces);
      while( true)
      {
        $interface = current($this->_interfaces);
        if( $found = ($interface->name == $name) or false === next($this->_interfaces) )
          break;
      }
    }

    if( ! $found )
    {
      $this->_interfaces[] = new DstyleDoc_Element_Interface( $this, $name );
      end($this->_interfaces);
    }
  }

  protected function get_interface()
  {
    if( ! count($this->_interfaces) )
    {
      $this->_interfaces[] = new DstyleDoc_Element_Interface( $this, null );
      return end($this->_interfaces);
    }
    else
      return current($this->_interfaces);
  }

  protected function get_interfaces()
  {
    return $this->_interfaces;
  }

  // }}}
  // {{{ $functions

  protected $_functions = array();

  protected function set_function( $name )
  {
    $found = false;
    if( ! empty($name) and count($this->_functions) )
    {
      reset($this->_functions);
      while( true)
      {
        $function = current($this->_functions);
        if( $found = ($function->name == $name or $function === $name) or false === next($this->_functions) )
          break;
      }
    }

    if( ! $found )
    {
      if( $name instanceof DstyleDoc_Element_Function )
        $this->_functions[] = $name;
      else
        $this->_functions[] = new DstyleDoc_Element_Function( $this, $name );
      end($this->_functions);
    }
  }
  
  protected function get_function()
  {
    if( ! count($this->_functions) )
    {
      $this->_functions[] = new DstyleDoc_Element_Function( $this, null );
      return end($this->_functions);
    }
    else
      return current($this->_functions);
  }

  protected function get_functions()
  {
    return $this->_functions;
  }

  // }}}
  // {{{ $methods

  protected $_methods = array();

  protected function set_method( $name )
  {
    $found = false;
    if( ! empty($name) and count($this->_methods) )
    {
      reset($this->_methods);
      while( true)
      {
        $method = current($this->_methods);
        if( $found = ($method->name == $name or $method === $name) or false === next($this->_methods) )
          break;
      }
    }

    if( ! $found )
    {
      if( $name instanceof DstyleDoc_Element_Method )
        $this->_methods[] = $name;
      else
        $this->_methods[] = new DstyleDoc_Element_Method( $this, $name );
      end($this->_methods);
    }
  }
  
  protected function get_method()
  {
    if( ! count($this->_methods) )
    {
      $this->_methods[] = new DstyleDoc_Element_Method( $this, null );
      return end($this->_methods);
    }
    else
      return current($this->_methods);
  }

  protected function get_methods()
  {
    return $this->_methods;
  }

  // }}}
  // {{{ file_exists()

  public function file_exists( $file )
  {
    foreach( $this->_files as $value )
    {
      if( $value->file === $file )
        return $value;
    }
    return false;
  }

  // }}}
  // {{{ class_exists()

  public function class_exists( $class )
  {
    foreach( $this->_classes as $value )
    {
      if( $value->name === $class )
        return $value;
    }
    return false;
  }

  // }}}
  // {{{ interface_exists()

  public function interface_exists( $interface )
  {
    foreach( $this->_interfaces as $value )
    {
      if( $value->name === $interface )
        return $value;
    }
    return false;
  }

  // }}}
  // {{{ get_file_classes()

  public function get_file_classes( DstyleDoc_Element_File $file )
  {
    $classes = array();
    foreach( $this->classes as $class )
      if( $class->file = $file )
        $classes[] = $class;
    return $classes;
  }

  // }}}
}

if( ! class_exists('BadPropertyException') )
{
class BadPropertyException extends LogicException
{
  public function __construct( $class, $member )
  {
    parent::__construct( sprintf('Access denied for %s::$%s.', get_class($class), $member) );
  }
}
}

?>
