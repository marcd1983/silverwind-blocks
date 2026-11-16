<% if $Orientation == 'collapsible' %>
    <details class="collapse collapse-arrow border border-base-300 bg-base-100">
        <summary class="collapse-title text-lg font-semibold">$Title.XML</summary>
        <div class="collapse-content">
            <ul class="menu w-full p-0">
                <% loop $Links %><li><% include Includes/SilverwindBlocks/MenuLink %></li><% end_loop %>
            </ul>
        </div>
    </details>
<% else %>
    $RenderedTitle('mb-3 text-lg font-semibold')
    <% if $Links %>
        <nav<% if $Title %> aria-label="$Title.ATT"<% end_if %>>
            <ul class="<% if $Orientation == 'horizontal' %>flex flex-wrap gap-x-6 gap-y-2 $ButtonJustifyClass<% else %>menu w-full p-0<% end_if %>">
                <% loop $Links %><li><% include Includes/SilverwindBlocks/MenuLink Plain=$Top.IsHorizontal %></li><% end_loop %>
            </ul>
        </nav>
    <% end_if %>
<% end_if %>
