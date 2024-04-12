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
        },
        changeSeason(event) {
            axios.get(event.currentTarget.dataset.href+"?season="+document.getElementById("seasonChoice").value).then(response => {
                const categoryChoiceElement = document.getElementById("categoryChoice");
                const competitionChoiceElement = document.getElementById("competitionChoice");

                categoryChoiceElement.innerHTML = "";
                competitionChoiceElement.innerHTML = "";
                const data = response.data;
                categoryChoiceElement.add(new Option("", "",));
                data.forEach(obj => {
                    categoryChoiceElement.add(new Option(obj, obj));
                });
            });
        },
        changeCategory(event) {
            axios.get(event.currentTarget.dataset.href+"?season="+document.getElementById("seasonChoice").value+"&category="+document.getElementById("categoryChoice").value).then(response => {
                const competitionChoiceElement = document.getElementById("competitionChoice");

                competitionChoiceElement.innerHTML = "";
                const data = response.data;
                competitionChoiceElement.add(new Option("", "",));
                data.forEach(obj => {
                    competitionChoiceElement.add(new Option(obj, obj));
                });
            });
        },
        changeCompetition(event) {
            const url = event.currentTarget.dataset.href+"?season="+document.getElementById("seasonChoice").value+"&category="+document.getElementById("categoryChoice").value+"&competition="+document.getElementById("competitionChoice").value;
            location.href = url;
        },
        changePhase(event) {
            const url = event.currentTarget.dataset.href;
            const link = event.currentTarget;
            axios.get(url).then(response => {
                document.querySelectorAll(".nav-link").forEach((item => {
                    item.classList.remove("active");
                }))
                link.classList.add("active");

                const parser = new DOMParser();
                let dataHtml = parser.parseFromString(response.data,"text/html");
                document.getElementById("classement-content").innerHTML = dataHtml.getElementById("classement-content").innerHTML;

            });
        }

    }
})

appHome.config.compilerOptions.delimiters = ['[[', ']]'];
appHome.mount('#content-home')