<%-- Full-width slider shared by the Slider block and the page hero slider ($PageSlider). Scope: the record using SliderSettings. --%>
<% cached 'sw-slider', $SlidesCacheKey %>
<% if $ActiveSlides %>
<div x-data="swSwiper" data-options="$SwiperOptionsJSON.ATT" class="sw-slider relative isolate overflow-hidden bg-neutral" style="--sw-ar: $DesktopRatio; --sw-ar-m: $MobileRatio;" role="region" aria-roledescription="carousel" aria-label="<% if $IsPageSlider %><%t Antlion\\SilverwindBlocks.Featured 'Featured' %><% else_if $Title %>$Title.ATT<% else %><%t Antlion\\SilverwindBlocks.Slideshow 'Slideshow' %><% end_if %>">
    <div class="swiper" x-ref="swiper">
        <div class="swiper-wrapper">
            <% loop $ActiveSlides %>
                <% include Includes/SilverwindBlocks/Slide InnerClass=$Up.SlideInnerClass, LazyLoad=$Up.LazyLoad %>
            <% end_loop %>
        </div>
    </div>
    <% include Includes/SilverwindBlocks/SwiperControls Count=$ActiveSlides.Count, Inset=1 %>
</div>
<% end_if %>
<% end_cached %>
