<div class="method-content"><div class="frame"><div class="content">
  <div class="content-display"><h1>{$_method.display}</h1></div>
  <div class="content-title"><p>{$_method.title}</p></div>
  <div class="content-syntax">
    {if !empty(#method_syntax#)}<h2>{#method_syntax#}</h2>{elseif isset(#method_syntax#)}{else}<h2>#method_syntax#</h2>{/if}
    <ul>
    {foreach item=syntax from=$_method.syntaxs}
      <li>{$syntax}</li>
    {/foreach}
    </ul>
  </div>
  <div class="content-description">{$_method.description}</div>

</div></div></div>
