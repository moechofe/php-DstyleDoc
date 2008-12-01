<div class="param-type-var">
  <span class="param-type">
  {section name=type loop=$_param.types}
    <em>{$_param.types[type]}{if ! $templatelite.section.type.last}, {/if}</em>
  {/section}
  </span>
  <span class="param-var">
    <strong>${$_param.var}</strong>
  </span>
</div>
<div class="return-description">
{$_param.description}
</div>
