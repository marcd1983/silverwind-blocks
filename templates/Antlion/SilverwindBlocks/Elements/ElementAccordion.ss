$RenderedTitle
<% if $Intro %><div class="prose mb-6 max-w-none">$Intro</div><% end_if %>
<% if $Panels %>
    <div class="join join-vertical w-full">
        <% loop $Panels %>
            <details id="$Anchor" class="collapse collapse-arrow join-item border border-base-300 bg-base-100"<% if not $Up.AllowMultiple %> name="accordion-$Up.ID"<% end_if %><% if $IsFirst && $Up.OpenFirst %> open<% end_if %>>
                <summary class="collapse-title text-lg font-semibold">$Title.XML</summary>
                <div class="collapse-content">
                    <div class="<% if $Image %>grid gap-6 sm:grid-cols-[minmax(0,12rem)_1fr]<% end_if %>">
                        <% if $Image %>
                            <img src="$Image.ScaleMaxWidth(400).URL" alt="" width="$Image.ScaleMaxWidth(400).Width" height="$Image.ScaleMaxWidth(400).Height" class="w-full rounded-box" loading="lazy">
                        <% end_if %>
                        <div>
                            <div class="prose max-w-none">$Content</div>
                            <% if $Links %>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    <% loop $Links %><% include Includes/SilverwindBlocks/Button Size='btn-sm' %><% end_loop %>
                                </div>
                            <% end_if %>
                        </div>
                    </div>
                </div>
            </details>
        <% end_loop %>
    </div>
<% end_if %>
