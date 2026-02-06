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
        refreshFsLightbox();
    },

    methods: {
        showUserDetails(event) {
            this.seeUserDetails = true;
            const url = event.currentTarget.dataset.href
                .replace('__season__', document.getElementById("seasonChoice").value);

            axios.get(url).then(response => {
                document.getElementById("detail-content-block-body").innerHTML = response.data;
                this.$forceUpdate();
            });
        },

        returnToList() {
            this.seeUserDetails = false;
        },

        changeSeason(event) {
            axios.get(event.currentTarget.dataset.href + "?season=" + document.getElementById("seasonChoice").value)
                .then(response => {
                    const categoryChoiceElement = document.getElementById("categoryChoice");
                    categoryChoiceElement.innerHTML = "";

                    categoryChoiceElement.add(new Option("", ""));
                    response.data.forEach(obj => {
                        categoryChoiceElement.add(new Option(obj, obj));
                    });
                });
        },

        changeCategory(event) {
            const url = event.currentTarget.dataset.href +
                "?season=" + document.getElementById("seasonChoice").value +
                "&category=" + document.getElementById("categoryChoice").value;

            location.href = url;
        },

        changePhase(event) {
            const link = event.currentTarget;
            let url = link.dataset.href;
            let codeCompetition = document.getElementById('competitions').value;
            let numPhase = link.dataset.phase;
            url = url.replace("__codeCompetition__",codeCompetition)

            axios.get(url).then(response => {
                document.querySelectorAll(".nav-link").forEach(item =>
                    item.classList.remove("active")
                );
                document.getElementById('nav-link-phase'+numPhase).classList.add("active");

                const parser = new DOMParser();
                const dataHtml = parser.parseFromString(response.data, "text/html");

                document.getElementById("classement-content").innerHTML =
                    dataHtml.getElementById("classement-content").innerHTML;
            });
        },

        showResultatJournee(event) {
            const url = event.currentTarget.dataset.href;
            const numj = event.currentTarget.dataset.numj;

            axios.get(url).then(response => {
                const modalJ = document.getElementById("modal-result-journey");
                const parser = new DOMParser();
                const dataHtml = parser.parseFromString(response.data, "text/html");

                modalJ.querySelector("#modalTitleNumJ").textContent = numj;
                modalJ.querySelector(".modal-body").innerHTML =
                    dataHtml.getElementById("resultat-journee").innerHTML;

                bootstrap.Modal.getOrCreateInstance(modalJ).show();
            });
        },

        showTerrain(event) {
    console.log('click terrain');

    const raw = event.currentTarget.dataset.terrain;
    console.log('raw:', raw);

    if (!raw) {
        alert('Pas de terrain');
        return;
    }

    let terrain;
    try {
        terrain = JSON.parse(raw);
    } catch (e) {
        console.error('JSON invalide', e);
        return;
    }

    console.log('terrain:', terrain);

    const modal = document.getElementById("modal-terrain");

    modal.querySelector(".modal-body").innerHTML = `
        <div><b>Nom : </b>${terrain.nom ?? ''}</div>
        <div><b>Addresse : </b></div>
        <div>${terrain.adresse ?? ''}</div>
        <div>${terrain.codePostal ?? ''} ${terrain.ville ?? ''}</div>
        <div><b>Surface : </b>${terrain.surface ?? ''}</div>
    `;

    bootstrap.Modal.getOrCreateInstance(modal).show();
}
    }
});

appHome.config.compilerOptions.delimiters = ['[[', ']]'];
appHome.mount('#content-home');
