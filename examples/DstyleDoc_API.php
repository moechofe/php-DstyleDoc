<?php

chdir('../');

require_once( 'xdebug.front.end.php' );

require_once( 'DstyleDoc.php' );
require_once( 'converter.FirstStyle.php' );

set_time_limit( 90 );
error_reporting( E_ALL | E_STRICT );

DstyleDoc::hie()
  ->source( 'DstyleDoc.php' )
  ->convert_with( 

    DstyleDoc_Converter_FirstStyle::hie()
      ->template_dir( 'converter.FirstStyle' )
      ->config( array(
        'logo' => 'DstyleDoc API documentation',

        'files_index_list_header' => 'Liste des fichiers',
        'classes_index_list_header' => 'Liste des classes déclarées dans le fichier <span class="file">%s</span>',
      ) )
  
  );

?>
