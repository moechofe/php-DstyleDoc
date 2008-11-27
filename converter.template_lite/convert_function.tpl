<div class="function-content"><div class="frame"><div class="content">
  <div class="content-display"><h1>{$_function.display}</h1></div>
  <div class="content-title"><p>{$_function.title}</p></div>
  <div class="content-syntax">
  {php}d($this->_vars['_function']->returns)->d5{/php}
    <h2>{if isset(#function_syntax#)}{#function_syntax#}{else}#function_syntax#{/if}</h2>
    <ul>
    {foreach item=syntax from=$_function.syntaxs}
      <li>{$syntax}</li>
    {/foreach}
    </ul>
  </div>
  <div class="content-description">{$_function.description}</div>
  <div class="content-params">{$_function.params}</div>
  {if $_function.returns}
  <div class="content-returns">
    <h2>{if isset(#function_returns#)}{#function_returns#}{else}#function_returns#{/if}</h2>
    <dl>
      {foreach item=return from=$_function.returns}
	<dt>{$return}HERE>>{php}d($this->_vars['return']){/php}</dt>
      {/foreach}
    </dl>
  </div>
  {/if}

</div></div></div>
