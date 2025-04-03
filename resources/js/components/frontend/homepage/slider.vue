<template>
	<section class="hero-slider p-0 home-4">
	  <div class="container">
		<div class="row">
		  <div class="col-lg-3 categorie-menu-fixed">
			<sidebar_categories :slider="true" :home="0"></sidebar_categories>
		  </div>
		  <!-- end fixed menu -->
  
		  <div class="col-lg-9">
			<div v-if="sliders.length > 0" class="row">
			  <div :class="addons.includes('ishopet') ? 'col-lg-9' : 'col-lg-12'">
				<VueSlickCarousel
				  class="hero-section"
				  :class="{ 'ishopet-hero-section': addons.includes('ishopet') }"
				  v-bind="slick_settings"
				  :rtl="settings.text_direction == 'rtl'"
				  @afterChange="handleAfterChange"
				  @beforeChange="handleBeforeChange"
				>
				  <div class="hero-slide-item" v-for="(slider, i) in sliders" :key="i">
					<div class="slider__img slider_div">
					  <a :href="slider.link" v-if="urlCheck(slider.link)">
						<img :src="slider.slider_bg_image" :alt="slider.link" />
					  </a>
					  <a href="javascript:void(0)" v-else-if="!slider.link">
						<img :src="slider.slider_bg_image" :alt="slider.link" />
					  </a>
					  <router-link v-else :to="slider.link">
						<img :src="slider.slider_bg_image" :alt="slider.link" />
					  </router-link>
					  <div v-if="activeSlide === i" class="slide-caption">
						<h2 v-html="slider.text"></h2>
						<!-- <button>{{ slider.button_text }}</button> -->
					  </div>
					</div>
				  </div>
				</VueSlickCarousel>
			  </div>
			  <div :class="addons.includes('ishopet') ? 'col-lg-3' : 'col-lg-12'">
				<div class="hero-banner-section" :class="{ 'ishopet-hero-banner': addons.includes('ishopet') }">
				  <div class="banner__items">
					<div class="banner__item" v-for="(banner, index) in banners" :key="'banner' + index">
					  <div class="banner-img">
						<a :href="banner.link" v-if="urlCheck(banner.link)">
						  <img :src="banner.image" :alt="banner.link" />
						</a>
						<router-link v-else :to="banner.link">
						  <img :src="banner.image" :alt="banner.link" />
						</router-link>
					  </div>
					</div>
				  </div>
				</div>
			  </div>
			  <!-- /.hero-banner-section -->
			</div>
		  </div>
		</div>
	  </div>
	</section>
  </template>
  
  <script>
  import VueSlickCarousel from "vue-slick-carousel";
  import shimmer from "../partials/shimmer";
  import sidebar_categories from "../partials/sidebar_categories";
  
  export default {
	name: "slider",
	components: { VueSlickCarousel, shimmer, sidebar_categories },
	data() {
	  return {
		activeSlide: 0,
		slick_settings: {
		  dots: true,
		  edgeFriction: 0.35,
		  infinite: true,
		  speed: 500,
		  slidesToShow: 1,
		  slidesToScroll: 1,
		  arrows: false,
		//   autoplay: true,
		  autoplaySpeed: 5000,
		},
	  };
	},
	computed: {
	  sliders() {
		return this.$store.getters.getSliders;
	  },
	  banners() {
		return this.$store.getters.getSliderBanners;
	  },
	},
	methods: {
	  handleAfterChange(currentSlide) {
		this.activeSlide = currentSlide;
	  },
	  handleBeforeChange() {
		this.activeSlide = null; // Hide the caption and button during the transition
	  },
	  toggleCategory() {
		if (this.defaultCategoryShow == false) {
		  document.body.classList.add("sidebar-active");
		  this.$store.dispatch("defaultCategoryShow", true);
		} else {
		  document.body.classList.remove("sidebar-active");
		  this.$store.dispatch("defaultCategoryShow", false);
		}
	  },
	},
  };
  </script>
  
  <style scoped>
.slide-caption h2 {
	overflow: hidden;
	display: inline-block;
	position: relative;
	animation: slideDown 1.5s ease-in-out forwards;
	color: #ffffff; /* Ensure text color is white */
	text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7); /* Add text shadow for better readability */
	background: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
	padding: 10px; /* Add padding for better spacing */
	border-radius: 5px; 
	opacity: 0; /* Ensure the text is initially hidden */
}

@keyframes slideDown {
	0% {
		transform: translateY(-100%);
		opacity: 0;
	}
	100% {
		transform: translateY(0);
		opacity: 1;
	}
}

.slide-caption {
	position: absolute;
	top: 20px;
	left: 20px;
	color: #ffffff;
	padding: 10px;
	z-index: 10;
}

.slide-caption h2 {
	font-size: 20px;
	margin-bottom: 10px;
	line-height: 1.5;
	color: #ffffff; /* Ensure text color is white */
	text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7); /* Add text shadow for better readability */
	background: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
	padding: 10px; /* Add padding for better spacing */
	border-radius: 5px; /* Optional: rounded corners for the background */
}

.slide-caption button {
	background-color: #007bff;
	color: white;
	border: none;
	padding: 10px 20px;
	cursor: pointer;
	text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.7); /* Add text shadow for button text */
	border-radius: 5px; /* Optional: rounded corners for the button */
}

.slider_div {
	position: relative;
}

@media (max-width: 767px) {
	.slide-caption h2 {
		font-size: 12px;
	}
	.slide-caption {
		top: 5px;
	}
	
	@keyframes slideDown {
		0% {
			transform: translateY(-100%);
			opacity: 0;
		}
		100% {
			transform: translateY(0);
			opacity: 1;
		}
	}
}
</style>
