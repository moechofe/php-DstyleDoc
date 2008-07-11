<div class="method-content"><div class="frame"><div class="content">
  <div class="content-display"><h1>{$_method.display}</h1></div>
  <div class="content-title"><p>{$_method.title}</p></div>
  <div class="content-syntax">
    <h2>{#method_syntax#}</h2>
    <ul>
    {foreach item=syntax from=$_method.syntaxs}
      <li>{$syntax}</li>
    {/foreach}
    </ul>
  </div>
  <div class="content-description">{$_method.description}</div>
  
</div></div></div>{* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 fileformat=unix foldmethod=marker encoding=utf8 setlocal noendofline binary *}
