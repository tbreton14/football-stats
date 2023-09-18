import { createApp } from 'vue';
import axios from 'axios';

const appHome = createApp({
    data() {
        return {
            seeUserDetails: false
        }
    },
    methods: {
        showUserDetails(event) {
            this.seeUserDetails = true;
            axios.get(event.currentTarget.dataset.href).then(response => {
               //document.getElementById("detail-content-block-body").html = response.data;
                const htmlElement = response.data;
                document.getElementById("detail-content-block-body").innerHTML = htmlElement;
                this.$forceUpdate();
            });
        },
        returnToList() {
            this.seeUserDetails = false;
        }
    }
})

appHome.config.compilerOptions.delimiters = ['[[', ']]'];
appHome.mount('#content-home')