<?php

chdir('../');

require_once( 'xdebug.front.end.php' );

require_once( 'DstyleDoc.php' );
require_once( 'converter.FirstStyle.php' );
//ini_set( 'memory_limit', -1 );
//set_time_limit( 90 );
error_reporting( E_ALL | E_STRICT );

DstyleDoc::hie()
  ->source( 'DstyleDoc.php' )
  ->convert_with(

    DstyleDoc_Converter_FirstStyle::hie()
      ->template_dir( 'converter.FirstStyle' )
      ->config( array(

'skin' => 'rosy',
'charset' => 'utf-8',

'database_pass' => 'SeveuSe',

// Texts declarations

'logo' => 'DstyleDoc API documentation',

'page_class' => 'Cette page traite de la classe %2$s déclarée dans le fichier %4$s. <a href="#page-browser">Accédez à la navigation</a>.',
'page_method' => 'Cette page traite de la méthode %2$s de la classe %4$s déclarée dans le fichier %6$s. <a href="#page-browser">Accédez à la navigation</a>.',

'files_index_list_header' => 'Liste des fichiers',
'classes_index_list_header' => 'Liste des classes déclarées dans le fichier <span class="file">%s</span>',
'methods_index_list_header' => 'Liste des méthodes de la classe <span class="class">%s</span>',

'method_syntax' => 'syntaxe',
'method_description' => 'déscription',

      ) )

    );

?>
