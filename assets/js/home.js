import { createApp } from 'vue';
import axios from 'axios';
import fslightbox from 'fslightbox';

const appHome = createApp({
    data() {
        return {
            seeUserDetails: false
        }
    },
    mounted() {
        console.log("mounted");
        // var googlePhotos = document.querySelectorAll(".linkGooglePhoto");
        // googlePhotos.forEach(photo => {
        //     photo.setAttribute("data-fslightbox","gallery");
        //     photo.setAttribute("data-type","image");
        // });
        refreshFsLightbox();
    },
    methods: {
        showUserDetails(event) {
            this.seeUserDetails = true;
            var url = event.currentTarget.dataset.href.replace('__season__',document.getElementById("seasonChoice").value);
            axios.get(url).then(response => {
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
        },
        showResultatJournee(event) {
            const url = event.currentTarget.dataset.href;
            const numj = event.currentTarget.dataset.numj;
            axios.get(url).then(response => {
                var modalJ = document.getElementById("modal-result-journey");
                const parser = new DOMParser();
                let dataHtml = parser.parseFromString(response.data,"text/html");

                modalJ.querySelector("#modalTitleNumJ").innerHTML = numj;
                modalJ.querySelector(".modal-body").innerHTML = dataHtml.getElementById("resultat-journee").innerHTML;
                bootstrap.Modal.getOrCreateInstance(modalJ).show();
            });
        }

    }
})

appHome.config.compilerOptions.delimiters = ['[[', ']]'];
appHome.mount('#content-home')