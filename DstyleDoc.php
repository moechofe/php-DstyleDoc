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

  protected function t_unknown( DstyleDoc_Converter $converter, $current, $source )
  {
    return DstyleDoc_Token_Unknown::hie( $converter, $current, $source );
  }

  protected function t_close_tag( DstyleDoc_Converter $converter, $current )
  {
    if( $current instanceof DstyleDoc_Token )
      return $current;
    else
      return DstyleDoc_Token_Null::hie();
  }

  protected function t_extends( DstyleDoc_Converter $converter, $current )
  {
    return DstyleDoc_Token_Extends::hie( $current );
  }

  protected function t_implements( DstyleDoc_Converter $converter, $current )
  {
    return DstyleDoc_Token_Implements::hie( $current );
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

  protected function t_variable( DstyleDoc_Converter $converter, $current, $source )
  {
    return DstyleDoc_Token_Variable::hie( $current, $source, null );
  }

  protected function t_constant_encapsed_string( DstyleDoc_Converter $converter, $current, $source )
  {
    return DstyleDoc_Token_String::hie( $current, substr($source,1,-1) );
  }

  protected function t_lnumber( DstyleDoc_Converter $converter, $current, $source )
  {
    return DstyleDoc_Token_Lnumber::hie( $current, $source );
  }

  protected function t_throw( DstyleDoc_Converter $converter, $current )
  {
    return DstyleDoc_Token_Throw::hie( $current );
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

  protected function t_abstract( DstyleDoc_Converter $converter, $current, $source )
  {
    return DstyleDoc_Token_Modifier::hie( $current, $source );
  }

  protected function t_static( DstyleDoc_Converter $converter, $current, $source )
  {
    return DstyleDoc_Token_Modifier::hie( $current, $source );
  }

  protected function t_final( DstyleDoc_Converter $converter, $current, $source )
  {
    return DstyleDoc_Token_Modifier::hie( $current, $source );
  }

  protected function t_public( DstyleDoc_Converter $converter, $current, $source )
  {
    return DstyleDoc_Token_Modifier::hie( $current, $source );
  }

  protected function t_protected( DstyleDoc_Converter $converter, $current, $source )
  {
    return DstyleDoc_Token_Modifier::hie( $current, $source );
  }

  protected function t_private( DstyleDoc_Converter $converter, $current, $source )
  {
    return DstyleDoc_Token_Modifier::hie( $current, $source );
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

  static public function hie( DstyleDoc_Converter $converter, DstyleDoc_Token $current, $source )
  {
    switch( $source )
    {
    case ',':
      if( $current instanceof DstyleDoc_Token_Variable )
        return DstyleDoc_Token_Function_Tuple::hie( $current->function );
      break;

    case '(':
      if( $current instanceof DstyleDoc_Token_Function and ! $current->is_tupled )
        return DstyleDoc_Token_Function_Tuple::hie( $current );
      elseif( $current instanceof DstyleDoc_Token_Method and ! $current->is_tupled )
        return DstyleDoc_Token_Method_Tuple::hie( $current );
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
  // {{{ $file

  protected $_file = 0;

  protected function set_file( DstyleDoc_Token_File $file )
  {
    $this->_file = $file;
  }

  protected function get_file()
  {
    return $this->_file;
  }

  // }}}
  // {{{ $line

  protected $_line = 0;

  protected function set_line( $line )
  {
    $this->_line = (integer)$line;
  }

  protected function get_line( $line )
  {
    return $this->_line;
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
    if( $doc_comment instanceof DstyleDoc_Token_Doc_Comment and $doc_comment->file instanceof DstyleDoc_Token_File )
      parent::__construct( $doc_comment->file, $line );
    else
      parent::__construct( null, $line );

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

    $class->file = $this->file;
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

      $function->file = $method->file;
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

  static public function hie( DstyleDoc_Token $current )
  {
    return new self( $current );
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

  static public function hie( DstyleDoc_Token $current )
  {
    return new self( $current );
  }

  // }}}
}

/**
 * Classe de token d'inclusion de dépendance.
 * /
class DstyleDoc_Token_Dependency extends DstyleDoc_Token implements DstyleDoc_Token_Valueable
{
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

    return DstyleDoc_Token_Null::hie();
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

  static public function hie( DstyleDoc_Token $current, $file, $line )
  {
    if( $current instanceof DstyleDoc_Token_Modifier )
    {
      $method = new self( $current->class, $file, $line );
      $method->doc_comment = $current->doc_comment;
      $method->abstract = $current->abstract;
      $method->static = $current->static;
      $method->final = $current->final;
      $method->protected = $current->protected;
      $method->public = $current->public;
      $method->private = $current->private;
    }
    else
      $method = new self( $current, $file, $line );
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

  protected function __construct( DstyleDoc_Token_Class $class, $file, $line )
  {
    parent::__construct( $class, $file, $line );
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

  protected function __construct( DstyleDoc_Token $varable, $source, $type )
  {
    parent::__construct( $varable );
    $this->var = $source;
    $this->type = $type;
  }

  // }}}
  // {{{ hie()

  static public function hie( DstyleDoc_Token $current, $source, $type )
  {
    if( $current instanceof DstyleDoc_Token_Method_Tuple or $current instanceof DstyleDoc_Token_Function_Tuple )
    {
      $current->function->variable = new self( $current->function, $source, $type );
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
abstract class DstyleDoc_Class_Ref extends DstyleDoc_Token
{
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
  // {{{ __construct()

  protected function __construct( DstyleDoc_Token_Class $class )
  {
    $this->class = $class;
  }

  // }}}
}

/**
 * Classe des tokens faisant reference à un token de type function.
 */
abstract class DstyleDoc_Function_Ref extends DstyleDoc_Token
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

  protected function __construct( DstyleDoc_Token $function )
  {
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

  static public function hie( DstyleDoc_Token $current, $source )
  {
    if( $current instanceof DstyleDoc_Token_Modifier )
      $modifier = $current;
    else
      $modifier = new self;

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

  protected function __construct()
  {
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
      return DstyleDoc_Token_Null::hie();
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

  static public function hie( DstyleDoc_Token_Class $current )
  {
    return new self( $current );
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

  static public function hie( DstyleDoc_Token_Class $current )
  {
    return new self( $current );
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

  static public function hie( DstyleDoc_Token_Function $current )
  {
    return new self( $current );
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

abstract class DstyleDoc_Custom_Element extends DstyleDoc_Properties
{
  // {{{ $converter

  protected $_converter = null;

  protected function set_converter( DstyleDoc_Converter $converter )
  {
    $this->_converter = $converter;
  }

  protected function get_converter()
  {
    return $this->_converter;
  }

  // }}}
  // {{{ $descriptions

  protected $_descriptions = array();

  protected function get_descriptions()
  {
    return $this->_descriptions;
  }

  protected function set_descriptions( $descriptions )
  {
    $this->_descriptions = (array)$descriptions;
  }

  protected function set_description( $description )
  {
    $this->_descriptions[] = $description;
  }

  protected function get_description()
  {
    return $this->converter->convert_description( $this->_descriptions );
  }

  // }}}
  // {{{ __construct()

  public function __construct( DstyleDoc_Converter $converter )
  {
    $this->converter = $converter;
  }

  // }}}
}

/**
 * Classe abstraite d'un Element.
 */
abstract class DstyleDoc_Element extends DstyleDoc_Custom_Element
{
  // {{{ $version

  protected $_version = '';

  protected function set_version( $version ) 
  {
    $this->_version = $version;
  }

  protected function get_version()
  {
    return $this->_version;
  }

  // }}}
  // {{{ $documentation

  protected $_documentation = '';

  protected function set_documentation( $documentation )
  {
    $this->_documentation = $documentation;
  }

  protected function get_documentation()
  {
    return $this->_documentation;
  }

  // }}}
  // {{{ $analysed

  protected $_analysed = false;

  protected function set_analysed( $analysed )
  {
    $this->_analysed = (boolean)$analysed;
  }

  protected function get_analysed()
  {
    return $this->_analysed;
  }

  // }}}
  // {{{ $packages

  protected $_packages = array();

  protected function set_packages( $packages )
  {
    $this->_packages = $packages;
  }

  protected function get_packages()
  {
    return $this->_packages;
  }

  // }}}
  // {{{ $historys

  protected $_historys = array();

  protected function set_historys( $versions )
  {
    $this->_historys = (array)$versions;
  }

  protected function get_historys()
  {
    return $this->_historys;
  }

  protected function set_history( $version ) 
  {
    $this->_historys[] = new DstyleDoc_Element_History_Version( $this->converter, $version );
  }

  protected function get_history()
  {
    if( count($this->_historys) )
    {
      list($version) = array_reverse($this->_historys);
      return $version;
    }
    else
      return new DstyleDoc_Element_History_Version( $this->converter, null );
  }

  // }}}
  // {{{ __toString()

  abstract public function __toString();

  // }}}
  // {{{ analyse()

  public function analyse()
  {
    $analysers = array();
    foreach( get_declared_classes() as $class )
      if( is_subclass_of( $class, 'DstyleDoc_Analyser' ) )
        $analysers[] = $class;

    $current = null;
    foreach( explode("\n",strtr($this->documentation,array("\r\n"=>"\n","\r"=>"\n"))) as $source )
    {
      echo '<hr /><h1>',htmlentities($source),'</h1>';
      var_dump( $current );
      $result = array();
      $source = DstyleDoc_Analyser::remove_stars($source);
      foreach( $analysers as $analyser )
      {
          if( call_user_func( array($analyser,'analyse'), $current, $source, &$instance, &$priority ) )
            $result[$priority] = $instance;
      }
      if( $result )
      {
        ksort($result);
        $current = current($result);

        echo '<hr />';
        var_dump( $result );

        if( $current instanceof DstyleDoc_Analyser )
          $current = $current->apply( $this );
      }
    }

    foreach( $analysers as $analyser )
      call_user_func( array($analyser,'finalize'), $this );

    $this->analysed = true;
  }

  // }}}
}

/**
 * Classe abstratite d'un Element Contenant un titre.
 */
abstract class DstyleDoc_Element_Titled extends DstyleDoc_Element
{
  // {{{ $descriptions

  protected function get_description()
  {
    if( ! $this->analysed ) $this->analyse();
    $copy = $this->_descriptions;
    if( count($copy) )
      array_shift($copy);
    return $this->converter->convert_description( $copy );
  }

  // }}}
  // {{{ $title

  protected function get_title()
  {
    if( ! $this->analysed ) $this->analyse();
    if( count($this->_descriptions) )
      list($result) = $this->_descriptions;
    else
      $result = '';
    return $this->converter->convert_title( $result );
  }

  // }}}
}

/**
 * Classe abstraite d'un Element possèdant un lien dans un fichier.
 */
abstract class DstyleDoc_Element_Filed extends DstyleDoc_Element_Titled
{
  // {{{ $file

  protected $_file = '';

  protected function set_file( $file )
  {
    if( is_string($file) and ($found = $this->converter->file_exists($file)) )
      $this->_file = $found;
    elseif( $file instanceof DstyleDoc_Element_File )
      $this->_file = $file;
    else
      $this->_file = new DstyleDoc_Element_File( $this->converter, $file );
  }

  protected function get_file()
  {
    return $this->_file;
  }

  // }}}
  // {{{ $line

  protected $_line = 0;

  protected function set_line( $line )
  {
    $this->_line = abs((integer)$line);
  }

  protected function get_line()
  {
    return $this->_line;
  }

  // }}}
}

/**
 * Classe abstraite d'un Element possèdant un lien dans un fichier et un nom.
 */
abstract class DstyleDoc_Element_Filed_Named extends DstyleDoc_Element_Filed
{
  // {{{ $name

  protected $_name = '';

  protected function set_name( $name )
  {
    $this->_name = (string)$name;
  }

  protected function get_name()
  {
    return $this->_name;
  }

  // }}}
  // {{{ __toString()

  public function __toString()
  {
    return $this->name;
  }

  // }}}
  // {{{ __construct() 

  public function __construct( DstyleDoc_Converter $converter, $name )
  {
    parent::__construct( $converter );
    if( $name )
      $this->name = $name;
  }

  // }}}
}

/**
 * Classe d'un element de type fichier.
 */
class DstyleDoc_Element_File extends DstyleDoc_Element_Titled
{
  // {{{ $file

  protected $_file = '';

  protected function set_file( $file )
  {
    $this->_file = (string)$file;
  }

  protected function get_file()
  {
    return $this->_file;
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
  // {{{ __toString()

  public function __toString()
  {
    return $this->converter->convert_file( $this->file );
  }

  // }}}
  // {{{ __construct()

  public function __construct( DstyleDoc_Converter $converter, $file )
  {
    parent::__construct( $converter );
    $this->file = $file;
  }

  // }}}
}

/**
 * Classe d'un element de type classe.
 */
class DstyleDoc_Element_Class extends DstyleDoc_Element_Filed_Named
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
  // {{{ $parent

  protected $_parent = null;

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
        $this->_methods[] = new DstyleDoc_Element_Method( $this->converter, $name );
      end($this->_methods);
    }
  }

  protected function get_method()
  {
    if( ! count($this->_methods) )
    {
      $this->_methods[] = new DstyleDoc_Element_Method( $this->converter, null );
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
  // {{{ $childs

  protected function get_childs()
  {
    return $this->_childs;
  }

  // }}}
}

/**
 * Classe d'un element de version de l'historique.
 */
class DstyleDoc_Element_History_Version extends DstyleDoc_Custom_Element
{
  // {{{ $version

  protected $_version = '';

  protected function set_version( $version ) 
  {
    $this->_version = $version;
  }

  protected function get_version()
  {
    return $this->_version;
  }

  // }}}
  // {{{ __construct()

  public function __construct( DstyleDoc_Converter $converter, $version )
  {
    parent::__construct( $converter );
    $this->version = $version;
  }

  // }}}
}

/**
 * Classe d'un element de type interface.
 */
class DstyleDoc_Element_Interface extends DstyleDoc_Element_Filed_Named
{
}

/**
 * Classe d'un element de type fonction.
 */
class DstyleDoc_Element_Function extends DstyleDoc_Element_Filed_Named
{
  // {{{ $params

  protected $_params = array();

  protected function set_params( $params )
  {
    $this->_params = (array)$params;
  }

  protected function get_params()
  {
    return $this->_params;
  }

  /**
   * Séléction un paramètre existant ou en crée un nouveau.
   * Le paramètre ainsi séléctionné peut être récupérer avec get_param().
   * Params:
   *  $param = Le nom de la variable existante ou qui sera créer.
   */
  protected function set_param( $param ) 
  {
    $found = false;
    if( ! empty($param) and count($this->_params) )
    {
      reset($this->_params);
      while( true)
      {
        $value = current($this->_params);
        if( $value->var == $param )
        {
          $found = true;
          break;
        }
        elseif( false === next($this->_params) )
          break;
      }
    }

    if( ! $found )
    {
      $this->_params[] = new DstyleDoc_Element_Param( $this->converter, $param );
      end($this->_params);
    }
  }

  protected function get_param()
  {
    if( ! count($this->_params) )
    {
      $this->_params[] = new DstyleDoc_Element_Param( $this->converter, null );
      return end($this->_params);
    }
    else
      return current($this->_params);
  }

  // }}}
  // {{{ $returns

  protected $_returns = null;

  protected function get_return()
  {
    if( ! $this->_returns )
      $this->_returns = new DstyleDoc_Element_Return( $this->converter );

    return $this->_returns;
  }

  // }}}
  // {{{ $exceptions

  protected $_exceptions = array();

  protected function set_exception( $name )
  {
    $found = false;
    if( ! empty($name) and count($this->_exceptions) )
    {
      reset($this->_exceptions);
      while( true)
      {
        $exception = current($this->_exceptions);
        if( $found = ($exception->name == $name) or false === next($this->_exceptions) )
          break;
      }
    }

    if( ! $found )
    {
      $this->_exceptions[] = new DstyleDoc_Element_Exception( $this->converter, $name );
      end($this->_exceptions);
    }
  }

  protected function get_exception()
  {
    if( ! count($this->_exceptions) )
    {
      $this->_exceptions[] = new DstyleDoc_Element_Exception( $this->converter, null );
      return end($this->_exceptions);
    }
    else
      return current($this->_exceptions);
  }

  protected function get_exceptions()
  {
    return $this->_exceptions;
  }

  // }}}
  // {{{ $syntax

  protected $_syntax = array();

  protected function set_syntax( $syntax )
  {
    $this->_syntax[] = new DstyleDoc_Element_Syntax( $this->converter, $syntax );
  }

  protected function get_syntax()
  {
    if( count($this->_syntax) )
      return end($this->_syntax);
    else
      return new DstyleDoc_Element_Syntax( $this->converter, null );
  }

  // }}}
}

/**
 * Classe d'un element de type fonction.
 */
class DstyleDoc_Element_Method extends DstyleDoc_Element_Function
{
  // {{{ $class

  protected $_class = null;

  protected function set_class( DstyleDoc_Element_Class $class )
  {
    $this->_class = $class;
  }

  protected function get_class()
  {
    return $this->_class;
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
}

/**
 * Class d'un element de type syntaxe.
 */
class DstyleDoc_Element_Syntax extends DstyleDoc_Custom_Element
{
  // {{{ $syntax

  protected $_syntax = array();

  protected function set_syntax( $syntax ) 
  {
    $this->_syntax = (array)$syntax;
  }

  protected function get_syntax()
  {
    return $this->_syntax;
  }

  // }}}
  // {{{ __construct()

  public function __construct( DstyleDoc_Converter $converter, $syntax )
  {
    parent::__construct( $converter );
    $this->syntax = $syntax;
  }

  // }}}
}

/**
 * Classe d'un element de type paramètre.
 */
class DstyleDoc_Element_Exception extends DstyleDoc_Custom_Element
{
  // {{{ $name

  protected $_name = '';

  protected function set_name( $name ) 
  {
    $this->_name = (string)$name;
  }

  protected function get_name()
  {
    return $this->_name;
  }

  // }}}
  // {{{ __construct()

  public function __construct( DstyleDoc_Converter $converter, $exception )
  {
    parent::__construct( $converter );
    $this->name = $exception;
  }

  // }}}
}

/**
 * Classe d'un element de type paramètre.
 */
class DstyleDoc_Element_Param extends DstyleDoc_Custom_Element
{
  // {{{ $types

  protected $_types = '';

  protected function set_types( $types ) 
  {
    $this->_types = (array)$types;
  }

  protected function get_types()
  {
    return $this->_types;
  }

  protected function set_type( $type ) 
  {
    if( ! empty($type) )
    {
      $this->_types[] = (string)$type;
      $this->_types = array_unique($this->_types);
    }
  }

  // }}}
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
  // {{{ $default

  protected $_default = '';

  protected function set_default( $default ) 
  {
    $this->_default = (string)$default;
  }

  protected function get_default()
  {
    return $this->_default;
  }

  // }}}
  // {{{ __construct()

  public function __construct( DstyleDoc_Converter $converter, $var )
  {
    parent::__construct( $converter );
    if( $var )
      $this->var = $var;
  }

  // }}}
}

/**
 * Classe d'un element de type paramètre.
 */
class DstyleDoc_Element_Return extends DstyleDoc_Custom_Element
{
  // {{{ $types

  protected $_types = '';

  protected function set_types( $types ) 
  {
    $this->_types = (array)$types;
  }

  protected function get_types()
  {
    return $this->_types;
  }

  protected function set_type( $type ) 
  {
    if( ! empty($type) )
    {
      $this->_types[] = (string)$type;
      $this->_types = array_unique($this->_types);
    }
  }

  // }}}
}

/**
 * Interface de la base des analysers
 */
interface DstyleDoc_Analyseable
{
  // {{{ analyse()

  static function analyse( $current, $source, &$instance, &$priority );

  // }}}
  // {{{ apply()

  function apply( DstyleDoc_Element $element );

  // }}}
}

interface DstyleDoc_Analyser_Descriptable
{
  // {{{ descriptable()

  function descriptable( DstyleDoc_Element $element, $description );

  // }}}
}

/**
 * Classe abstraite de la base des analysers.
 */
abstract class DstyleDoc_Analyser extends DstyleDoc_Properties implements DstyleDoc_Analyseable
{
  // {{{ remove_stars()

  static public function remove_stars( $source )
  {
    // ^\s*(?:\/*\**|\**)\s*(.*?)\s*(?:\**\/|\**)$
    if( preg_match( '/^\\s*(?:\\/*\\**|\\**)\\s*(.*?)\\s*(?:\\**\\/|\\**)$/', $source, $matches ) )
      return $matches[1];
    else
      return $source;
  }

  // }}}
  // {{{ finalize()

  static public function finalize( DstyleDoc_Element $element )
  {
  }

  // }}}
}

/**
 * Classe d'analyse de la description.
 */
class DstyleDoc_Analyser_Description extends DstyleDoc_Analyser
{
  // {{{ priority

  const priority = 100;

  // }}}
  // {{{ analyse()

  static public function analyse( $current, $source, &$instance, &$priority )
  {
    if( $source and $current instanceof DstyleDoc_Analyser_Descriptable )
    {
      $instance = new self( $source );
      $priority = self::priority;
      $instance->descriptable = $current;
      return true;
    }
    elseif( $source )
    {
      $instance = new self( $source );
      $priority = self::priority;
      return true;
    }
    else
      return false;
  }

  // }}}
  // {{{ apply()

  /**
   * Ajoute une nouvelle ligne de description à l'élément.
   */
  public function apply( DstyleDoc_Element $element )
  {
    if( $this->descriptable )
    {
      $this->descriptable->descriptable( $element, $this->description );
      return $this->descriptable;
    }
    else
    {
      $element->description = $this->description;
      return $this;
    }
  }

  // }}}
  // {{{ $description

  protected $_description = '';

  protected function set_description( $description )
  {
    $this->_description = (string)$description;
  }

  protected function get_description()
  {
    return $this->_description;
  }

  // }}}
  // {{{ __construct()

  protected function __construct( $description )
  {
    $this->description = $description;
  }

  // }}}
  // {{{ $descriptable

  protected $_descriptable = null;

  protected function set_descriptable( DstyleDoc_Analyser_Descriptable $descriptable )
  {
    $this->_descriptable = $descriptable;
  }

  protected function get_descriptable()
  {
    return $this->_descriptable;
  }

  // }}}
}

/**
 * Classe d'analyse d'un séparateur de paragraphe la description
 */
class DstyleDoc_Analyser_Description_Paragraphe extends DstyleDoc_Analyser
{
  // {{{ priority

  const priority = 1000;

  // }}}
  // {{{ analyse()

  static public function analyse( $current, $source, &$instance, &$priority )
  {
    if( $current instanceof DstyleDoc_Analyser_Description and $source === '' )
    {
      $instance = new self();
      $priority = self::priority;
      return true;
    }
    else
      return false;
  }

  // }}}
  // {{{ apply()

  /**
   * Ajoute un nouveau paragraphe à la description à l'élément.
   * S'assure que le précédent ajout n'étaient pas déjà un nouveau paragraphe.
   */
  public function apply( DstyleDoc_Element $element )
  {
    if( count($element->descriptions) > 1 )
    {
      list($last) = array_reverse($element->descriptions);
      if( $last !== '' )
        $element->description = '';
    }
    return $this;
  }

  // }}}
  // {{{ finalize()

  static public function finalize( DstyleDoc_Element $element )
  {
    if( count($element->descriptions) > 1 )
    {
      list($last) = array_reverse($element->descriptions);
      if( $last === '' )
      {
        $new = $element->descriptions;
        array_pop($new);
        $element->descriptions = $new;
      }
    }
  }

  // }}}
}

/**
 * Classe d'analyse d'une balise de version.
 */
class DstyleDoc_Analyser_Version extends DstyleDoc_Analyser
{
  // {{{ $version

  protected $_version = '';

  protected function set_version( $version ) 
  {
    $this->_version = $version;
  }

  protected function get_version()
  {
    return $this->_version;
  }

  // }}}
  // {{{ priority

  const priority = 10;

  // }}}
  // {{{ analyse()

  static public function analyse( $current, $source, &$instance, &$priority )
  {
    // ^version:\s*(.+)$
    if( preg_match( '/^version:\\s*(.+)$/i', $source, $matches ) )
    {
      $instance = new self( $matches[1] );
      $priority = self::priority;
      return true;
    }
    else
      return false;
  }

  // }}}
  // {{{ __construct()

  protected function __construct( $version )
  {
    $this->version = $version;
  }

  // }}}
  // {{{ apply()

  /**
   * Ajoute un nouveau paragraphe à la description à l'élément.
   * S'assure que le précédent ajout n'étaient pas déjà un nouveau paragraphe.
   */
  public function apply( DstyleDoc_Element $element )
  {
    $element->version = $this->version;
    return $this;
  }

  // }}}
}

/**
 * Classe d'analyse d'une balise d'historique.
 */
class DstyleDoc_Analyser_History extends DstyleDoc_Analyser
{
  // {{{ priority

  const priority = 10;

  // }}}
  // {{{ analyse()

  static public function analyse( $current, $source, &$instance, &$priority )
  {
    // ^history:(?:\s*(?:v|version:?\s*)?(\d.*?)\s*[:=]?(?:\s+(.*)))?$
    if( preg_match( '/^history:(?:\\s*(?:v|version:?\\s*)?(\\d.*?)\\s*[:=]?(?:\\s+(.*)))?$/i', $source, $matches ) )
    {
      $instance = new self();
      $priority = self::priority;
      if( ! empty($matches[1]) )
      {
        $instance = new DstyleDoc_Analyser_Element_History_List( $matches[1], $matches[2] );
        $property = DstyleDoc_Analyser_Element_History_List::priority;
      }
      return true;
    }
    else
      return false;
  }

  // }}}
  // {{{ apply()

  /**
   * Ajoute un nouveau paragraphe à la description à l'élément.
   * S'assure que le précédent ajout n'étaient pas déjà un nouveau paragraphe.
   */
  public function apply( DstyleDoc_Element $element )
  {
    return $this;
  }

  // }}}
}

/**
 * Classe d'analyse d'un élément de liste d'historique.
 */
class DstyleDoc_Analyser_Element_History_List extends DstyleDoc_Analyser implements DstyleDoc_Analyser_Descriptable
{
  // {{{ $version

  protected $_version = '';

  protected function set_version( $version ) 
  {
    $this->_version = $version;
  }

  protected function get_version()
  {
    return $this->_version;
  }

  // }}}
  // {{{ $description

  protected $_description = '';

  protected function set_description( $description )
  {
    $this->_description = (string)$description;
  }

  protected function get_description()
  {
    return $this->_description;
  }

  // }}}
  // {{{ descriptable()

  public function descriptable( DstyleDoc_Element $element, $description )
  {
    $element->history->description = $description;
  }

  // }}}
  // {{{ priority

  const priority = 20;

  // }}}
  // {{{ analyse()

  static public function analyse( $current, $source, &$instance, &$priority )
  {
    // ^(?:[-+*]\s*)?(?:v|version:?\s*)?(\d.*?)\s*[:=]?(?:\s+(.*))$
    if( ($current instanceof DstyleDoc_Analyser_History or $current instanceof DstyleDoc_Analyser_Element_History_List)
      and preg_match( '/^(?:[-+*]\\s*)?(?:v|version:?\\s*)?(\\d.*?)\\s*[:=]?(?:\\s+(.*))$/i', $source, $matches ) )
    {
      $instance = new self( $matches[1], $matches[2] );
      $priority = self::priority;
      return true;
    }
    else
      return false;
  }

  // }}}
  // {{{ apply()

  /**
   * Ajoute un nouveau paragraphe à la description à l'élément.
   * S'assure que le précédent ajout n'étaient pas déjà un nouveau paragraphe.
   */
  public function apply( DstyleDoc_Element $element )
  {
    $element->history = $this->version;
    $element->history->description = $this->description;
    return $this;
  }

  // }}}
  // {{{ __construct()

  public function __construct( $version, $description )
  {
    $this->version = $version;
    $this->description = $description;
  }

  // }}}
}

/**
 * Classe d'analyse d'une balise de paramètre.
 */
class DstyleDoc_Analyser_Param extends DstyleDoc_Analyser
{
  // {{{ priority

  const priority = 10;

  // }}}
  // {{{ analyse()

  static public function analyse( $current, $source, &$instance, &$priority )
  {
    // ^params?:\s*(?:\s(?:([^\s+]+)\s+)?(?:(\$.+?|\.{3})(?:\s*[:=]?\s+)?)(.*))?$
    if( preg_match( '/^params?:\\s*(?:\\s(?:([^\\s+]+)\\s+)?(?:(\\$.+?|\\.{3})(?:\\s*[:=]?\\s+)?)(.*))?$/i', $source, $matches ) )
    {
      $instance = new self();
      $priority = self::priority;
      if( isset($matches[3]) )
      {
        $instance = new DstyleDoc_Analyser_Element_Param_List( $matches[1], $matches[2], $matches[3] );
        $property = DstyleDoc_Analyser_Element_Param_List::priority;
      }
      return true;
    }
    else
      return false;
  }

  // }}}
  // {{{ apply()

  /**
   * Ajoute un nouveau paragraphe à la description à l'élément.
   * S'assure que le précédent ajout n'étaient pas déjà un nouveau paragraphe.
   */
  public function apply( DstyleDoc_Element $element )
  {
    return $this;
  }

  // }}}
}

/**
 * Classe d'analyse d'un élément de liste de paramètre.
 */
class DstyleDoc_Analyser_Element_Param_List extends DstyleDoc_Analyser implements DstyleDoc_Analyser_Descriptable
{
  // {{{ $types

  protected $_types = '';

  protected function set_types( $types ) 
  {
    $this->_types = (array)$types;
  }

  protected function get_types()
  {
    return $this->_types;
  }

  // }}}
  // {{{ $var

  protected $_var = '';

  protected function set_var( $var ) 
  {
    $this->_var = $var;
  }

  protected function get_var()
  {
    return $this->_var;
  }

  // }}}
  // {{{ $description

  protected $_description = '';

  protected function set_description( $description )
  {
    $this->_description = (string)$description;
  }

  protected function get_description()
  {
    return $this->_description;
  }

  // }}}
  // {{{ descriptable()

  public function descriptable( DstyleDoc_Element $element, $description )
  {
    if( $element instanceof DstyleDoc_Element_Function )
      $element->param->description = $description;
  }

  // }}}
  // {{{ priority

  const priority = 15;

  // }}}
  // {{{ analyse()

  static public function analyse( $current, $source, &$instance, &$priority )
  {
    // ^\s*(?:[-+*]\s+)?(?:([^\s]+)\s+)?(?:(\$.+?|\.{3})(?:\s*[:=]?\s+)?)(.*)$
    if( ($current instanceof DstyleDoc_Analyser_Param or $current instanceof DstyleDoc_Analyser_Element_Param_List)
      and preg_match( '/^\\s*(?:[-+*]\\s+)?(?:([^\\s]+)\\s+)?(?:(\\$.+?|\\.{3})(?:\\s*[:=]?\\s+)?)(.*)$/i', $source, $matches ) )
    {
      $instance = new self( $matches[1], $matches[2], $matches[3] );
      $priority = self::priority;
      return true;
    }
    else
      return false;
  }

  // }}}
  // {{{ apply()

  /**
   * Ajoute un nouveau paragraphe à la description à l'élément.
   * S'assure que le précédent ajout n'étaient pas déjà un nouveau paragraphe.
   */
  public function apply( DstyleDoc_Element $element )
  {
    if( $element instanceof DstyleDoc_Element_Function )
    {
      $element->param = $this->var;

      if( $this->var )
        $element->param->var = $this->var;

      foreach( $this->types as $type )
        $element->param->type = $type;

      $element->param->description = $this->description;
    }
    return $this;
  }

  // }}}
  // {{{ __construct()

  public function __construct( $types, $var, $description )
  {
    $this->types = preg_split('/[,|]/', $types);
    $this->var = $var;
    $this->description = $description;
  }

  // }}}
}

/**
 * Classe d'analyse d'une balise de retour.
 */
class DstyleDoc_Analyser_Return extends DstyleDoc_Analyser
{
  // {{{ priority

  const priority = 10;

  // }}}
  // {{{ analyse()

  static public function analyse( $current, $source, &$instance, &$priority )
  {
    // ^returns?:\s*(?:\s(?:([^\s+]+)\s+)?(.*))?$
    if( preg_match( '/^returns?:\\s*(?:\\s(?:([^\\s+]+)\\s+)?(.*))?$/i', $source, $matches ) )
    {
      $instance = new self();
      $priority = self::priority;
      if( isset($matches[2]) )
      {
        $instance = new DstyleDoc_Analyser_Element_Return_List( $matches[1], $matches[2] );
        $property = DstyleDoc_Analyser_Element_Return_List::priority;
      }
      return true;
    }
    else
      return false;
  }

  // }}}
  // {{{ apply()

  /**
   * Ajoute un nouveau paragraphe à la description à l'élément.
   * S'assure que le précédent ajout n'étaient pas déjà un nouveau paragraphe.
   */
  public function apply( DstyleDoc_Element $element )
  {
    return $this;
  }

  // }}}
}

/**
 * Classe d'analyse d'un élément de liste de retour.
 */
class DstyleDoc_Analyser_Element_Return_List extends DstyleDoc_Analyser implements DstyleDoc_Analyser_Descriptable
{
  // {{{ $types

  protected $_types = '';

  protected function set_types( $types ) 
  {
    $this->_types = (array)$types;
  }

  protected function get_types()
  {
    return $this->_types;
  }

  // }}}
  // {{{ $description

  protected $_description = '';

  protected function set_description( $description )
  {
    $this->_description = (string)$description;
  }

  protected function get_description()
  {
    return $this->_description;
  }

  // }}}
  // {{{ descriptable()

  public function descriptable( DstyleDoc_Element $element, $description )
  {
    if( $element instanceof DstyleDoc_Element_Function )
      $element->return->description = $description;
  }

  // }}}
  // {{{ priority

  const priority = 15;

  // }}}
  // {{{ analyse()

  static public function analyse( $current, $source, &$instance, &$priority )
  {
    // ^\s*(?:-\s+)?(?:(?:([^\s+]+)\s+)?(.*))?$
    if( ($current instanceof DstyleDoc_Analyser_Return or $current instanceof DstyleDoc_Analyser_Element_Return_List)
      and preg_match( '/^\\s*(?:-\\s+)?(?:(?:([^\\s+]+)\\s+)?(.*))?$/i', $source, $matches ) )
    {
      if( ! trim($matches[2]) )
        return false;
      $instance = new self( $matches[1], $matches[2] );
      $priority = self::priority;
      return true;
    }
    else
      return false;
  }

  // }}}
  // {{{ apply()

  /**
   * Ajoute un nouveau paragraphe à la description à l'élément.
   * S'assure que le précédent ajout n'étaient pas déjà un nouveau paragraphe.
   */
  public function apply( DstyleDoc_Element $element )
  {
    if( $element instanceof DstyleDoc_Element_Function )
    {
      foreach( $this->types as $type )
        $element->return->type = $type;

      $element->return->description = $this->description;
    }
    return $this;
  }

  // }}}
  // {{{ __construct()

  public function __construct( $types, $description )
  {
    $this->types = preg_split('/[,|]/', $types);
    $this->description = $description;
  }

  // }}}
}

/**
 * Classe d'analyse d'une balise de version.
 */
class DstyleDoc_Analyser_Package extends DstyleDoc_Analyser
{
  // {{{ $packages

  protected $_packages = '';

  protected function set_packages( $packages ) 
  {
    $this->_packages = (array)$packages;
  }

  protected function get_packages()
  {
    return $this->_packages;
  }

  // }}}
  // {{{ priority

  const priority = 10;

  // }}}
  // {{{ analyse()

  static public function analyse( $current, $source, &$instance, &$priority )
  {
    // ^package:\s*(.+)$
    if( preg_match( '/^package:\\s*(.+)$/i', $source, $matches ) )
    {
      $instance = new self( $matches[1] );
      $priority = self::priority;
      return true;
    }
    else
      return false;
  }

  // }}}
  // {{{ __construct()

  protected function __construct( $package )
  {
    $this->packages = preg_split( '/[.,;:> ]+/', $package );
  }

  // }}}
  // {{{ apply()

  /**
   * Ajoute un nouveau paragraphe à la description à l'élément.
   * S'assure que le précédent ajout n'étaient pas déjà un nouveau paragraphe.
   */
  public function apply( DstyleDoc_Element $element )
  {
    $element->packages = $this->packages;
    return $this;
  }

  // }}}
}

/**
 * Classe d'analyse d'une balise d'exception.
 */
class DstyleDoc_Analyser_Throw extends DstyleDoc_Analyser
{
  // {{{ priority

  const priority = 10;

  // }}}
  // {{{ analyse()

  static public function analyse( $current, $source, &$instance, &$priority )
  {
    // ^throws?:\s*(?:\s(?:([\pL\pN]+)\s*)(?:[:=]\s+)?(.*))?$
    if( preg_match( '/^throws?:\\s*(?:\\s(?:([\\pL\\pN]+)\\s*)(?:[:=]\\s+)?(.*))?$/i', $source, $matches ) )
    {
      $instance = new self();
      $priority = self::priority;
      if( isset($matches[2]) )
      {
        $instance = new DstyleDoc_Analyser_Element_Throw_List( $matches[1], $matches[2] );
        $property = DstyleDoc_Analyser_Element_Throw_List::priority;
      }
      return true;
    }
    else
      return false;
  }

  // }}}
  // {{{ apply()

  /**
   * Ajoute un nouveau paragraphe à la description à l'élément.
   * S'assure que le précédent ajout n'étaient pas déjà un nouveau paragraphe.
   */
  public function apply( DstyleDoc_Element $element )
  {
    return $this;
  }

  // }}}
}

/**
 * Classe d'analyse d'un élément de liste d'exception.
 */
class DstyleDoc_Analyser_Element_Throw_List extends DstyleDoc_Analyser implements DstyleDoc_Analyser_Descriptable
{
  // {{{ $exception

  protected $_exception = '';

  protected function set_exception( $exception ) 
  {
    $this->_exception = (string)$exception;
  }

  protected function get_exception()
  {
    return $this->_exception;
  }

  // }}}
  // {{{ $description

  protected $_description = '';

  protected function set_description( $description )
  {
    $this->_description = (string)$description;
  }

  protected function get_description()
  {
    return $this->_description;
  }

  // }}}
  // {{{ descriptable()

  public function descriptable( DstyleDoc_Element $element, $description )
  {
    if( $element instanceof DstyleDoc_Element_Function )
      $element->exception->description = $description;
  }

  // }}}
  // {{{ priority

  const priority = 15;

  // }}}
  // {{{ analyse()

  static public function analyse( $current, $source, &$instance, &$priority )
  {
    // \s*(?:[-+*]\s+)?(?:([\pL\pN]+)\s*)(?:[:=]\s+)?(.*)$
    if( ($current instanceof DstyleDoc_Analyser_Throw or $current instanceof DstyleDoc_Analyser_Element_Throw_List)
      and preg_match( '/\\s*(?:[-+*]\\s+)?(?:([\\pL\\pN]+)\\s*)(?:[:=]\\s+)?(.*)$/i', $source, $matches ) )
    {
      if( ! trim($matches[1]) )
        return false;
      $instance = new self( $matches[1], $matches[2] );
      $priority = self::priority;
      return true;
    }
    else
      return false;
  }

  // }}}
  // {{{ apply()

  /**
   * Ajoute une exception à l'élément.
   */
  public function apply( DstyleDoc_Element $element )
  {
    if( $element instanceof DstyleDoc_Element_Function )
    {
      $element->exception = $this->exception;
      $element->exception->description = $this->description;
    }
    return $this;
  }

  // }}}
  // {{{ __construct()

  public function __construct( $exception, $description )
  {
    $this->exception = $exception;
    $this->description = $description;
  }

  // }}}
}

/**
 * Classe d'analyse d'une balise de syntaxe.
 */
class DstyleDoc_Analyser_Syntax extends DstyleDoc_Analyser
{
  // {{{ priority

  const priority = 10;

  // }}}
  // {{{ analyse()

  static public function analyse( $current, $source, &$instance, &$priority )
  {
    // ^syntax:\s*$
    if( preg_match( '/^syntax:\\s*$/i', $source, $matches ) )
    {
      $instance = new self();
      $priority = self::priority;
      return true;
    }
    else
      return false;
  }

  // }}}
  // {{{ apply()

  /**
   * Ajoute un nouveau paragraphe à la description à l'élément.
   * S'assure que le précédent ajout n'étaient pas déjà un nouveau paragraphe.
   */
  public function apply( DstyleDoc_Element $element )
  {
    return $this;
  }

  // }}}
}

/**
 * Classe d'analyse d'un élément de liste de syntaxe.
 */
class DstyleDoc_Analyser_Element_Syntax_List extends DstyleDoc_Analyser implements DstyleDoc_Analyser_Descriptable
{
  // {{{ $syntax

  protected $_syntax = '';

  protected function set_syntax( $syntax ) 
  {
    $optional = false;
    foreach( explode(',', $syntax) as $var )
    {
      // \s*(\[?)\s*(\$[\pLpN]+|\.{3})\s*\]?
      if( preg_match('/\\s*(\\[?)\\s*(\\$[\\pLpN]+|\\.{3})\\s*\\]?/i', $var, $matches) )
      {
        if( ! empty($matches[1]) )
          $optional = true;
        $this->_syntax[] = array(
          'var' => $matches[2],
          'optional' => $optional );
      }
    }
  }

  protected function get_syntax()
  {
    return $this->_syntax;
  }

  // }}}
  // {{{ $description

  protected $_description = '';

  protected function set_description( $description )
  {
    $this->_description = (string)$description;
  }

  protected function get_description()
  {
    return $this->_description;
  }

  // }}}
  // {{{ descriptable()

  public function descriptable( DstyleDoc_Element $element, $description )
  {
    if( $element instanceof DstyleDoc_Element_Function )
      $element->syntax->description = $description;
  }

  // }}}
  // {{{ priority

  const priority = 15;

  // }}}
  // {{{ analyse()

  static public function analyse( $current, $source, &$instance, &$priority )
  {
    // (?:[-+*]\s*)?((?:\s*,?\s*\[?\s*(?:\$[\pLpN]+|\.{3}))*\]?)\s*[:=]?\s*(.*)$
    if( ($current instanceof DstyleDoc_Analyser_Syntax or $current instanceof DstyleDoc_Analyser_Element_Syntax_List)
      and preg_match( '/(?:[-+*]\\s*)?((?:\\s*,?\\s*\\[?\\s*(?:\\$[\\pLpN]+|\\.{3}))*\\]?)\\s*[:=]?\\s*(.*)$/i', $source, $matches ) )
    {
      if( ! trim($matches[1]) )
        return false;
      $instance = new self( $matches[1], $matches[2] );
      $priority = self::priority;
      return true;
    }
    else
      return false;
  }

  // }}}
  // {{{ apply()

  /**
   * Ajoute une exception à l'élément.
   */
  public function apply( DstyleDoc_Element $element )
  {
    if( $element instanceof DstyleDoc_Element_Function )
    {
      $element->syntax = $this->syntax;
      $element->syntax->description = $this->description;
    }
    return $this;
  }

  // }}}
  // {{{ __construct()

  public function __construct( $syntax, $description )
  {
    $this->syntax = $syntax;
    $this->description = $description;
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
