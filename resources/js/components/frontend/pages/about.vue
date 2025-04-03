<template>
    <div class="sg-page-content">
      <section class="about-section">
        <div class="container" v-if="lengthCounter(page) > 0">
          <div class="page-title text-center">
            <h1>{{ page.title }}</h1>
          </div>
          <div class="about-content" v-html="page.content"></div>
        </div>
        <!-- /.container -->
        <div class="container mb-3" v-else-if="shimmer" v-for="(page, i) in 3">
          <shimmer :height="200" :key="i"></shimmer>
        </div>
        <!-- /.container -->
      </section>
      <!-- /.about-section -->
    </div>
  </template>
  
  
<script>
import shimmer from "../partials/shimmer";

export default {
    name: "about",
    components: {
        shimmer
    },
    data(){
      return {
          slug:this.$route.params.slug
      }
    },
    watch: {
        $route() {
            if(this.page.link != this.$route.params.slug){
                this.$store.dispatch('othersPage', this.$route.params.slug);
            }
        },
        page()
        {
            document.title = this.page.title;
        }
    },
    mounted(){
        if(this.page.link != this.$route.params.slug) {
            this.$store.dispatch('othersPage', this.slug);
        }
      document.title = this.page.title;

    },
   computed:{
       page() {
           return this.$store.getters.getPageData;
       },
       shimmer(){
           return this.$store.state.module.shimmer
       }
   }
}
</script>
<style scoped>
.sg-page-content {
  background-color: #f9f9f9;
  padding: 20px 0;
}

.about-section {
  padding: 60px 0;
}

.page-title {
  margin-bottom: 30px;
}

.page-title h1 {
  font-size: 2.5em;
  color: #333;
  text-align: center;
}

.about-content {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
}

.about-text {
  flex: 1 1 50%;
  padding: 20px;
  font-size: 1.1em;
  color: #555;
}

.about-image {
  flex: 1 1 40%;
  padding: 20px;
  text-align: center;
}

.about-image img {
  max-width: 100%;
  height: auto;
  border-radius: 10px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}
</style>

