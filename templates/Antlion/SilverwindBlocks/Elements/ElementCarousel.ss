<%-- Full width: the heading lines up with the site, the cards start at the side padding and run to the screen edges when scrolled --%>
<% if $RenderedTitle || $Intro %>
    <div class="<% if $IsFullBleed %>sw-container<% end_if %>">
        $RenderedTitle
        <% if $Intro %><div class="prose mb-6 max-w-none">$Intro</div><% end_if %>
    </div>
<% end_if %>
<% if $Slides %>
    <div x-data="swSwiper" data-options="$SwiperOptionsJSON.ATT" class="relative<% if not $IsFullBleed %> px-1<% end_if %>" role="region" aria-roledescription="carousel" aria-label="<% if $Title %>$Title.ATT<% else %><%t Antlion\\SilverwindBlocks.Carousel 'Carousel' %><% end_if %>">
        <div class="swiper py-2<% if $IsFullBleed %> px-gutter<% end_if %>" x-ref="swiper">
            <div class="swiper-wrapper">
                <% loop $Slides %>
                    <div class="swiper-slide h-auto!" role="group" aria-roledescription="slide" aria-label="$Pos / $TotalItems">
                        <% include Includes/SilverwindBlocks/Card Style=$Up.CardStyle %>
                    </div>
                <% end_loop %>
            </div>
        </div>
        <% include Includes/SilverwindBlocks/SwiperControls Count=$Slides.Count, ArrowsInside=$IsFullBleed %>
    </div>
<% end_if %>
