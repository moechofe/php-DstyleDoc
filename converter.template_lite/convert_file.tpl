<div class="file-content"><div class="frame"><div class="content">
  <div class="content-title">{$_file.title}</div>
  <div class="content-display"><h1>{if isset(#file_header_display#)}{#file_header_display#|string_format:$_file.display}{else}#file_header_display(file-name)#{/if}</h1></div>
  <div class="content-description">{$_file.description}</div>
  <div class="content-index">{classes_index file=$_file}</div>
  <div class="content-index">{functions_index file=$_file}</div>
</div></div></div>
