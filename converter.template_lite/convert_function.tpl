<div class="function-content"><div class="frame"><div class="content">
  <div class="content-title"><p>{$_function.title}</p></div>
  <div class="content-display"><h1>{$_function.display}</h1></div>
  <div class="content-syntax">
    <h2>{if isset(#function_syntax#)}{#function_syntax#|string_format:$_function.display:$_function.link}{else}#function_syntax(function.name,function.link)#{/if}</h2>
    <ul>
    {foreach item=syntax from=$_function.syntaxs}
      <li>{$syntax}</li>
    {/foreach}
    </ul>
  </div>
  <div class="content-description">{$_function.description}</div>
  {if $_function.params}
  <div class="content-params">
    <h2>{if isset(#function_params#)}{#function_params#|string_format:$_function.display:$_function.link}{else}#function_params(function.name,function.link)#{/if}</h2>
    <ul>
      {foreach item=param from=$_function.params}
	<li>{$param}</li>
      {/foreach}
    </ul>
  </div>
  {/if}
  {if $_function.returns}
  <div class="content-returns">
    <h2>{if isset(#function_returns#)}{#function_returns#|string_format:$_function.display:$_function.link}{else}#function_returns(function.name,function.link)#{/if}</h2>
    <ul>
      {foreach item=return from=$_function.returns}
	<li>{$return}</li>
      {/foreach}
    </ul>
  </div>
  {/if}
</div></div></div>
