<template>
    <div class="row item m-0 py-4 px-2" :class="{'colored-bg': this.data.author === 'vikl'}">
        <div class="col">
            <div class="row">
                <div class="row align-items-baseline">
                    <div v-if="data.author === 'vikl'" class="col-auto pe-0 robot align-self-center">
                        <fa-icon :icon="['fas', 'robot']"></fa-icon>
                    </div>
                    <div class="col-auto pe-0">
                        <span class="fw-bold fs-5">{{ author }}</span>
                    </div>
                    <div class="col-auto pe-0">
                        <span class="text-grey-dark">{{ dateTime }}</span>
                    </div>
                </div>
                <div class="row mt-1">
                    <div class="col-12">{{ data.content }}</div>
                </div>

            </div>
        </div>

        <div class="col-auto d-flex align-items-center dropdown">
            <button v-if="!(courseFinished && note === undefined)" class="btn-note dropdown-toggle"
                data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside"
                ref="dropdownBtn">
                <span data-bs-toggle="tooltip" title="Persönliche Notizen sind privat und können von keiner anderen Person gelesen werden.">
                    <fa-icon v-show="note == undefined || note === ''" :icon="['far', 'message']" class="note empty"></fa-icon>
                    <fa-icon v-show="note != undefined && note !== ''" :icon="['fas', 'message']" class="note"></fa-icon>
                </span>
            </button>
            <div class="dropdown-menu container-content note-container" ref="dropdownMenu">
                <div class="row">
                    <div class="col"><b>Persönliche Notiz</b></div>
                    <div class="col-auto btn-close-note" @click="closeNote">
                        <fa-icon :icon="['fas', 'close']"></fa-icon>
                    </div>
                </div>
                <textarea placeholder="Text..." rows="3" class="form-control mb-1" v-model="noteNew" :readonly="courseFinished"></textarea>
                <div class="row justify-content-end">
                    <div class="col-auto">
                        <button v-if="!courseFinished" class="btn btn-primary btn-sm px-3" @click="saveNote" :disabled="noteNew?.trim() === note?.trim()">
                            <fa-icon :icon="['fas', 'save']"></fa-icon>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</template>
<script>
import { showSuccessAlert , showErrorAlert} from '../../../helpers/Alerts';

export default {
    props: ['data', 'persona', 'pseudonym', 'courseFinished'],
    data() {
        return {
            note: this.data?.additions?.note ? this.data?.additions?.note : '',
            noteNew: this.note ? this.note : '',
        };
    },
    computed: {
        author() {
            return this.data.author === 'vikl' ? this.persona : this.pseudonym;
        },
        dateTime() {
            const date = new Date(this.data.created_at);
            return date instanceof Date && !isNaN(date) ? date.toLocaleString('de-DE') : '';
        }
    },
    mounted() {
        this.$refs.dropdownBtn?.addEventListener('show.bs.dropdown', event => {
            this.noteNew = this.note;
        });
    },
    beforeUnmount() {
        this.$refs.dropdownBtn?.removeEventListener('show.bs.dropdown', event => {
            this.noteNew = this.note;
        });
    },
    methods: {
        saveNote() {
            axios.put(`/counselling/note/${this.data.counselling_id}/${this.data.message_number}`,{
                text: this.noteNew,
            })
            .then(res => {
                this.closeNote();
                this.note = res.data.counsellingMessage.additions.note ? res.data.counsellingMessage.additions.note : '';
                showSuccessAlert('Anmerkung gespeichert');
            })
            .catch(err => {
                showErrorAlert(err);
            })

        },
        
        closeNote() {
            this.$refs.dropdownBtn.ariaExpanded = false;
            this.$refs.dropdownBtn.classList.remove('show');
            this.$refs.dropdownMenu.classList.remove('show');
        },
    },
    
}
</script>
<style lang="scss" scoped>
@import '../../../../css/general.scss';

    .colored-bg {
        background-color: $background-light;
    }

    .note {
        width: 1.5em;
        height: 1.5em;
        color: $primary;
        &:hover {
            color: $primary-dark;
            cursor: pointer;
        }
        &.empty {
            color: $grey-dark;
            &:hover {
                color: $primary;
            }
        }
    }
    .robot svg {
        width: 20px;
        height: 20px;
    }

    .btn-note {
        border: none;
        background-color: transparent;
        &::after {
            display: none;
        }
        &.show svg {
            color: $primary;
        }
    }
    .note-container {
        min-width: 200px;
        border: none;
        border-radius: 0;

        textarea {
            resize: none;
            width: 100%;
            padding: 3px;
            &:focus {
                box-shadow: none !important;
                outline: none;
            }
        }
        svg {
            width: 16px;
            height: 16px;
        }
    }

    .btn-close-note {
        color: $grey-dark;
        &:hover {
            cursor: pointer;
            color: $primary-dark;
        }
    }
</style>