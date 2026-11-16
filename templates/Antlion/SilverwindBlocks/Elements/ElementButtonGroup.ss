$RenderedTitle
<% if $Links %>
    <div class="flex flex-wrap gap-3 $ButtonJustifyClass<% if $Stacked %> max-sm:flex-col<% end_if %>">
        <% loop $Links %>
            <% include Includes/SilverwindBlocks/Button Size=$Top.ButtonSizeClass %>
        <% end_loop %>
    </div>
<% end_if %>
