<div x-data="swTabs">
    $RenderedTitle
    <% if $Intro %><div class="prose mb-6 max-w-none">$Intro</div><% end_if %>
    <% if $Panels %>
        <div role="tablist" class="tabs $TabStyleClass overflow-x-auto" aria-label="<% if $Title %>$Title.ATT<% else %><%t Antlion\\SilverwindBlocks.Tabs 'Tabs' %><% end_if %>" @keydown="keydown(\$event)">
            <% loop $Panels %>
                <button type="button" role="tab" id="$Anchor-button" class="tab whitespace-nowrap<% if $IsFirst %> tab-active<% end_if %>" data-tab="$Anchor" aria-controls="$Anchor" aria-selected="<% if $IsFirst %>true<% else %>false<% end_if %>"<% if not $IsFirst %> tabindex="-1"<% end_if %>
                        :class="{ 'tab-active': isActive('$Anchor') }" :aria-selected="isActive('$Anchor').toString()" :tabindex="isActive('$Anchor') ? 0 : -1" @click="select('$Anchor')">$Title.XML</button>
            <% end_loop %>
        </div>
        <% loop $Panels %>
            <div role="tabpanel" id="$Anchor" aria-labelledby="$Anchor-button" tabindex="0" class="pt-6 focus-visible:outline-2" x-show="isActive('$Anchor')"<% if not $IsFirst %> x-cloak<% end_if %>>
                <% if $Image %>
                    <img src="$Image.ScaleMaxWidth(1200).URL" alt="" width="$Image.ScaleMaxWidth(1200).Width" height="$Image.ScaleMaxWidth(1200).Height" class="mb-6 w-full rounded-box" loading="lazy">
                <% end_if %>
                <div class="prose max-w-none">$Content</div>
                <% if $Links %>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <% loop $Links %><% include Includes/SilverwindBlocks/Button %><% end_loop %>
                    </div>
                <% end_if %>
            </div>
        <% end_loop %>
    <% end_if %>
</div>
