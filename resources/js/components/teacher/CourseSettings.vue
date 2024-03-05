<template>
    <div>
        <div class="row mb-3">
            <div class="col-xl-6">
                <div class="settings-group">
                    <h5>Kursinformationen</h5>
                    <div class="sub-controls">
                        <base-settings ref="baseSettings" :id="courseId" :disabled="courseFinished"></base-settings>
                    </div>
                </div>
                <div class="mt-3 settings-group" v-if="!(courseFinished && setups.length == 0)">
                    <h5>Aufgaben</h5>
                    <div class="sub-controls">
                        <div v-for="(setup, index) in setups" :key="index" class="mb-2">
                            <div class="row align-items-center">
                                <div class="col">
                                    <span class="fw-bold">{{ index + 1 }}. Aufgabe: </span>
                                </div>
                                <div class="col-auto" v-if="!courseFinished">
                                    <button class="btn btn-danger btn-sm" @click="deleteCounsellingSetup(index)">
                                        Löschen
                                    </button>
                                </div>
                            </div>
                            <counselling-setups-settings :setup="setup"
                                :index="index"
                                :all_personae="all_personae"
                                :all_fields="all_fields"
                                :disabled="courseFinished"
                                :course_duration="{'start': $refs.baseSettings.inputs.start_date, 'end': $refs.baseSettings.inputs.end_date}">
                            </counselling-setups-settings>
                        </div>
                        <button class="btn btn-secondary" @click="addCounsellingSetup" v-if="!courseFinished">Aufgabe hinzufügen</button>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 mt-3 mt-md-0">
                <div class="settings-group">
                    <h5>Personae für Übungen</h5>
                    <div class="sub-controls" :class="{ 'is-invalid': v$.exercise.settings.personae.$error }">
                        <div class="invalid-feedback d-block mb-3" v-if="v$.exercise.settings.personae.$error">
                            Mindestens eine Persona auswählen
                        </div>
                        <personas :groups="groups" :selectedFields="exercise.settings.counselling_fields" :selectedPersonae="exercise.settings.personae"
                            @personaChanged="togglePersona" @fieldChanged="toggleField" :disabled="courseFinished">
                        </personas>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-3" v-if="courseId">
            <div class="col settings-group mt-0">
                <h5>Kursteilnehmer*innen</h5>
                <div class="sub-controls p-0">
                    <members-list :id="courseId" :disabled="courseFinished"></members-list>
                </div>
            </div>
        </div>
        <div class="row mb-3" v-if="courseId">
            <div class="col settings-group mt-0">
                <h5>Kurs löschen</h5>
                <div class="sub-controls">
                    <div><b>Achtung:</b><br/>Durch Löschen des Kurses werden alle zugehörigen Beratungen gelöscht. Die Kursmitglieder haben keinen Zugriff mehr auf die Chatverläufe.</div>
                    <button class="btn btn-danger mt-1" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">
                        Kurs löschen
                    </button>
                </div>
            </div>
        </div>
        <div class="row text-center">
            <div class="col-md-12">
                <a class="btn" :class="[courseFinished ? 'btn-primary' : 'btn-secondary']" href="/">{{ courseFinished ? 'Zurück' : 'Abbrechen'}}</a>
                <button v-if="!courseFinished" class="btn btn-primary" @click="save">{{courseId ? 'Änderungen speichern' : 'Kurs anlegen' }}</button>
            </div>
        </div>
    </div>
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Löschen bestätigen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div><b>Achtung:</b><br/>Durch Löschen des Kurses werden alle zugehörigen Beratungen gelöscht. Die Kursmitglieder haben keinen Zugriff mehr auf die Chatverläufe.</div>
                    <div class="row my-3">
                        <div class="mb-2">Zum Bestätigen bitte Kursname und Einschreibeschlüssel eingeben:</div>
                        <div class="col-sm-6">
                            <label>Kursname</label>
                            <input id="confirmName" type="text" class="form-control" v-model="confirm.name" :class="{ 'is-invalid': confirm.wrong }">
                        </div>
                        <div class="col-sm-6">
                            <label>Einschreibeschlüssel</label>
                            <input id="confirmKey" type="text" class="form-control" v-model="confirm.enrollment_key" :class="{ 'is-invalid': confirm.wrong }">
                        </div>
                    </div>
                    <div class="row justify-content-end">
                        <div class="col-auto">
                            <button type="button" class="btn btn-secondary me-3" data-bs-dismiss="modal">Abbrechen</button>
                            <button type="button" class="btn btn-danger" @click="delete_course()">Löschen</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>
<script>
import { showSuccessAlert, showErrorAlert } from '../../helpers/Alerts';
import { useVuelidate } from '@vuelidate/core';
import { required } from '@vuelidate/validators';

export default {
    props: [ 'courseId' ],
    data() {
        return {
            course: {},
            groups: [],
            all_fields: [],
            all_personae: [],
            exercise: {
                settings: {
                    counselling_fields: [],
                    personae: []
                },
                id: null,
            },
            setups: [],
            v$: useVuelidate(),
            confirm: {
                name: '',
                enrollment_key: '',
                wrong: false,
            },
        }
    },

    computed: {
        courseFinished() {
            const end = new Date(this.course?.end_date).setHours(0,0,0,0);
            const today = new Date().setHours(0,0,0,0);
            return today > end;
        },
    },

    validations() {
        return {
            exercise: {
                settings: {
                    personae: {
                        required,
                        minimumOne: (value) => {
                            // Check if at least one persona has been selected that belongs to a selected field
                            return value.some((personaId) => {

                                const personaGroupId = this.groups.find((group) =>
                                    group.personas.some((p) => p.id === personaId)
                                ).id;

                                return this.exercise.settings.counselling_fields.includes(personaGroupId);
                            });
                        },
                    }
                }
                
            },
        }
    },

    mounted() {
        // load possible persona
        axios.get('/personas')
            .then(response => {
                this.groups = response.data;
                this.groups.forEach((field) => {
                    this.all_fields.push({ name: field.name, id: field.id });
                    field.personas.forEach((persona) => {
                        this.all_personae.push({ name: persona.name, id: persona.id, field: field.id });
                    })
                })
                
                if (this.courseId) {
                    // load and set data for existing course
                    axios.get(`/course/${this.courseId}/infos`)
                        .then((res) => {
                            this.setData(res.data);
                        })
                        .catch((err) => {
                            showErrorAlert(err);
                        })
                } else {
                    // default setting for exercise for new course: everything selected
                    this.exercise.settings.counselling_fields = this.all_fields.map(field => field.id);
                    this.exercise.settings.personae = this.all_personae.map(p => p.id);
                }
            })
            .catch((err) => {
                showErrorAlert(err);
            });
        const confirmDeleteModal = document.getElementById('confirmDeleteModal');
        confirmDeleteModal.addEventListener('hide.bs.modal', event => {
            this.confirm = {
                name: '',
                enrollment_key: '',
                wrong: false
            };
        });
    },
    unmounted() {
        const confirmDeleteModal = document.getElementById('confirmDeleteModal');
        confirmDeleteModal.removeEventListener('hide.bs.modal', event => {
            this.confirm = {
                name: '',
                enrollment_key: '',
                wrong: false
            };
        });
    },

    methods: {
        setData(courseData) {
            this.course = courseData;
            const setups = courseData.counselling_setups;
            // set data for exercise-setup
            this.exercise = {
                settings: setups.filter((setup) => setup.mandatory == false)[0].settings,
                id: setups.filter((setup) => setup.mandatory == false)[0].id,
            };
            // set data for tasks-setups
            this.setups = [];
            const tasks = setups.filter((setup) => setup.mandatory);
            tasks.forEach(task => {
                this.setups.push({
                    id: task.id,
                    mandatory: true,
                    due_date: task.due_date.split(" ")[0],
                    settings: {
                        counselling_fields: this.all_fields.filter((field) => task.settings.counselling_fields.includes(field.id)),
                        personae: this.all_personae.filter((persona) => task.settings.personae.includes(persona.id)),
                    }
                })
            })
            // set data for baseSettings
            this.$refs.baseSettings.inputs.name = courseData.name;
            this.$refs.baseSettings.inputs.enrollment_key = courseData.enrollment_key;
            this.$refs.baseSettings.inputs.start_date = courseData.start_date;
            this.$refs.baseSettings.inputs.end_date = courseData.end_date;
        },

        toggleField(field) {
            if (this.exercise.settings.counselling_fields.includes(field.id)) {
                const index = this.exercise.settings.counselling_fields.indexOf(field.id);
                this.exercise.settings.counselling_fields.splice(index, 1);
            } else {
                this.exercise.settings.counselling_fields.push(field.id);
            }
        },
        togglePersona(persona) {
            if (this.exercise.settings.personae.includes(persona.id)) {
                const index = this.exercise.settings.personae.indexOf(persona.id);
                this.exercise.settings.personae.splice(index, 1);
            } else {
                this.exercise.settings.personae.push(persona.id);
            }
        },
        addCounsellingSetup() {
            this.setups.push({
                mandatory: true,
                due_date: null,
                settings: {
                    counselling_fields: [],
                    personae: []
                },
                id: null,
            });
        },
        deleteCounsellingSetup(index) {
            this.setups.splice(index, 1);
        },
        async save() {
            const inputCorrect = await this.v$.$validate();
            if (!inputCorrect) return;

            const baseSettings = this.$refs.baseSettings.inputs;
            let course = {
                name: baseSettings.name,
                enrollment_key: baseSettings.enrollment_key,
                start_date: baseSettings.start_date,
                end_date: baseSettings.end_date,
                counselling_setups: [ this.exercise ],
            };
            if (this.setups.length > 0) {
                const setups_data = [];
                this.setups.forEach(setup => {
                    setups_data.push({
                        'settings': {
                            'counselling_fields': setup.settings.counselling_fields.map(cf => cf.id),
                            'personae': setup.settings.personae.map(p => p.id),
                        },
                        'mandatory': setup.mandatory,
                        'due_date': setup.due_date,
                        'id': setup.id
                    })
                });
                course.counselling_setups = course.counselling_setups.concat(setups_data);
            }
            if (this.course?.id) {
                course.id = this.course.id;
            }
            axios.post('/course/store', course)
                .then(res => {
                    if (!this.courseId) {
                        window.location.href = `/course/${res.data.id}`;
                        return;
                    }
                    this.setData(res.data.course);
                    showSuccessAlert('Änderungen gespeichert');
                })
                .catch(err => {
                    showErrorAlert(err);
                })
        },
        delete_course() {
            if (this.confirm.name !== this.course.name || this.confirm.enrollment_key !== this.course.enrollment_key) {
                this.confirm.wrong = true;
                return;
            }
            axios.delete('/course/' + this.course.id)
                .then((res) => {
                    window.location.href = '/';
                })
                .catch((err) => {
                    showErrorAlert(err);
                })
        },
    },
}
</script>
<style lang="scss" scoped>
    .settings-group {
        margin: 20px;
    }
</style>
<style src="@suadelabs/vue3-multiselect/dist/vue3-multiselect.css"></style>
