<%--
    Navigation, dots, scrollbar and pause button for swSwiper. Pass Count, and
    Inset=1 to overlay them all on the slides (sliders) rather than below
    (carousels). ArrowsInside=1 keeps just the arrows inside the edges, for a
    full-width carousel: outside them they'd run off the screen.
--%>
<% if $Count > 1 %>
    <% if $Navigation %>
        <button type="button" x-ref="prev" class="btn btn-circle absolute top-1/2 z-20 -translate-y-1/2 border-0 bg-base-100/85 shadow-md backdrop-blur hover:bg-base-100 <% if $Inset || $ArrowsInside %>left-3 sm:left-5<% else %>-left-3 sm:-left-5<% end_if %>" aria-label="<%t Antlion\\SilverwindBlocks.Previous 'Previous slide' %>">
            <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd"/></svg>
        </button>
        <button type="button" x-ref="next" class="btn btn-circle absolute top-1/2 z-20 -translate-y-1/2 border-0 bg-base-100/85 shadow-md backdrop-blur hover:bg-base-100 <% if $Inset || $ArrowsInside %>right-3 sm:right-5<% else %>-right-3 sm:-right-5<% end_if %>" aria-label="<%t Antlion\\SilverwindBlocks.Next 'Next slide' %>">
            <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
        </button>
    <% end_if %>
    <% if $ShowPagination %>
        <div x-ref="pagination" class="swiper-pagination<% if not $Inset %> sw-pagination-below<% end_if %>"></div>
    <% end_if %>
    <% if $Scrollbar %>
        <div x-ref="scrollbar" class="swiper-scrollbar<% if not $Inset %> sw-scrollbar-below<% end_if %>"></div>
    <% end_if %>
    <% if $Autoplay %>
        <button type="button" x-show="autoplay" x-cloak @click="toggleAutoplay()" :aria-pressed="paused.toString()" class="btn btn-circle btn-sm absolute z-20 border-0 bg-base-100/85 shadow backdrop-blur <% if $Inset %>right-3 bottom-3 sm:right-5 sm:bottom-5<% else %>-top-12 right-0<% end_if %>" :aria-label="paused ? '<%t Antlion\\SilverwindBlocks.Play 'Play slideshow' %>' : '<%t Antlion\\SilverwindBlocks.Pause 'Pause slideshow' %>'">
            <% if $AutoplayProgress %>
                <%-- Countdown ring on the button's edge, clear of the icon --%>
                <svg class="pointer-events-none absolute inset-0 size-full -rotate-90 text-primary" viewBox="0 0 36 36" aria-hidden="true">
                    <circle cx="18" cy="18" r="17" fill="none" stroke="currentColor" stroke-opacity="0.2" stroke-width="2"/>
                    <circle cx="18" cy="18" r="17" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" pathLength="100" stroke-dasharray="100" :stroke-dashoffset="100 - progress * 100"/>
                </svg>
            <% end_if %>
            <svg x-show="!paused" class="relative size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M5.75 3a.75.75 0 0 0-.75.75v12.5c0 .414.336.75.75.75h1.5a.75.75 0 0 0 .75-.75V3.75A.75.75 0 0 0 7.25 3h-1.5ZM12.75 3a.75.75 0 0 0-.75.75v12.5c0 .414.336.75.75.75h1.5a.75.75 0 0 0 .75-.75V3.75a.75.75 0 0 0-.75-.75h-1.5Z"/></svg>
            <svg x-show="paused" class="relative size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M6.3 2.84A1.5 1.5 0 0 0 4 4.11v11.78a1.5 1.5 0 0 0 2.3 1.27l9.344-5.891a1.5 1.5 0 0 0 0-2.538L6.3 2.841Z"/></svg>
        </button>
    <% end_if %>
<% end_if %>
