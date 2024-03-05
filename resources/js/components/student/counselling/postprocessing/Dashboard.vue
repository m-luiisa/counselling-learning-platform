<template>
    <div class="h-100">
        <div class="h-100 row d-flex align-content-between">
            <span>
                <!-- Counselling infos -->
                <div class="row align-items-center">
                    <div class="col-xl-3 col-md-auto">
                        <label class="fw-bold">Beratungsname:</label>
                    </div>
                    <div class="col-xl-9 pe-0 position-relative">
                        <input type="text" class="form-control counselling-title" :disabled="title_disabled" v-model="counselling_title"/>
                        <span v-if="!courseFinished" class="title-icons">
                            <span v-show="title_disabled"
                                @click="edit_title" data-toggle="tooltip" title="Name bearbeiten">
                                <fa-icon :icon="['fas', 'edit']"></fa-icon>
                            </span>
                            <span v-show="!title_disabled" class="me-3 text-danger"
                                @click="cancel_edit_title" data-toggle="tooltip" title="Abbrechen">
                                <fa-icon :icon="['fas', 'xmark']"></fa-icon>
                            </span>
                            <span v-show="!title_disabled"
                                @click="save_counselling_title" data-toggle="tooltip" title="Speichern">
                                <fa-icon :icon="['fas', 'save']"></fa-icon>
                            </span>
                        </span>

                    </div>
                </div>
                <div class="row mt-2 align-items-center">
                    <div class="col-xl-3 col-md-auto">
                        <label class="fw-bold">Beratungsstelle:</label>
                    </div>
                    <div class="col-xl-9 pe-0">
                        <span>{{ counselling.counselling_field }}</span>
                    </div>
                </div>
                <!-- Postprocessing checklist -->
                <div class="mt-5">
                    <div class="container-content">
                        <div class="mb-3">
                            <b>Nachbereitung</b><small class="text-grey-dark"> (optional)</small>
                        </div>
                        <div class="row my-2">
                            <div class="col-auto">
                                <input type="checkbox" class="form-check-input green" @change="noteCheckboxChanged" :checked="counselling.note?.done" :disabled="courseFinished"/>
                            </div>
                            <div class="col p-0">
                                <a class="link fw-normal" @click="change_tab('note')">Notizen zur Selbstreflexion ➜</a>
                            </div>
                        </div>
                        <div class="row my-2">
                            <div class="col-auto"><input type="checkbox" class="form-check-input green" disabled/></div>
                            <div class="col p-0"><a class="link disabled fw-normal">Feedback vom Virtuellen Klienten ➜</a></div>
                        </div>
                        <div class="row my-2">
                            <div class="col-auto"><input type="checkbox" class="form-check-input green" disabled/></div>
                            <div class="col p-0 text-grey-dark">
                                Bewertung des Virtuellen Klienten
                                <br/>
                                <span class="text-grey-dark">
                                    Die Bewertung der Chatbot-Nachrichten wird genutzt, um den Virtuellen Klienten kontinuierlich zu verbessern.
                                    Jede Bewertung kann zusätzlich begründet werden. Die Bewertung wird automatisch gesendet, sobald die Beratung geschlossen wird.
                                </span>
                            </div>
                        </div>
                        <div class="row justify-content-end mt-3">
                            <div class="col-auto">
                                <b>{{ status }}/3 Schritte abgeschlossen</b>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Feedback checklist -->
                <div class="mt-5">
                    <div class="container-content">
                        <div class="mb-3">
                            <b>Zusätzliches Feedback</b>
                        </div>
                        <div class="row my-2">
                            <div class="col-auto"><input type="checkbox" class="form-check-input green" disabled/></div>
                            <div class="col p-0"><a class="link disabled fw-normal">Feedback von Trainer*in ➜</a></div>
                        </div>
                        <div class="row my-2">
                            <div class="col-auto"><input type="checkbox" class="form-check-input green" disabled/></div>
                            <div class="col p-0"><a class="link disabled fw-normal">Peer Review von anderen Kursteilnehmenden ➜</a></div>
                        </div>
                    </div>
                </div>
            </span>

            <div class="text-center my-5">
                <a class="btn btn-primary" :href="`/course/${counselling.course}`">Beratung schließen</a>
            </div>
        </div>
    </div>
</template>
<script>
import { showSuccessAlert , showErrorAlert} from '../../../../helpers/Alerts';

export default {
    props: ['counselling', 'courseFinished'],
    data() {
        return {
            title_disabled: true,
            counselling_title: this.counselling.title,
        };
    },
    computed: {
        status() {
            const options = [this.counselling.note, this.counselling.feedback, this.counselling.peerReview];
            return options.filter(option => option != null && (option.status === 'done' || option.done)).length;
        }
    },
    methods: {
        edit_title() {
            this.title_disabled = false;
        },
        cancel_edit_title() {
            this.title_disabled = true;
            this.counselling_title = this.counselling.title;
        },
        save_counselling_title() {
            axios.put('/counselling/' + this.counselling.id, {
                title: this.counselling_title,
            })
            .then(res => {
                this.title_disabled = true;
                this.$emit('counsellingChanged', res.data.counselling);
                showSuccessAlert(res.data.message);
            })
            .catch(err => {
                showErrorAlert(err);
            })
        },

        noteCheckboxChanged($event) {
            let noteData = { };
            noteData.done = $event.target.checked;
            noteData.text = this.counselling.note === null ? '' : this.counselling.note.text;

            axios.put('/counselling/' + this.counselling.id, {
                note: noteData
            })
            .then(res => {
                this.$emit('counsellingChanged', res.data.counselling);
                showSuccessAlert(res.data.message);
            })
            .catch(err => {
                showErrorAlert(err);
            })
        },

        change_tab(newTab) {
            this.$emit('changeTab', newTab);
        }
    },
    watch: {
        counselling: {
            handler(newCounselling) {
                this.counselling_title = newCounselling.title;
            },
            deep: true
        },
    },
    
}
</script>
<style lang="scss" scoped>
@import '../../../../../css/general.scss';

.row {
    margin-right: 0 ;
}
.container-content {
    padding: 25px;
}

.counselling-title {
    padding-right: 45px;
}

.title-icons {
    color: $primary;
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    &:hover {
        cursor: pointer;
        color: $primary-dark;
    }
}

.link:not(:hover) {
    color: $text;
}
.link.disabled {
    color: $grey-dark;
    cursor: default;
}

.close-btn {
    position: relative;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
}
</style>