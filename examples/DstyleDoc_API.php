<?php

chdir('../');
set_time_limit(0);

require_once( 'xdebug.front.end.php' );

require_once( 'DstyleDoc.php' );
require_once( 'converter.FirstStyle.php' );
//ini_set( 'memory_limit', -1 );
//set_time_limit( 90 );
error_reporting( E_ALL | E_STRICT );

DstyleDoc::hie()
  ->source( 'DstyleDoc.php' )
  ->source( 'process.tokens.php' )
  ->convert_with(

    DstyleDoc_Converter_FirstStyle::hie()
      ->template_dir( 'converter.FirstStyle' )
      ->destination_dir( 'api_doc' )
      ->config( array(

'skin' => 'rosy',
'charset' => 'utf-8',

'database_pass' => 'SeveuSe',

// Texts declarations

'logo' => 'DstyleDoc API documentation',

'home_title' => 'Documentation API de DstyleDoc',
'home_text' => '<p><strong><em>DstyleDoc</em> est un générateur de documentation</strong> pour PHP <strong>léger et puissant</strong>. - Il pourrait être encore plus léger et puissant, mais je n\'ai pas que ça a faire. - Pour s\'en assurer scrutez la liste des avantages.</p><dl><dt>Capacité à <strong>générer la documentation</strong> et de l\'afficher sur une page Web <strong>à la volé</strong>.</dt><dd>Gràce à une <s>super</s> technique <s>futuriste</s> nommé l\'<a href="http://en.wikipedia.org/wiki/Lazy_evaluation">évaluation paresseuse</a> <em>DstyleDoc</em> analyse la documentation seulement si cela est nécessaire. Résultat, sur des petites documentations : les pages peuvent être générées à la volé.</dd><dt>Compatible avec JavaDoc et la "D Embedded Documentation".</dt><dd>Oubliez la viellissante JavaDoc pas très adapté et adoptez la symphatique <a href="http://www.digitalmars.com/d/2.0/ddoc.html">syntaxe de documentation du langage D</a>. Dans les deux cas, <em>DstyleDoc</em> implémente des nouveaux tags spéciales pour PHP.</dd><dt>Analyse du code à la <strong>recherche des exceptions lancées et des valeurs de retours</strong>.</dt><dd><small>Ma fonctionnalitée favorite :</small> <em>DstyleDoc</em> recherche dans le code des fonctions, pour trouver les classes des exceptions lancées et les types des valeurs retournées. La moitier de la documentation est faite.</dd><dt><strong>Liens automatique vers les éléments</strong> de la documentation.</dt><dd>Ne saisissez plus de tags internes {@link}. <em>DstyleDoc</em> détecte automatiquement si des références vers d\'autre éléments apparaîssent dans le texte et crée les liens correspondants.</dd></dl>',

'file_header_display' => 'Le fichier <strong>%s</strong>',

'page_file' => 'Cette page traite du fichier %2$s. <a href="#page-browser">Accédez à la navigation</a>',
'page_home' => 'Cette page traite de la documentation de DstyleDoc. <a href="#page-browser">Accédez à la navigation</a>',
'page_class' => 'Cette page traite de la classe %2$s déclarée dans le fichier %4$s. <a href="#page-browser">Accédez à la navigation</a>.',
'page_method' => 'Cette page traite de la méthode %2$s de la classe %4$s déclarée dans le fichier %6$s. <a href="#page-browser">Accédez à la navigation</a>.',

'class_header_display' => 'La classe <strong>%s</strong>',

'method_syntax' => 'syntaxe',
'method_description' => 'déscription',
'method_params' => 'paramètres',
'method_returns' => 'valeurs de retour',

'files_index_list_header' => 'Liste des fichiers',
'classes_index_list_header' => 'Liste des classes déclarées dans le fichier %2$s',
'methods_index_list_header' => 'Liste des méthodes de la classe <span class="class">%s</span>',

      ) )

    );

?>
