<div class="method-content"><div class="frame"><div class="content">
  <div class="content-title"><p>{if isset(#method_title#)}{#method_title#|string_format:$_method.title:$_method.link}{else}#method_title(method-title,method-link)#{/if}</p></div>
  <div class="content-display"><h1>{if isset(#method_display#)}{#method_display#|string_format:$_method.display:$_method.link}{else}#method_display(method-name,method-link){/if}</h1></div>
  <div class="content-package">
		{if isset(#method_package#)}{#method_package#|string_format:$_method.display:$_method.link}{else}#method_package(method-name,method-link)#{/if}
		<ul>
{foreach from=$_method.packages item=package}
			<li>{if is_object($package)}$package.link{else}{$package}{/if}</li>
{/foreach}
		</ul>
	</div>
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
    <dl>
{foreach item=param from=$_method.params}
      {$param}
{/foreach}
    </dl>
  </div>
{/if}
{if $_method.returns}
  <div class="content-returns">
    <h2>{if isset(#method_returns#)}{#method_returns#|string_format:$_method.display:$_method.link}{else}#method_returns(method.name,method.link)#{/if}</h2>
    <dl>
{foreach item=return from=$_method.returns}
      {$return}
{/foreach}
    </dl>
  </div>
{/if}
{if $_method.exceptions}
  <div class="content-exceptions">
    <h2>{if isset(#method_exceptions#)}{#method_exceptions#|string_format:$_method.display:$_method.link}{else}#method_exceptions(method.name,method.link)#{/if}</h2>
    <dl>
{foreach item=exception from=$_method.exceptions}
      {$exception}
{/foreach}
    </dl>
  </div>
{/if}
</div></div></div>
