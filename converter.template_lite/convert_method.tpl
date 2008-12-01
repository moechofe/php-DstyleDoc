<div class="method-content"><div class="frame"><div class="content">
  <div class="content-title"><p>{$_method.title}</p></div>
  <div class="content-display"><h1>{$_method.display}</h1></div>
  <div class="content-syntax">
    <h2>{if isset(#method_syntax#)}{#method_syntax#|string_format:$_method.display:$_method.link}{else}#method_syntax(methode-name,method-link)#{/if}</h2>
    <ul>
    {foreach item=syntax from=$_method.syntaxs}
      <li>{$syntax}</li>
    {/foreach}
    </ul>
  </div>
  <div class="content-description">{$_method.description}</div>
  {if $_method.params}
  <div class="content-params">
    <h2>{if isset(#method_params#)}{#method_params#|string_format:$_method.display:$_method.link}{else}#method_params(method.name,method.link)#{/if}</h2>
    <ul>
      {foreach item=param from=$_method.params}
	<li>{$param}</li>
      {/foreach}
    </ul>
  </div>
  {/if}
  {if $_method.returns}
  <div class="content-returns">
    <h2>{if isset(#method_returns#)}{#method_returns#|string_format:$_method.display:$_method.link}{else}#method_returns(method.name,method.link)#{/if}</h2>
    <ul>
      {foreach item=return from=$_method.returns}
	<li>{$return}</li>
      {/foreach}
    </ul>
  </div>
  {/if}

</div></div></div>
