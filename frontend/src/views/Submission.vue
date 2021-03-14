<template>
  <div> 
    <!-- header -->
    <b-navbar toggleable="lg" type="success" variant="info">
        <b-container class="bv-example-row">
            <b-navbar-brand href="#">Home</b-navbar-brand>
            <b-navbar-toggle target="nav-collapse"></b-navbar-toggle>
            <b-collapse id="nav-collapse" is-nav>
            <b-navbar-nav>
                <b-nav-item href="#">Submission</b-nav-item>
                <b-nav-item href="#" disabled>Contact</b-nav-item>
            </b-navbar-nav>

            <!-- Right aligned nav items -->
            <b-navbar-nav class="ml-auto">
                <b-nav-form>
                <b-form-input size="sm" class="mr-sm-2" placeholder="Search"></b-form-input>
                <b-button size="sm" class="my-2 my-sm-0" type="submit">Search</b-button>
                </b-nav-form>
                <b-nav-item-dropdown right>
                <!-- Using 'button-content' slot -->
                <template #button-content>
                    <em>Xuyen</em>
                </template>
                <b-dropdown-item href="#">Profile</b-dropdown-item>
                <b-dropdown-item href="#">Sign Out</b-dropdown-item>
                </b-nav-item-dropdown>
            </b-navbar-nav>
            </b-collapse>
        </b-container>
    </b-navbar>
    <!-- main -->
    <b-container>
        <form action="" @submit.prevent="submitFile" enctype="multipart/form-data">
            <div class="form-control">
                <label for="file">Upload file</label>
                <input type="file" ref="file" @change="selectFile" class="file-input">
                <span v-if="file" class="file-name"></span>
                <button class="button is-info">Submit</button>
            </div>
        </form>
    </b-container>
</div>  
</template>

<style></style>
<script>

import VueApexCharts from "vue-apexcharts";
import StatisticsCardLine from "@/components/statistics-cards/StatisticsCardLine";
import VuePerfectScrollbar from "vue-perfect-scrollbar";
import ChangeTimeDurationDropdown from "@/components/ChangeTimeDurationDropdown";
import axios from 'axios';

export default {
  data() {
    return {
      file: ""
    }
  },
  components: {
    VueApexCharts,
    StatisticsCardLine,
    VuePerfectScrollbar,
    ChangeTimeDurationDropdown
  },
  mounted() {
  },
  created() {
  },
  methods: {
    selectFile() {
        this.file = this.$refs.file.files[0];
        console.log(this.file);
      },
    async submitFile() {
        const formData = new FormData();
        formData.append('/upload', this.file);
        try {
        await axios.post('', formData)
        }catch(err) {
            console.log(err);
        }
    }
  },
  watch: {
  }
}
</script>
