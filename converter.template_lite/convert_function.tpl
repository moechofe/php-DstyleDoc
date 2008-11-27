<div class="function-content"><div class="frame"><div class="content">
  <div class="content-display"><h1>{$_function.display}</h1></div>
  <div class="content-title"><p>{$_function.title}</p></div>
  <div class="content-syntax">
  {php}d($this->_vars['_function']->returns)->d5{/php}
    <h2>{#function_syntax#}</h2>
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
    <h2>{#function_returns#}</h2>
    <dl>
      {foreach item=return from=$_function.returns}
	<dt>HERE>>{php}d($this->_vars['return']){/php}</dt>
      {/foreach}
    </dl>
  </div>
  {/if}

</div></div></div>
